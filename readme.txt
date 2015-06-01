=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: http://yrnxt.com/wordpress/featured-video-plus/
Tags: featured, post, video, videos, image, thumbnail, html5, flash, lazy, overlay, youtube, vimeo, dailymotion, soundcloud, spotify
Author: Alexander Höreth
Author URI: http://yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8UKMQW2DMM6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.7
Tested up to: 4.2.2
Stable tag: 2.0.2

Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo and many more.



== Description ==
> A picture is worth a thousand words. How many words is a video worth?

Featured Videos work like Featured Images, just smoother: Paste a video URL into the designated new box on the post edit screen and the video will be displayed in place of a post image.

There are three ways to get the videos onto your page:

1. **Automagically!** If your theme makes use of WordPress' native [featured image functionality](http://codex.wordpress.org/Post_Thumbnails) you are set: Automatic insertion, lazy loading or lightbox overlays, its your choice. If this does not work you can either
2. insert the `[featured-video-plus]`-__Shortcode__ in your posts or
3. manually make use of the __PHP-functions__ in your theme's source files.

> Sadly many themes do not follow the WordPress standards and implement their own fancy functions for displaying featured images - check out the [FAQ](https://wordpress.org/plugins/featured-video-plus/faq/). Another common problem are sliders - in general: Videos do not like sliders at all.

See the plugin in action on [yrnxt.com](http://yrnxt.com/wordpress/featured-video-plus/). There is a button in the sidebar to switch between the different featured video display modes: [Automatic](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=replace), [lazy](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=dynamic) and [overlay](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=overlay).

Besides **Local Videos** you can use videos from a whole lot of external providers like **YouTube**, **Vimeo** and **Dailymotion**. **SoundCloud** and **Spotify** (including playlists) are supported as well. Check the [WordPress Codex](http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F) for a complete list. If some provider is not listed you can always just use an embed code or whatever HTML you like.

After installing the plugin check your site's *Media Settings* (`Settings -> Media` in the administration interface): The plugin adds quite some little helper options there. Change to lazy or overlay mode, tweak video sizing, individualize the look of the most prominent providers' video players and turn on autoplay or video looping. By default videos try to dynamically fit their parent containers width and adjust their size responsively.

= Support =
I do read all support questions in the [forums](https://wordpress.org/support/plugin/featured-video-plus) but cannot reply to all of them. The plugin is an unpaid side project and full support would require more time than I can invest for free for over 10k active installs. If you really need help, consider [buying me a cookie](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8UKMQW2DMM6) - best way to attract my attention and to support future enhancements.



== Installation ==

= Installation =

1. Visit your WordPress Administration interface and go to `Plugins -> Add New`
2. Search for `Featured Video Plus`, and click `Install Now` below the plugin's name
3. When the installation finished, click `Activate Plugin`

The plugin is ready to go. Now edit your posts and add video links to the `Featured Video` box on the right! Plugin specific settings can be found under `Settings -> Media`.

= Theme integration =

If the automatic integration fails you can always fallback to either using the shortcode or adjusting your themes sourcecode manually:

**Shortcode**

	[featured-video-plus]
	[featured-video-plus width=300]

**PHP-functions**

	the_post_video( $size )
	has_post_video( $post_id )
	get_the_post_video( $post_id, $size )
	get_the_post_video_url( $post_id )
	get_the_post_video_image( $post_id )
	get_the_post_video_image_url( $post_id )

All parameters are optional. If no `$post_id` is given the current post's ID will be used. `$size` is either a string keyword (`thumbnail`, `medium`, `large` or `full`) or a 2-item array representing width and height in pixels, e.g. `array(560,320)`.

When editing your theme's sourcecode keep in mind that a future update through WordPress.org might overwrite your changes. Consider creating a child theme to prevent that.



== Screenshots ==

1. A Featured Video in the Twenty Fifteen theme on [yrnxt.com](http://yrnxt.com/wordpress/featured-video-plus).
2. Featured Video and Featured Image boxes on the post edit screen.
3. Featured Video settings on the `Settings -> Media` administration screen.



== Frequently Asked Questions ==

= Why do I just get text back after adding an URL to the Featured Video input? =
If the plugin just displays the URL back as text it probably does not recognize that it comes from a video provider. Try inserting the raw embed code instead and [check the docs](http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F) to see which providers are supported.

= How do I use my local videos? =
Click the small media icon in the Featured Video input box on the post edit screen and upload your video or choose it from the media library. WordPress does not support all formats tho, [check this table](http://www.mediaelementjs.com/#devices) for details.

= Why do I not see a featuerd video or image on the frontend at all? =
For the videos to be automatically displayed you need to define a Featured Image. This image will never be shown if a video is available. If your theme does not support featured images the plugin also has no chance of working out of the box.

= Why does the frontend still display the featured image although I added a featured video to the post? =
Sadly not all themes work out of the box. Themes need to make use of WordPress' native [Post Thumbnail](http://codex.wordpress.org/Post_Thumbnails) functionality (specifically `get_the_post_thumbnail()` and/or `the_post_thumbnail()`) - these functions are where the plugin can hook into the theme and modify what is displayed. Consider contacting the theme's creator or modifying the theme's sourcecode in order to add the plugin's [PHP-functions](https://wordpress.org/plugins/featured-video-plus/installation/).

= How can I make the videos fit into their designated space in my theme? =
Take a look at your media settings and try using a fixed width instead of responsive sizing.

= Can I help translating the plugin? =
Yes, please! Check out the public [Featured Video Plus Translation Project](https://poeditor.com/join/project?hash=WlyLh0cFO3).



== Upgrade Notice ==

= 2.0.2 =
Only upgrade when using WordPress 3.7 or higher! Big refactor with support for more video providers.



== Changelog ==

= 2.0.0, 2.0.1, 2.0.2: 2015-06-01 =
* __Requires WordPress 3.7 or higher now!__ This reflects versions of WordPress which are "officially" [supported](https://codex.wordpress.org/Supported_Versions). The plugin will from now on try to stick to supporting all versions listed there.
* Major code refactor which results in many bugs scrubbed.
* Support for raw embed codes and [all WordPress core media providers](https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F).
* Updated wp.org icon and cover.


= 1.9.1: 2014-09-06 =
* __Last update compatible all the way back to WordPress 3.2!__
* You can now specify the '[end](https://developers.google.com/youtube/player_parameters#end)' parameter for YouTube embeds ([*](http://wordpress.org/support/topic/how-to-specify-start-and-end-for-youtube-videos))
* Added option for only displaying videos on single posts/pages ([*](http://wordpress.org/support/topic/i-need-to-only-change-the-featured-images-not-the-thumbnails),[*](http://wordpress.org/support/topic/video-thumbnails-with-link-to-post),[*](http://wordpress.org/support/topic/want-everything-of-fvp-other-than-feature-video-thumb))
* Removed hardcoded http protocol for embeds [*](http://wordpress.org/support/topic/fix-for-videos-over-ssl)

= 1.9: 2014-01-02 =
* Replaced Video.js with MediaElement.js (ships with WordPress since 3.6 - __breaks local videos partially if you use an older WordPress version!__)
* Added Spanish translations! Translation by [WebHostingHub.com](http://webhostinghub.com)
* Updated FitVids.js to 1.0.3

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
