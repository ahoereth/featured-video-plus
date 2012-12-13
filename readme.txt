=== Featured Video Plus ===
Contributors: a.hoereth
Plugin Name: Featured Video Plus
Plugin URI: https://github.com/ahoereth/featured-video-plus
Tags: featured video, featured, post, video, thumbnail, post thumbnail, image, flash, youtube, vimeo
Author: Alexander Höreth
Author URI: http://ahoereth.yrnxt.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=a%2ehoereth%40gmail%2ecom
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.0

Add Featured Videos to your posts and pages, just like you add Featured Images. Works with every theme which supports Featured Images.


== Description ==

This plugin enables you to add videos as Featured Videos to posts and pages. A screen capture of the video will be added as Featured Image automatically and then by default be replaced by the video when viewing the site.
The Featured Videos can either be displayed inplace of Featured Images, can be added to the theme by editing the theme's source files or inserted in your posts manually using the shortcode.

The plugin will add an box to the admin interface's post and pages edit page where you can paste your videos URL. At the moment the plugin supports __YouTube__ (including [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")) and __Vimeo__.
If you are missing a certain video platform: Leave a message in the supports forum.

After activating the plugin you will get some additions to your media settings, where you can choose how the videos will be size and some other stuff - have a look at the screenshots. If the theme you are using does not work with any combination of the width and height settings please contact me and I will look into it.

Shortcode:

	[featured-video-plus]
	[featured-video-plus width=300]

	
Theme functions:

    the_post_video(array(width, height), fullscreen = true)
    has_post_video(post_id)
    get_the_post_video(post_id, size(width, height), fullscreen = true)
	
All parameters are optional. If no post_id is given the current post's id will be used.


This plugin was created after using the original [Featured Video](http://wordpress.org/extend/plugins/featured-video/) plugin with more features and a more seemless integration into WordPress in mind. I hope you like it, feel free to contact me by mail or post in the support forum.

== Installation ==

1. Visit your WordPress Administration interface and go to Plugins -> Add New
2. Search for "Featured Video Plus", and click "Install Now" below the plugins name
3. When the installation finished, click "Activate Plugin"

The plugin is ready to go. Now edit your posts and add video links to the "Featured Video" box on the top right!
If you want to change some settings have a look under Settings -> Media.


== Changelog ==

= 1.0 =
* Release


== Screenshots ==

1. Featured Video and Featured Image boxes on the post edit screen.
2. A Featured Video in the Twenty Twelve theme.
3. Settings -> Media screen


== Frequently Asked Questions ==

= After adding the URL and saving the post I do not get any video? =
Maybe the plugin does not recognize the URL. Try the URL you get when clicking on share below a youtube video or the simple vimeo URL, which should look something like this: http://vimeo.com/32071937
If you want to you can post the URL which is not working in the support forums and the plugin might work with it in the next release.

= What about other video portals? =
Leave me a note in the support forums which you would like and I will consider adding them in the next release.

= Are there translated versions? =
Not yet, but I will add translation capabilities soon.