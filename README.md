Featured Video Plus - WordPress Plugin
=============

Add Featured Videos to your posts and pages, just like you add Featured Images. Works with every theme which supports Featured Images.

Description
-------

This plugin enables you to add videos as Featured Videos to posts and pages. A screen capture of the video will be added as Featured Image automatically and then by default be replaced by the video when viewing the site.
The Featured Videos can either be displayed inplace of Featured Images, can be added to the theme by editing the theme's source files or inserted in your posts manually using the shortcode.

The plugin will add an box to the admin interface's post and pages edit page where you can paste your videos URL. At the moment the plugin supports __YouTube (including [time-links](http://support.google.com/youtube/bin/answer.py?hl=en&answer=116618 "Link to a specific time in a video")) and Vimeo.
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