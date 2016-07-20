# Featured Video Plus #

Add Featured Videos to your posts and pages. Works like magic with most themes which use Featured Images. Local Media, YouTube, Vimeo and many more.



## Description ##
> A picture is worth a thousand words. How many words is a video worth?

Featured Videos work like Featured Images, just smoother: Paste a video URL into the designated new box on the post edit screen and the video will be displayed in place of a post image.

There are three ways to get the videos onto your page:

1. **Automagically!** If your theme makes use of WordPress' native [featured image functionality](http://codex.wordpress.org/Post_Thumbnails) you are set: Automatic insertion, lazy loading or lightbox overlays, its your choice. If this does not work you can either
2. insert the `[featured-video-plus]`-__Shortcode__ in your posts or
3. manually make use of the __PHP-functions__ in your theme's source files.

For more details, check the [installation](http://wordpress.org/plugins/featured-video-plus/installation/) page.

> <strong>Theme compatibility</strong><br>
> Sadly many themes do not follow the WordPress standards and implement their own fancy functions for displaying featured images - those very likely break this plugin. Check out the [FAQ](https://wordpress.org/plugins/featured-video-plus/faq/). Another common problem are sliders: Videos, in general, do not like sliders at all.

See the plugin in action on [yrnxt.com](http://yrnxt.com/wordpress/featured-video-plus/). There is a button in the sidebar to switch between the different featured video display modes: [Automatic](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=replace), [lazy](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=dynamic) and [overlay](http://yrnxt.com/wordpress/featured-video-plus/?setfvpmode=overlay).

Besides **Local Videos** you can use videos from a whole lot of external providers like **YouTube**, **Vimeo** and **Dailymotion**. **SoundCloud** and **Spotify** (including playlists) are supported as well. Check the [WordPress Codex](http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F) for a complete list. If some provider is not listed you can always just use an embed code or whatever HTML you like.

After installing the plugin check your site's *Media Settings* (`Settings -> Media` in the administration interface): The plugin adds quite some little helper options there. Change to lazy or overlay mode, tweak video sizing, individualize the look of the most prominent providers' video players and turn on autoplay or video looping. By default videos try to dynamically fit their parent containers width and adjust their size responsively.

> <strong>Support</strong><br>
> I do read all support questions in the [forums](http://wordpress.org/support/plugin/featured-video-plus) but cannot reply to all of them. The plugin is an unpaid side project and full support would require more time than I can invest for free for over 20k active installs. If you really need help, consider [buying me a cookie](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8UKMQW2DMM6) - best way to attract my attention and to support future enhancements.



## Installation ##

### Installation ###

1. Visit your WordPress Administration interface and go to `Plugins -> Add New`
2. Search for `Featured Video Plus`, and click `Install Now` below the plugin's name
3. When the installation finished, click `Activate Plugin`

The plugin is ready to go. Now edit your posts and add video links to the `Featured Video` box on the right! Plugin specific settings can be found under `Settings -> Media`.

### Theme integration ###

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



