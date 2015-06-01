# Changelog #

## 2.0.0, 2.0.1, 2.0.2: 2015-06-01 ##
* __Requires WordPress 3.7 or higher now!__ This reflects versions of WordPress which are "officially" [supported](https://codex.wordpress.org/Supported_Versions). The plugin will from now on try to stick to supporting all versions listed there.
* Major code refactor which results in many bugs scrubbed.
* Support for raw embed codes and [all WordPress core media providers](https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F).
* Updated wp.org icon and cover.


## 1.9.1: 2014-09-06 ##
* __Last update compatible all the way back to WordPress 3.2!__
* You can now specify the '[end](https://developers.google.com/youtube/player_parameters#end)' parameter for YouTube embeds ([*](http://wordpress.org/support/topic/how-to-specify-start-and-end-for-youtube-videos))
* Added option for only displaying videos on single posts/pages ([*](http://wordpress.org/support/topic/i-need-to-only-change-the-featured-images-not-the-thumbnails),[*](http://wordpress.org/support/topic/video-thumbnails-with-link-to-post),[*](http://wordpress.org/support/topic/want-everything-of-fvp-other-than-feature-video-thumb))
* Removed hardcoded http protocol for embeds [*](http://wordpress.org/support/topic/fix-for-videos-over-ssl)

## 1.9: 2014-01-02 ##
* Replaced Video.js with MediaElement.js (ships with WordPress since 3.6 - __breaks local videos partially if you use an older WordPress version!__)
* Added Spanish translations! Translation by [WebHostingHub.com](http://webhostinghub.com)
* Updated FitVids.js to 1.0.3

## 1.8: 2013-05-16 ##
* Video.js [4.0](http://blog.videojs.com/post/50021214078/video-js-4-0-now-available)
* Customize the local video player
* Better autoplay handling
* Remove anchors wrapping videos
* General bug fixes

## 1.7.1: 2013-04-30 ##
* Fixed manual usage option ([*](http://wordpress.org/support/topic/lightbox-video-on-featured-image-click))
* Added featured image mouse over effect for featured video AJAX usage

## 1.7: 2013-04-30 ##
* Added functionality to display featured video in an lightbox using AJAX on featured image click ([*](http://www.web2feel.com/garvan/))
* Added functionality to replace featured image with featured video on demand when image is clicked using AJAX ([*](http://wordpress.org/support/topic/lightbox-video-on-featured-image-click))
* `get_the_post_video_url` has a new second parameter (boolean) to get the fallback video's URL ([*](http://wordpress.org/support/topic/fallback-video-url))
* Tested with WordPress 3.6

## 1.6.1: 2013-04-18 ##
* Fixed removing featured image when no featured video is specified ([*](http://wordpress.org/support/topic/featured-image-doesnt-save))

## 1.6: 2013-04-16 ##
* Added `get_the_post_video_url($post_id)` PHP-Function
* Added YouTube `enablejsapi` parameter with `playerapiid` (`fvpid + $post_id`) and iframe id ([*](http://wordpress.org/support/topic/need-filter-for-iframe-and-embed-code-manipulation))
* Added a filter for `get_the_post_video`: `get_the_post_video_filter` ([*](http://wordpress.org/support/topic/need-filter-for-iframe-and-embed-code-manipulation))
* Added option for using the featured image as video thumbnail for local videos
* Fixed local videoJS ([*](http://wordpress.org/support/topic/how-to-style-the-player-play-button-pause-button-etc))
* Fixed auto width and height for the Dailymotion and videoJS players
* Fixed YouTube videos for which the plugin cannot access the YouTube API ([*](http://wordpress.org/support/topic/link-appearing-red-in-featured-video-section))

## 1.5.1: 2013-03-27 ##
* Fixed Featured Video box on new-post.php
* Enhanced Featured Image ajax behavior

## 1.5: 2013-03-22 ##
* __AJAXified__ the Featured Video box - just like Featured Images
* Added options for a) disabling VideoJS JS/CSS, b) enabling VideoJS CDN and c) YouTube `wmode`
* Plugin no longer breaks WP image editor ([*](http://wordpress.org/support/topic/breaks-image-scaling-shows-nan))

## 1.4: 2013-03-15 ##
* __WP 3.5 Media Manager__ seamless integrated
* Time-links now available for YouTube and Dailymotion (append #t=1m2s)
* New `autoplay` setting
* Specify your Dailymotion Syndication Key
* Added `get_the_post_video_image` & `get_the_post_video_image_url`
* Local videos no longer break when domain changes or attachment is edited
* Better Featured Image handling

## 1.3: 2013-01-16 ##
* __Internationalization__: Added German translations
* Added customizations for YouTube and Dailymotion
* Revamped video sizing
* Better error handling
* Contextual help on media settings and post edit screen
* LiveLeak (very experimental, they have no API)

## 1.2: 2013-01-09 ##
* __Local Videos__: mp4, webm, ogg
* More dynamic user interface
* Minimized JS and CSS

## 1.1: 2012-12-16 ##
* __Dailymotion__
* Fixed YouTube time-links
* Enhanced interaction of Featured Videos & Featured Images

## 1.0: 2012-12-13 ##
* Release
