=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: http://yrnxt.com/category/wordpress/featured-video-plus/
Tags: featured, post, video, image, thumbnail, html5, flash, youtube, vimeo, dailymotion, mp4, webm, ogg, embed, ajax
Author: Alexander Höreth
Author URI: http://yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=a%2ehoereth%40gmail%2ecom
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.1
Tested up to: 3.6
Stable tag: 1.8

Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo, Dailymotion.

== Description ==
*A picture is worth a thousand words. How many words is a video worth?*

This plugin enables you to define Featured Videos, which, if set, take the place of Featured Images. There are three ways to get the videos onto your page:

1. If your theme already makes use of  [Featured Images](http://codex.wordpress.org/Post_Thumbnails), these will in most themes __automatically__ be replaced by your Featured Videos if available. Alternatively you can
2. insert the `[featured-video-plus]`-__Shortcode__ in your posts or
3. manually make use of the __PHP functions__ in your theme's source files.

Instead of option 1 the plugin can also request the videos using an AJAX request when the featured image is clicked. This reduces load times and gives you the flexibility to display videos in a lightbox to ensure your theme does not break.

See the theme in action on [yrnxt.com](http://yrnxt.com/wordpress/featured-video-plus/). Also take a look at the [Garvan](http://www.web2feel.com/garvan/) video blogging theme.

Beside your __Local Videos__ (`mp4`, `webM` & `ogg/ogv`) you can use videos from __YouTube__, __Vimeo__ and __Dailymotion__. If you miss a certain video platform: [Leave me a note](http://wordpress.org/support/plugin/featured-video-plus). For YouTube and Dailymotion the plugin also features [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618).

The plugin adds customization options to your Media Settings. Beside aesthetic individualizations for each video platform's player you can turn off automatic integration, turn on autoplay, define your Dailymotion Syndication Key and tweak video sizing. By default videos try to dynamically fit their parent containers width. Take a look at *Settings -> Media*.

= Shortcode =

	[featured-video-plus]
	[featured-video-plus width=300]

= PHP functions =

	the_post_video( $size )
	has_post_video( $post_id )
	get_the_post_video( $post_id, $size )
	get_the_post_video_url( $post_id )
	get_the_post_video_image_url( $post_id, $fallback )
	get_the_post_video_image( $post_id )

All parameters are optional. If no `$post_id` is given the current post's ID will be used. `$size` is either a string keyword (`thumbnail`, `medium`, `large` or `full`) or a 2-item array representing width and height in pixels, e.g. array(560,320). $fallback by default is false, when set to true this will return the fallback URL for local videos.




*This plugin was created with the original [Featured Video](http://wordpress.org/extend/plugins/featured-video/) plugin in mind. __Featured Video Plus__ was freshly coded from ground up to bring you more features and to integrate more seamless into WordPress.*


== Installation ==

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "*Featured Video Plus*", and click "*Install Now*" below the plugin's name
3. When the installation finished, click "*Activate Plugin*"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the right! Plugin specific settings can be found under *Settings -> Media*.


== Changelog ==

= 1.8: 2013-05-16 =
* Video.js [4.0](http://blog.videojs.com/post/50021214078/video-js-4-0-now-available)
* Customize the local video player
* Better autoplay handling
* Remove anchors wrapping videos
* General bug fixes

= 1.7.1: 2013-04-30 =
* Fixed manual usage option ([*](http://wordpress.org/support/topic/lightbox-video-on-featured-image-click))
* Added featured image mouse over effect for featured video AJAX usage

= 1.7: 2013-04-30 =
* Added functionality to display featured video in an lightbox using AJAX on featured image click ([*](http://www.web2feel.com/garvan/))
* Added functionality to replace featured image with featured video on demand when image is clicked using AJAX ([*](http://wordpress.org/support/topic/lightbox-video-on-featured-image-click))
* `get_the_post_video_url` has a new second parameter (boolean) to get the fallback video's URL ([*](http://wordpress.org/support/topic/fallback-video-url))
* Tested with WordPress 3.6

= 1.6.1: 2013-04-18 =
* Fixed removing featured image when no featured video is specified ([*](http://wordpress.org/support/topic/featured-image-doesnt-save))

= 1.6: 2013-04-16 =
* Added `get_the_post_video_url($post_id)` PHP-Function
* Added YouTube `enablejsapi` parameter with `playerapiid` (`fvpid + $post_id`) and iframe id ([*](http://wordpress.org/support/topic/need-filter-for-iframe-and-embed-code-manipulation))
* Added a filter for `get_the_post_video`: `get_the_post_video_filter` ([*](http://wordpress.org/support/topic/need-filter-for-iframe-and-embed-code-manipulation))
* Added option for using the featured image as video thumbnail for local videos
* Fixed local videoJS ([*](http://wordpress.org/support/topic/how-to-style-the-player-play-button-pause-button-etc))
* Fixed auto width and height for the Dailymotion and videoJS players
* Fixed YouTube videos for which the plugin cannot access the YouTube API ([*](http://wordpress.org/support/topic/link-appearing-red-in-featured-video-section))

= 1.5.1: 2013-03-27 =
* Fixed Featured Video box on new-post.php
* Enhanced Featured Image ajax behavior

= 1.5: 2013-03-22 =
* __AJAXified__ the Featured Video box - just like Featured Images
* Added options for a) disabling VideoJS JS/CSS, b) enabling VideoJS CDN and c) YouTube `wmode`
* Plugin no longer breaks WP image editor ([*](http://wordpress.org/support/topic/breaks-image-scaling-shows-nan))

= 1.4: 2013-03-15 =
* __WP 3.5 Media Manager__ seamless integrated
* Time-links now available for YouTube and Dailymotion (append #t=1m2s)
* New `autoplay` setting
* Specify your Dailymotion Syndication Key
* Added `get_the_post_video_image` & `get_the_post_video_image_url`
* Local videos no longer break when domain changes or attachment is edited
* Better Featured Image handling

= 1.3: 2013-01-16 =
* __Internationalization__: Added German translations
* Added customizations for YouTube and Dailymotion
* Revamped video sizing
* Better error handling
* Contextual help on media settings and post edit screen
* LiveLeak (very experimental, they have no API)

= 1.2: 2013-01-09 =
* __Local Videos__: mp4, webm, ogg
* More dynamic user interface
* Minimized JS and CSS

= 1.1: 2012-12-16 =
* __Dailymotion__
* Fixed YouTube time-links
* Enhanced interaction of Featured Videos & Featured Images

= 1.0: 2012-12-13 =
* Release


== Upgrade Notice ==

= 1.6 =
Smoothness

= 1.5 =
AJAX!

= 1.4 =
WP3.5 Media Manager, time-links...

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
Maybe the plugin does not recognize the URL. Take a look into the contextual help (button on the top right of the post edit screen). There is a list what the URLs should look like. If this does not help leave a note in the support forum.

= The input box has a red background - but the video works just fine. Whats going on? =
With every video you insert into the meta box the plugin tries to access the API of the
according video provider to grab information about the video and pull an image. When this API
access fails the input box gets a red background. When for example the server you are using is
located in Germany it cannot access the YouTube API for videos blocked in this country - still
you and your visitors might be able to watch the videos as normal. The plugin cannot test for this.

= How do I use my local videos? =
Take a look into the contextual help (button on the top right of the post edit screen).

= My theme uses Featured Images. Why are my videos not being displayed in place? =
For the videos to be automatically displayed you need to define a Featured Image. This image will never be shown if a video is available.
Beside this your theme needs to feature [Post Thumbnails](http://codex.wordpress.org/Post_Thumbnails) and make use of `get_the_post_thumbnail()` or `the_post_thumbnail()`, because these are the core functions the plugin hooks into.

If the automatic integration does not work, you can tell me in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using and I will take a look at it and might be able to develop a workaround.

= How can I make the videos fit the theme? =
Take a look at your media settings and try different fixed sizes. If tweaking those does not help: [Tell me](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using.

= What about other video providers? =
Leave me a note in the support forums which video platforms you would like to see in a feature release!

= How can I translate the plugin? =
Grap the [featured-video-plus.pot](https://github.com/ahoereth/featured-video-plus/blob/master/lng/featured-video-plus.pot) file, [translate it](http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/) and post it in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus). It will very lik be shipped with the next version.