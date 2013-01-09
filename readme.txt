=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: https://github.com/ahoereth/featured-video-plus
Tags: featured video, featured image, featured, video, post video, post thumbnail, post, thumbnail, html5, flash, youtube, vimeo, dailymotion, mp4, webm, ogg, ogv
Author: Alexander Höreth
Author URI: http://ahoereth.yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=a%2ehoereth%40gmail%2ecom
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.2

Add Featured Videos to your posts and pages, just like you add Featured Images. Supports your Local Videos, YouTube, Vimeo and Dailymotion.


== Description ==
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos in addition to Featured Images. Themes using Featured Images automatically display the videos in place.

There are three ways to get the videos onto your page:
1. If your theme already makes use of Featured Images, these will automatically be replaced by Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]` shortcode in your entries or
3. manually use the PHP functions in your theme's source files.

Beside your __Local Videos__ (mp4, webM & ogg/ogv) you can use __YouTube__ (w/[time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")), __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus).

The plugin adds some individualization options to your Media Settings. Beside aesthetic customizations you can turn off automatic integration and tweak some technical settings.

=Shortcode:=

	[featured-video-plus]
	[featured-video-plus width=300]

=PHP functions:=

	the_post_video(array(width, height), fullscreen = true)
	has_post_video(post_id)
	get_the_post_video(post_id, size(width, height), fullscreen = true)

All parameters are optional. If no post_id is given the current post's id will be used.



*This plugin was created with the original [Featured Video](http://wordpress.org/extend/plugins/featured-video/) plugin in mind. __Featured Video Plus__ was freshly coded from ground up to be more powerful, bring you more features and to integrate more seamless into WordPress.*


== Installation ==

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "Featured Video Plus", and click "Install Now" below the plugins name
3. When the installation finished, click "Activate Plugin"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the right! If you want to use Local Videos they need to be `mp4`, `webM` or `ogg/ogv`. If you want to change some settings have a look under Settings -> Media.


== Changelog ==

= 1.2 =
* __Added support for local videos__
* Allow webM mime type for media upload
* Added Media Settings link in plugin info
* More notices/warnings
* More JS
* minimized JS and CSS
* fixed some other stuff

= 1.1 =
* __Added Dailymotion__
* fixed youtube 'start at specific time' embeds
* overhaul of the interaction between Featured Videos and Featured Images
* existing featured images will no longer be replaced by newly added featured videos in the administration interface

= 1.0 =
* Release


== Upgrade Notice ==

= 1.2 =
Now featuring your local videos!

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

= My theme uses Featured Images. Why are my videos not being displayed in place? =
For the videos to be automatically displayed you need to define a Featured Image. This image will never be shown if a video is available.
On the technical side your theme needs to feature [Post Thumbnails](http://codex.wordpress.org/Post_Thumbnails) and make use of `get_the_post_thumbnail()` or `the_post_thumbnail()`, because there is where the plugin hooks into.

If the automatic integration does not work, you can tell me in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using and I will take a look at it and might be able to develop a workaround.

= Why do my videos do not fit their container? =
Take a look at your media settings and try tweaking the video size. For most installations the sizing works fine by default, if it still does not: I'm [happy to help](http://wordpress.org/support/plugin/featured-video-plus).

= I activated local video support, how do I use it? =
* Add a __mp4__, __webM__ or __ogv__ video to your Media Library
* Copy the `Link To Media File` and paste it into the Featured Video box
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