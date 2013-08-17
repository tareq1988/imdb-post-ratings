<?php

/**
 * New WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class IMDB_Post_Ratings_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     * */
    function IMDB_Post_Ratings_Widget() {
        $widget_ops = array('classname' => 'imdb-most-rated', 'description' => 'Most rated widget');
        $this->WP_Widget( 'imdb-most-rated', 'Top Rated Posts', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array $args An array of standard parameters for widgets in this theme
     * @param array $instance An array of settings for this widget instance
     * @return void Echoes it's output
     * */
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;

        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

        $posts = IMDB_Post_Ratings::init()->get_top_rated( $instance['post_type'], $instance['limit'] );

        echo '<ul>';
        if ( $posts ) {
            foreach ($posts as $item) {
                $extra = $instance['show_rating'] == 'on' ? ' - <span class="ip-rating-txt">' . number_format( $item->rating, 2 ) . '</span>' : '';
                printf( '<li><a href="%s">%s</a>%s</li>', get_permalink( $item->post_id ), get_the_title( $item->post_id ), $extra );
            }
        } else {
            printf( '<li>%s</li>', __( 'Nothing found', 'ipr' ) );
        }
        echo '</ul>';

        echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array $new_instance An array of new settings as submitted by the admin
     * @param array $old_instance An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     * */
    function update( $new_instance, $old_instance ) {
        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array $instance An array of the current settings for this widget
     * @return void Echoes it's output
     * */
    function form( $instance ) {
        $defaults = array(
            'title' => __( 'Top rated', 'ipr' ),
            'post_type' => 'post',
            'limit' => 10,
            'show_rating' => 'on'
        );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = esc_attr( $instance['title'] );
        $post_type = esc_attr( $instance['post_type'] );
        $limit = esc_attr( $instance['limit'] );
        $show_rating = $instance['show_rating'] == 'on' ? 'on' : 'off';

        $post_types = get_post_types();
        unset( $post_types['nav_menu_item'] );
        unset( $post_types['revision'] );
        ?>
        <p>
            <label><?php _e( 'Title:', 'ipr' ); ?> </label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label><?php _e( 'Post Type:', 'ipr' ) ?></label>
            <select id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" >
                <?php foreach ($post_types as $pt) { ?>
                    <option value="<?php echo $pt; ?>" <?php selected( $post_type, $pt ) ?>><?php echo $pt; ?></option>
                <?php } ?>
            </select>
        </p>

        <p>
            <label><?php _e( 'Limit:', 'wedevs' ); ?> </label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" />
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $show_rating, 'on' ); ?> id="<?php echo $this->get_field_id( 'show_rating' ); ?>" name="<?php echo $this->get_field_name( 'show_rating' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_rating' ); ?>"><?php _e( 'Show Rating', 'ipr' ) ?></label>
        </p>
        <?php
    }

}

add_action( 'widgets_init', create_function( '', "register_widget('IMDB_Post_Ratings_Widget');" ) );

