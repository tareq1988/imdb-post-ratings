=== IMDB Post Rating ===
Contributors: tareq1988
Donate Link: http://tareq.wedevs.com/donate/
Tags: rating, star, star rating, rank, post rating
Requires at least: 3.3
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a simple yet another rating plugin.

== Description ==

This is a simple yet another rating plugin for WordPress.

= Features =

1. Creates a table `{wpprefix}_imdb_rating`
2. Using `IMDB_Post_Ratings::init()->rating_input();` inside a post, provides a **10 star** rating. Change the number using the filter `ip_base_rating`. Cool huh?
3. You can give a rating, also then can remove the rating, give again, remove again, give again…

= Functions =

1. `IMDB_Post_Ratings::init()->rating_input( $post_id )` Inserts the rating star bars.
1. `IMDB_Post_Ratings::init()->get_top_rated()` - get top rated posts. Supports **3** parameters, `post_type`, `limit`, `offset`
1. `IMDB_Post_Ratings::init()->get_rating( $post_id )` - returns rating for single post.

= Contribute =
This may have bugs and lack of many features. If you want to contribute on this project, you are more than welcome. Please fork the repository from [Github](https://github.com/tareq1988/imdb-post-ratings).

= Author =
Brought to you by [Tareq Hasan](http://tareq.wedevs.com) from [weDevs](http://wedevs.com)

= Donate =
Please [donate](http://tareq.wedevs.com/donate/) for this awesome plugin to continue it's development to bring more awesome features.


== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

Nothing here

== Screenshots ==

1. Rating

== Changelog ==

= 0.1 =
Initial version released


== Upgrade Notice ==

Nothing here
