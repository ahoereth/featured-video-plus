<?php
/**
Plugin Name: Featured Video Plus
Plugin URI: https://github.com/ahoereth/featured-video-plus
Description: Featured Videos just like Featured Images.
Author: Alexander Höreth
Version: 1.0
Author URI: http://ahoereth.yrnxt.com
License: GPL2

    Copyright 2009-2012  Alexander Höreth (email: a.hoereth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

require_once( plugin_dir_path(__FILE__) . 'php/general.php' );
require_once( plugin_dir_path(__FILE__) . 'php/backend.php' );
require_once( plugin_dir_path(__FILE__) . 'php/frontend.php' );

// init general class, located in php/general.php
$featured_video_plus = new featured_video_plus();

// shortcode
add_shortcode( 'featured-video-plus', array( &$featured_video_plus, 'shortcode' ) );

// only on backend / administration interface
if(  is_admin() ) {
	// init backend class, located in php/backend.php
	$featured_video_plus_backend = new featured_video_plus_backend($featured_video_plus);

	add_action('admin_menu', array( &$featured_video_plus_backend, 'metabox_register' ) );
	add_action('save_post',  array( &$featured_video_plus_backend, 'metabox_save' )	 );

	add_action('admin_init', array( &$featured_video_plus_backend, 'settings_init' ) );

	// enqueue scripts and styles
	add_action( 'admin_enqueue_scripts', array( &$featured_video_plus_backend, 'enqueue' ) );

	add_action('admin_notices', array( &$featured_video_plus_backend, 'activation_notification' ) );
	add_action('admin_init', array( &$featured_video_plus_backend, 'ignore_activation_notification' ) );

	add_action('admin_notices', array( &$featured_video_plus_backend, 'no_featimg_warning' ) );
	add_action('admin_init', array( &$featured_video_plus_backend, 'no_featimg_warning_callback' ) );
	add_filter( 'admin_post_thumbnail_html', array( &$featured_video_plus_backend, 'featimg_metabox' ));
}


// only on frontend / page
if( !is_admin() ) {
	// init frontend class, located in php/frontend.php
	$featured_video_plus_frontend = new featured_video_plus_frontend($featured_video_plus);

	// enqueue scripts and styles
	add_action( 'wp_enqueue_scripts', array( &$featured_video_plus_frontend, 'enqueue' ) );

	// filter get_post_thumbnail output
	add_filter('post_thumbnail_html', array( &$featured_video_plus_frontend, 'filter_post_thumbnail'), 99, 5);


	// functions which are available to theme developers follow here:
	// echos the current posts featured video
	function the_post_video($width = '560', $height = '315', $allowfullscreen = true) {
		echo get_the_post_video(null, $width, $height, $allowfullscreen, true);
	}

	// returns the posts featured video
	function get_the_post_video($post_id = null, $width = '560', $height = '315', $allowfullscreen = true) {
		global $featured_video_plus;
		return $featured_video_plus->get_the_post_video($post_id, $width, $height, $allowfullscreen);
	}

	// checks if post has a featured video
	function has_post_video($post_id = null){
		global $featured_video_plus;
		return $featured_video_plus->has_post_video($post_id);
	}
}


include_once( dirname( __FILE__ ) . '/php/setup.php' );
register_activation_hook( 	 WP_PLUGIN_DIR . '/featured-video-plus/featured-video-plus.php', array( 'featured_video_plus_setup', 'on_activate' ) );
register_uninstall_hook( 	 WP_PLUGIN_DIR . '/featured-video-plus/featured-video-plus.php', array( 'featured_video_plus_setup', 'on_uninstall' ) );

?>