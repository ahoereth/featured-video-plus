=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: https://github.com/ahoereth/featured-video-plus
Tags: featured video, featured image, featured, post video, post thumbnail, video, thumbnail, html5, flash, youtube, vimeo, dailymotion
Author: Alexander Höreth
Author URI: http://ahoereth.yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=a%2ehoereth%40gmail%2ecom
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.2

Add Featured Videos to your posts and pages, just like you add Featured Images. Works with every theme which supports Featured Images.


== Description ==

*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos for your posts and pages. When Featured Images are supported by your theme the Featured Videos will automatically be displayed inplace if available. The Featured Image will be used as fallback.
The Featured Videos can either be displayed inplace of Featured Images, can be added to the theme by editing the theme's source files or inserted in your posts manually using the shortcode.

The plugin will add an box to the admin interface's post and pages edit page where you can paste your videos URL. At the moment the plugin supports __YouTube__ (including [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")), __Vimeo__ and __Dailymotion__. As experimental feature the plugin now also supports your __local videos__.
If you are missing a certain video platform: Leave a message in the supports forum.

After activating the plugin you will get some additions to your media settings. There you can choose how the videos will be sized and get some other individualisation properties - have a look at the [screenshots](http://wordpress.org/extend/plugins/featured-video-plus/screenshots/). If the theme you are using does not work with any combination of the width and height settings please contact me and I will look into it.

__Shortcode:__

	[featured-video-plus]
	[featured-video-plus width=300]


__PHP functions:__

	the_post_video(array(width, height), fullscreen = true)
	has_post_video(post_id)
	get_the_post_video(post_id, size(width, height), fullscreen = true)

All parameters are optional. If no post_id is given the current post's id will be used.


This plugin was created after using the original [Featured Video](http://wordpress.org/extend/plugins/featured-video/) plugin. Featured Video Plus is a complete remake with more features and a more seemless integration into WordPress.

== Installation ==

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "Featured Video Plus", and click "Install Now" below the plugins name
3. When the installation finished, click "Activate Plugin"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the right!
If you want to change some settings have a look under Settings -> Media.


== Changelog ==

= 1.2 =
* __Added experimental support for local videos__. Activate under settings.
* Allow webM mime type for media upload
* Added Media Settings link in plugin info
* fixed some small bugs

= 1.1 =
* __Added Dailymotion__
* fixed youtube 'start at specific time' embeds
* overhaul of the interaction between Featured Videos and Featured Images
* existing featured images will no longer be replaced by newly added featured videos in the administration interface

= 1.0 =
* Release


== Upgrade Notice ==

= 1.2 =
Now you can use your local videos.

= 1.1 =
Feature Dailymotion Videos on your posts!

== Screenshots ==

1. Featured Video and Featured Image boxes on the post edit screen.
2. A Featured Video in the Twenty Twelve theme.
3. Settings -> Media screen


== Frequently Asked Questions ==

= After adding the URL and saving the post I do not get any video? =
Maybe the plugin does not recognize the URL. Try the URL you get when clicking on share below a youtube video or the simple vimeo URL, which should look something like this: http://vimeo.com/32071937
If you want to you can post the URL which is not working in the support forums and the plugin might work with it in the next release.

= I activated local video support, how do I use it? =
* Add a __mp4__, __webM__ or __ogv__ video to your Media Library
* Copy the ´Link To Media File´ and paste it into the Featured Video box
* For better [compatibility](http://videojs.com/#compatibilitychart) upload a second version of the same video with a different format and paste the URL in the second input box as a fallback.

= What is the easiest way to get my video into these formats? =
Take a look at the [Miro Video Converter](http://www.mirovideoconverter.com/). It is open source, lightweight and compatible with Windows, Mac and Linux.

= What can I do about those errors I get when uploading my video? =
* Read [this](http://www.wpbeginner.com/wp-tutorials/how-to-increase-the-maximum-file-upload-size-in-wordpress/) on how to increase maximum file upload size.
* WordPress by default does not support webM. The plugin activates it, but under some conditions this might not be enough and you might want to take a look at this [post](http://ottopress.com/2011/howto-html5-video-that-works-almost-everywhere/).

= What happens if the user does not use a HTML5 compatible browser? =
The video player, [VIDEOJS](http://videojs.com/), features an adobe flash fallback if you provide an MP4 video.

= What about other video portals? =
Leave me a note in the support forums which you would like and I will consider adding them in the next release.

= Are there translations available? =
Not yet, but I will add translation capabilities soon. Interested in translating the plugin? Contact me!