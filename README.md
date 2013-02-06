IMDB Post Rating
====================

This is a simple yet another rating plugin.


Features
---------------
1. Creates a table `{wpprefix}_imdb_rating`
2. Using `IMDB_Post_Ratings::init()->rating_input();` inside a post, provides a **10 star** rating.
3. You can give a rating, also then can remove the rating, give again…

Functions
------------------

1. `IMDB_Post_Ratings::init()->rating_input( $post_id )` Inserts the rating star bars.
1. `IMDB_Post_Ratings::init()->get_top_rated()` - get top rated posts. Supports **3** parameters, `post_type`, `limit`, `offset`
1. `IMDB_Post_Ratings::init()->get_rating( $post_id )` - returns rating for single post.

Screenshot
---------------
![screenshot](screenshot.png)


Author
----------------------------
[Tareq Hasan](http://tareq.wedevs.com)