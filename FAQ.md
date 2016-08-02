# Frequently Asked Questions #

## Why do I just get text back after adding an URL to the Featured Video input? ##
If the plugin just returns text instead of the actual video the pasted url is probably not valid or not from a valid video provider. Try inserting the raw embed code instead or [check the docs](http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F) to see which providers are supported.

## How do I use my local videos? ##
Click the small media icon in the Featured Video input box on the post edit screen and upload your video or choose it from the media library. WordPress does not support all formats tho, [check this table](http://www.mediaelementjs.com/#devices) for details.

## Why do I not see a featured video or image on the frontend at all? ##
For the videos to be automatically displayed you need to define a Featured Image. Depending on your featured video settings this image will never be shown if a video is set. If your theme does not support featured images the plugin also has no chance of working out of the box.

## Why does the frontend still display the featured image although I added a featured video to the post? ##
Sadly not all themes work out of the box. Themes need to make use of WordPress' native [Post Thumbnail](http://codex.wordpress.org/Post_Thumbnails) functionality (specifically `get_the_post_thumbnail()` and/or `the_post_thumbnail()`) - these functions are where the plugin can hook into the theme and modify what is displayed. Consider contacting the theme's creator or modifying the theme's sourcecode in order to add the plugin's [PHP-functions](https://wordpress.org/plugins/featured-video-plus/installation/).

## How can I make the videos fit into their designated space in my theme? ##
Take a look at your media settings and try using a fixed width instead of responsive sizing.

## How can I make the plugin work with infinite scrolling? ##
While the plugin tries to handle infinite scrolling automatically, it does not work for all configurations. In those cases you will want to manually call `initFeaturedVideoPlus()` using JavaScript everytime new articles have been loaded. Most infinite scroll plugins should have some kind of post-load hook.

## Can I help translating the plugin? ##
Yes, please! Check out the official [Featured Video Plus Translation Project](https://translate.wordpress.org/projects/wp-plugins/featured-video-plus).
