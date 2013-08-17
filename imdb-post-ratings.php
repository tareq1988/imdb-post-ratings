<?php
/*
  Plugin Name: IMDB Post Ratings
  Plugin URI: http://wedevs.com/
  Description: Post ratings plugin that acts like IMDB
  Version: 0.1
  Author: Tareq Hasan
  Author URI: http://tareq.wedevs.com/
  License: GPL2
 */

/**
 * Copyright (c) 2013 Tareq Hasan (email: tareq@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
// don't call the file directly
if ( !defined( 'ABSPATH' ) )
    exit;

// load the widget
require_once dirname( __FILE__ ) . '/top-rated-widget.php';

/**
 * IMDB_Post_Ratings class
 *
 * @class IMDB_Post_Ratings The class that holds the entire IMDB_Post_Ratings plugin
 */
class IMDB_Post_Ratings {

    /**
     * @var string table name
     */
    private $table;

    /**
     * @var object $wpdb object
     */
    private $db;

    /**
     * Constructor for the IMDB_Post_Ratings class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        global $wpdb;

        // setup table name
        $this->db = $wpdb;
        $this->table = $this->db->prefix . 'imdb_rating';

        // Activate and deactivate hooks
        register_activation_hook( __FILE__, array($this, 'activate') );
        register_deactivation_hook( __FILE__, array($this, 'deactivate') );

        // Localize our plugin
        add_action( 'init', array($this, 'localization_setup') );

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

        // Ajax vote
        add_action( 'wp_ajax_ipr_vote', array($this, 'ajax_insert_vote') );
        add_action( 'wp_ajax_ip_vote_del', array($this, 'ajax_delete_vote') );
    }

    /**
     * Initializes the IMDB_Post_Ratings() class
     *
     * Checks for an existing IMDB_Post_Ratings() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new IMDB_Post_Ratings();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        $sql = "CREATE TABLE {$this->table} (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(11) unsigned NOT NULL,
            `post_type` varchar(20) NOT NULL,
            `user_id` int(11) unsigned NOT NULL,
            `vote` mediumint(2) unsigned NOT NULL DEFAULT '1',
            `updated` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `post_id` (`post_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query( $sql );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'ipr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueue scripts and styles
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'ipr-styles', plugins_url( 'css/style.css', __FILE__ ) );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'ipr-scripts', plugins_url( 'js/script.js', __FILE__ ), array('jquery'), false, true );
        wp_localize_script( 'ipr-scripts', 'ipr', array(
            'action' => 'ipr_vote',
            'del_action' => 'ip_vote_del',
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ipr-ratings' ),
            'loggedin' => is_user_logged_in() ? 'true' : 'false',
            'loginMessage' => __( 'Please login to vote', 'ipr' ),
            'errorMessage' => __( 'Something went wrong', 'ipr' )
        ) );
    }

    function flush_cache( $post_id ) {
        delete_transient( 'ipr_rating_' . $post_id );
    }

    /**
     * Ajax handler for inserting a vote
     *
     * @return void
     */
    function ajax_insert_vote() {
        check_ajax_referer( 'ipr-ratings', 'nonce' );

        // bail out if not logged in
        if ( !is_user_logged_in() ) {
            wp_send_json_error();
        }

        // so, the user is logged in huh? proceed on
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $vote = isset( $_POST['vote'] ) ? intval( $_POST['vote'] ) : 0;
        $user_id = get_current_user_id();

        if ( $post_id && $vote ) {

            // if already voted, then simply update the existing
            // else, add a new vote
            if ( $this->get_user_vote( $post_id, $user_id ) ) {
                $this->update_vote( $post_id, $user_id, $vote );
            } else {
                $this->add_vote( $post_id, $user_id, $vote );
            }
        }

        // flush the cache
        $this->flush_cache( $post_id );

        wp_send_json_success( array(
            'vote_i18n' => number_format_i18n( $vote ),
            'vote' => $vote
        ) );
    }

    /**
     * Ajax handler for deleting a vote
     *
     * @return void
     */
    function ajax_delete_vote() {
        check_ajax_referer( 'ipr-ratings', 'nonce' );

        // bail out if not logged in
        if ( !is_user_logged_in() ) {
            wp_send_json_error();
        }

        // so, the user is logged in huh? proceed on
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $user_id = get_current_user_id();

        $this->delete_vote( $post_id, $user_id );

        // flush the cache
        $this->flush_cache( $post_id );

        wp_send_json_success( array(
            'vote_i18n' => '-',
            'vote' => '0'
        ) );
    }

    /**
     * Gets a user vote for a post
     *
     * @param int $post_id
     * @param int $user_id
     * @return bool|object
     */
    function get_user_vote( $post_id, $user_id ) {
        $sql = "SELECT vote FROM {$this->table} WHERE post_id = %d AND user_id = %d";

        return $this->db->get_row( $this->db->prepare( $sql, $post_id, $user_id ) );
    }

    /**
     * Insert a user vote
     *
     * @param int $post_id
     * @param int $user_id
     * @param int $vote
     * @return bool
     */
    public function add_vote( $post_id, $user_id, $vote ) {
        $post_type = get_post_field( 'post_type', $post_id );

        return $this->db->insert(
            $this->table, array(
                'post_id' => $post_id,
                'post_type' => $post_type,
                'user_id' => $user_id,
                'vote' => $vote,
                'updated' => current_time( 'mysql' )
            ), array(
                '%d',
                '%s',
                '%d',
                '%d',
                '%s'
            )
        );
    }

    /**
     * Update a user vote
     *
     * @param int $post_id
     * @param int $user_id
     * @param int $vote
     * @return bool
     */
    public function update_vote( $post_id, $user_id, $vote ) {
        return $this->db->update(
            $this->table, array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'vote' => $vote,
                'updated' => current_time( 'mysql' )
            ),
            //where
            array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ),
            //data format
            array(
                '%d',
                '%d',
                '%d',
                '%s'
            ),
            //where format
            array(
                '%d',
                '%d'
            )
        );
    }

    /**
     * Delete a user vote
     *
     * @param int $post_id
     * @param int $user_id
     * @return bool
     */
    public function delete_vote( $post_id, $user_id ) {
        $query = "DELETE FROM {$this->table} WHERE post_id = %d AND user_id = %d";

        return $this->db->query( $this->db->prepare( $query, $post_id, $user_id ) );
    }

    /**
     * Get rating for a post
     *
     * @param int $post_id
     * @return obj
     */
    function get_rating( $post_id ) {
        $result = get_transient( 'ipr_rating_' . $post_id );

        if ( false === $result ) {
            $sql = "SELECT SUM(vote) / COUNT(id) AS rating, COUNT(id) AS voter
                FROM {$this->table}
                WHERE post_id = %d";

            $result = $this->db->get_row( $this->db->prepare( $sql, $post_id ) );
            set_transient( 'ipr_rating_' . $post_id, $result, 3600 );
        }

        return $result;
    }

    /**
     * Get top rated posts
     *
     * @param int $post_type
     * @param int $count
     * @param int $offset
     * @return array
     */
    function get_top_rated( $post_type = 'post', $count = 10, $offset = 0 ) {

        $sql = "SELECT format(SUM(im.vote) / COUNT(im.id), 2) AS rating, COUNT(im.id) AS voter, im.post_id
                FROM {$this->table} im
                LEFT JOIN {$this->db->posts} p ON p.ID = im.post_id
                WHERE im.post_type = '$post_type' AND p.post_status = 'publish'
                GROUP BY im.post_id
                ORDER BY rating DESC
                LIMIT $offset, $count";

        // cache the result
        $result = get_transient( 'ipr_top_' . $post_type );

        if ( false === $result ) {
            $result = $this->db->get_results( $sql );

            set_transient( 'ipr_top_' . $post_type, $result, 3600 );
        }

        return $result;
    }

    /**
     * Display the rating bar
     *
     * @global object $post
     * @param int $post_id
     */
    public function rating_input( $post_id = null ) {
        global $post;

        if ( !$post_id ) {
            $post_id = $post->ID;
        }

        $base_rating = apply_filters( 'ip_base_rating', 10 );
        $given_vote = $this->get_user_vote( $post_id, get_current_user_id() );

        $user_voted = false;

        // check if user voted
        if ( $given_vote ) {
            $user_vote = $given_vote->vote;
            $user_vote_i18n = number_format_i18n( $given_vote->vote );
            $user_voted = true;
        } else {
            $user_vote = 0;
            $user_vote_i18n = '-';
        }
        ?>
        <div class="ip-rating-container">
            <span class="ip-rating-stars">
                <?php for ($i = 1; $i <= $base_rating; $i++) { ?>
                    <span title="<?php printf( __( 'Click to rate: %s', 'ipr' ), number_format_i18n( $i ) ); ?>" data-i18n="<?php echo number_format_i18n( $i ); ?>" data-id="<?php echo $post_id; ?>" data-vote="<?php echo $i; ?>"></span>
                <?php } ?>
            </span> <!-- .ip-rating-stars -->

            <span class="ip-rating-preview">
                <span class="ip-rating-hover-value" data-vote="<?php echo $user_vote; ?>"><?php echo $user_vote_i18n; ?></span>
                <span class="ip-rating-sep">/</span>
                <span class="ip-baserating"><?php echo number_format_i18n( $base_rating ); ?></span>
            </span> <!-- .ip-rating-preview -->

            <span class="ip-rating-cancel">
                <span class="ipr-loading ipr-hide"></span>
                <a href="#" data-id="<?php echo $post_id; ?>" class="ip-delete<?php echo $user_voted ? '' : ' ipr-hide'; ?>"><span>X</span></a>
            </span> <!-- .ip-rating-cancel -->

        </div> <!-- .ip-rating-container -->

        <?php
    }

}

// IMDB_Post_Ratings

$imdb_ratings = IMDB_Post_Ratings::init();