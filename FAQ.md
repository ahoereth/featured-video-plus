# Frequently Asked Questions #

## After adding the URL and saving the post I do not get any video? ##
Maybe the plugin does not recognize the URL. Take a look into the contextual help (button on the top right of the post edit screen). There is a list what the URLs should look like. If this does not help leave a note in the support forum.

## The input box has a red background - but the video works just fine. Whats going on? ##
With every video you insert into the meta box the plugin tries to access the API of the
according video provider to grab information about the video and pull an image. When this API
access fails the input box gets a red background. When for example the server you are using is
located in Germany it cannot access the YouTube API for videos blocked in this country - still
you and your visitors might be able to watch the videos as normal. The plugin cannot test for this.

## How do I use my local videos? ##
Take a look into the contextual help (button on the top right of the post edit screen).

## My theme uses Featured Images. Why are my videos not being displayed in place? ##
For the videos to be automatically displayed you need to define a Featured Image. This image will never be shown if a video is available.
Beside this your theme needs to feature [Post Thumbnails](http://codex.wordpress.org/Post_Thumbnails) and make use of `get_the_post_thumbnail()` or `the_post_thumbnail()`, because these are the core functions the plugin hooks into.

If the automatic integration does not work, you can tell me in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using and I will take a look at it and might be able to develop a workaround.

## How can I make the videos fit the theme? ##
Take a look at your media settings and try different fixed sizes. If tweaking those does not help: [Tell me](http://wordpress.org/support/plugin/featured-video-plus) which theme you are using.

## What about other video providers? ##
Leave me a note in the support forums which video platforms you would like to see in a feature release!

## How can I translate the plugin? ##
Grab the [featured-video-plus.pot](https://github.com/ahoereth/featured-video-plus/blob/master/lng/featured-video-plus.pot) file, [translate it](http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/) and post it in the [Support Forum](http://wordpress.org/support/plugin/featured-video-plus). It will very likely be shipped with the next version.
