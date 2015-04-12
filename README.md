# Featured Video Plus #

Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo, Dailymotion.

## Description ##
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos, which can automatically be displayed instead of Featured Images. There are three ways to get the videos onto your page:

1. If your theme already makes use of  [Featured Images](http://codex.wordpress.org/Post_Thumbnails), these will in most themes __automatically__ be replaced by your Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]`-__Shortcode__ in your posts or
3. manually make use of the __PHP functions__ in your theme's source files.

Additionally you can choose to only show the video after the user clicks the original image - either in a lightbox or inline.

See the plugin in action on [yrnxt.com](http://yrnxt.com/wordpress/featured-video-plus/). Also take a look at the [Garvan](http://www.web2feel.com/garvan/) video blogging theme which makes optimal use of this plugin.

Beside your __Local Videos__ you can use videos from __YouTube__, __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus). For YouTube and Dailymotion the plugin also features [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618). If some site is not supported by the plugin you can still use all `iframe`-embed codes.

The plugin adds customization options to your Media Settings. Beside aesthetic individualizations for each video platform's player you can turn off automatic featured image replacement, turn on autoplay or looping and tweak video sizing. By default videos try to dynamically fit their parent containers width. Take a look at *Settings -> Media*.

### Support ###
I do read all support questions in the forum but cannot reply to all of them. The plugin is an unpaid side project. If you need support consider [buying me a cookie](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8UKMQW2DMM6) - best way to attract my attention.

### Shortcode ###

	[featured-video-plus]
	[featured-video-plus width=300]

### PHP functions ###

	the_post_video( $size )
	has_post_video( $post_id )
	get_the_post_video( $post_id, $size )
	get_the_post_video_url( $post_id )
	get_the_post_video_image( $post_id )
	get_the_post_video_image_url( $post_id )

All parameters are optional. If no `$post_id` is given the current post's ID will be used. `$size` is either a string keyword (`thumbnail`, `medium`, `large` or `full`) or a 2-item array representing width and height in pixels, e.g. array(560,320).


## Installation ##

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "*Featured Video Plus*", and click "*Install Now*" below the plugin's name
3. When the installation finished, click "*Activate Plugin*"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the right! Plugin specific settings can be found under *Settings -> Media*.



## Screenshots ##

1. Featured Video and Featured Image boxes on the post edit screen.
![1. Featured Video and Featured Image boxes on the post edit screen.](https://ps.w.org/featured-video-plus/assets/screenshot-1.jpg)

2. A Featured Video in the Twenty Twelve theme.
![2. A Featured Video in the Twenty Twelve theme.](https://ps.w.org/featured-video-plus/assets/screenshot-2.jpg)

3. Settings -> Media screen
![3. Settings -> Media screen](https://ps.w.org/featured-video-plus/assets/screenshot-3.png)



