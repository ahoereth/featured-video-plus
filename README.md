# Featured Video Plus - WordPress Plugin #
Add Featured Videos to your posts and pages, just like you add Featured Images. Supports your Local Videos, YouTube, Vimeo and Dailymotion.

[On WordPress.org](http://wordpress.org/extend/plugins/featured-video-plus/)

## Description ##
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos in addition to Featured Images. Themes using Featured Images automatically display the videos in place.

There are three ways to get the videos onto your page:
1. If your theme already makes use of Featured Images, these will automatically be replaced by Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]` shortcode in your entries or
3. manually use the PHP functions in your theme's source files.

Beside your __Local Videos__ (´mp4´, ´webM´ & ´ogg/ogv´) you can use __YouTube__ (w/[time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")), __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus).

The plugin adds some individualization options to your Media Settings. Beside aesthetic customizations you can turn off automatic integration and tweak some technical settings.

### Shortcode ###

	[featured-video-plus]
	[featured-video-plus width=300]


### Theme functions ###

    the_post_video(array(width, height), fullscreen = true)
    has_post_video(post_id)
    get_the_post_video(post_id, size(width, height), fullscreen = true)

All parameters are optional. If no post_id is given the current post's id will be used.

## Changelog ##
### 1.2 ###
* __Added support for local videos__
* Allow webM mime type for media upload
* Added Media Settings link in plugin info
* More notices/warnings
* More JS
* minimized JS and CSS
* fixed some other stuff

### 1.1 ###
* __Added Dailymotion__
* fixed youtube 'start at specific time' embeds
* overhaul of the interaction between Featured Videos and Featured Images
* existing featured images will no longer be replaced by newly added featured videos in the administration interface

### 1.0 ###
* Release
