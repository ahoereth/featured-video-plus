# Featured Video Plus - WordPress Plugin #
Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo, Dailymotion.

[On WordPress.org](http://wordpress.org/extend/plugins/featured-video-plus/)

## Description ##
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos in addition to Featured Images. There are three ways to get the videos onto your page:

1. If your theme already makes use of Featured Images, these will __automatically__ be replaced by Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]`-__Shortcode__ in your posts or
3. manually use the __PHP functions__ in your theme's source files.

Beside your __Local Videos__ (`mp4`, `webM` & `ogg/ogv`) you can use videos from __YouTube__, __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus). For YouTube and Dailymotion the plugin also features [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618).

The plugin adds customization options to your Media Settings. Beside aesthetic individualizations for each video platform's player you can turn off automatic integration, turn on autoplay, define your Dailymotion Syndication Key and tweak video sizing. By default videos try to dynamically fit their parent containers width.

### Shortcode ###

	[featured-video-plus]
	[featured-video-plus width=300]


### PHP functions ###

	the_post_video( $size )
	has_post_video( $post_id )
	get_the_post_video( $post_id, $size )
	get_the_post_video_image_url( $post_id )
	get_the_post_video_image( $post_id )

All parameters are optional. If no `$post_id` is given the current post's ID will be used. `$size` is either a string keyword (`thumbnail`, `medium`, `large` or `full`) or a 2-item array representing width and height in pixels, e.g. array(560,320).

## Changelog ##

### 1.4: 2013-03-15 ###
* __WP 3.5 Media Manager__ seamless integrated
* Time-links now available for YouTube and Dailymotion (append #t###1m2s)
* New `autoplay` setting
* Specify your Dailymotion Syndication Key
* Added `get_the_post_video_image` & `get_the_post_video_image_url`
* Local videos no longer break when domain changes or attachment is edited
* Better Featured Image handling

### 1.3: 2013-01-16 ###
* __Internationalization__: Added German translations
* Added customizations for YouTube and Dailymotion
* Revamped video sizing
* Better error handling
* Contextual help on media settings and post edit screen
* LiveLeak (very experimental, they have no API)

### 1.2: 2013-01-09 ###
* __Local Videos__: mp4, webm, ogg
* More dynamic user interface
* Minimized JS and CSS

### 1.1: 2012-12-16 ###
* __Dailymotion__
* Fixed YouTube time-links
* Enhanced interaction of Featured Videos & Featured Images

### 1.0: 2012-12-13 ###
* Release

