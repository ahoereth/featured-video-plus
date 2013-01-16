=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: https://github.com/ahoereth/featured-video-plus
Tags: featured video, featured image, featured, video, post video, post thumbnail, post, thumbnail, html5, flash, youtube, vimeo, dailymotion, mp4, webm, ogg, ogv, embed
Author: Alexander Höreth
Author URI: http://ahoereth.yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=a%2ehoereth%40gmail%2ecom
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.3

Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo, Dailymotion.

== Description ==
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos in addition to Featured Images. There are three ways to get the videos onto your page:

1. If your theme already makes use of Featured Images, these will __automatically__ be replaced by Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]`-__Shortcode__ in your entries or
3. manually use the __PHP functions__ in your theme's source files.

Beside your __Local Videos__ (`mp4`, `webM` & `ogg/ogv`) you can use videos from __YouTube__ (w/[time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")), __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus).

The plugin adds customization options to your Media Settings. Beside aesthetic individualizations you can turn off automatic integration and tweak settings regarding video sizing. By default videos try to dynamically fit their parent containers width.

= Shortcode =

	[featured-video-plus]
	[featured-video-plus width=300]

= PHP functions =
All parameters are optional. If no post_id is given the current post's id will be used.

	the_post_video(array(width, height))
	has_post_video(post_id)
	get_the_post_video(post_id, size(width, height))




*This plugin was created with the original [Featured Video](http://wordpress.org/extend/plugins/featured-video/) plugin in mind. __Featured Video Plus__ was freshly coded from ground up to bring you more features and to integrate more seamless into WordPress.*


== Installation ==

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "*Featured Video Plus*", and click "*Install Now*" below the plugins name
3. When the installation finished, click "*Activate Plugin*"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the right! Plugin specific settings can be found under *Settings -> Media*.


== Changelog ==

= 1.3 =
* Added internationalization capabilities
* Added German translation
* Revamped video sizing
* Added customizations for YouTube and Dailymotion
* Plenty better error handling
* Added contextual help on media settings and post edit screen
* Liveleak (experimental, they do not have any API)

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

= 1.3 =
Internationalization! More user friendly, more customizations.

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
Maybe the plugin does not recognize the URL. Take a look into the contextual help (button on the top right of the post edit screen). There is a list of tested URLs.

= How do I use my local videos? =
Take a look into the contextual help (button on the top right of the post edit screen).

= My theme uses Featured Images. Why are my videos not being displayed in place? =
For the videos to be automatically displayed you need to define a Featured Image. This image will never be shown if a video is available.
On the technical side your theme needs to feature [Post Thumbnails](http://codex.wordpress.org/Post_Thumbnails) and make use of `get_the_post_thumbnail()` or `the_post_thumbnail()`, because there is where the plugin hooks into.

If the automatic integration does not work, you can tell me in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using and I will take a look at it and might be able to develop a workaround.

= How can I make the videos fit the theme? =
Take a look at your media settings and try fixed sizes. For most installations the automatic sizing works fine. If tweaking does not help: [Tell me](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using.

= What about other video portals? =
Leave me a note in the support forums which you would like and I will consider adding them in the next release.

= How can I translate the plugin? =
Grap the [featured-video-plus.pot](https://github.com/ahoereth/featured-video-plus/blob/master/lng/featured-video-plus.pot) file, [translate it](http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/) and post it in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus). It will very lik be shipped with the next version.