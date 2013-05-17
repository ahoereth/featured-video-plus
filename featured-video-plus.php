<?php
/**
Plugin Name: Featured Video Plus
Plugin URI: http://yrnxt.com/wordpress/featured-video-plus/
Description: Add Featured Videos to your posts and pages.
Author: Alexander Höreth
Version: 1.8
Author URI: http://yrnxt.com
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

if (!defined('FVP_VERSION'))
	define('FVP_VERSION', '1.8');

// symlink proof
$pathinfo = pathinfo(dirname(plugin_basename(__FILE__)));
if (!defined('FVP_NAME'))
	define('FVP_NAME', $pathinfo['filename']);
if (!defined('FVP_DIR'))
	define('FVP_DIR', plugin_dir_path(__FILE__));
if (!defined('FVP_URL'))
	define('FVP_URL', plugins_url(FVP_NAME) . '/');

// init general class, located in php/general.php
include_once( FVP_DIR . 'php/general.php' );
$featured_video_plus = new featured_video_plus();

// include api functions which are intended to be used by developers
include_once( FVP_DIR . 'php/functions.php' );

// init translations
add_action( 'plugins_loaded', array( &$featured_video_plus, 'language' ) );


// only on backend / administration interface
if( is_admin() ) {
	// init backend class, located in php/backend.php
	include_once( FVP_DIR . 'php/backend.php' );
	$featured_video_plus_backend = new featured_video_plus_backend($featured_video_plus);

	// plugin upgrade/setup
	include_once( FVP_DIR . '/php/upgrade.php' );
	add_action( 'admin_init', 'featured_video_plus_upgrade' );

	// admin meta box
	add_action('admin_menu', 				array( &$featured_video_plus_backend, 'metabox_register' ) );
	add_action('save_post', 				array( &$featured_video_plus_backend, 'metabox_save' 	 ) );

	// enqueue admin scripts and styles
	add_action('admin_enqueue_scripts', array( &$featured_video_plus_backend, 	'enqueue' ) );
	add_action('admin_enqueue_scripts', array( &$featured_video_plus, 			'enqueue' ) );

	// link to media settings on plugins overview
	add_filter('plugin_action_links', 	array( &$featured_video_plus_backend, 'plugin_action_link' ), 10, 2);

	// add upload mime types for HTML5 videos
	add_filter('upload_mimes', 			array( &$featured_video_plus_backend, 'add_upload_mimes' ) );

	// post edit help
	add_action('admin_init', 			array( &$featured_video_plus_backend, 'help' ) );
	add_action( 'load-post.php', 		array( &$featured_video_plus_backend, 'tabs' ), 20 ); // $GLOBALS['pagenow']
	if( get_bloginfo('version') < 3.3 )
		add_filter( 'contextual_help', 	array( &$featured_video_plus_backend, 'help_pre_33' ), 10, 3 );

	// admin settings
	include_once( FVP_DIR . 'php/settings.php' );
	$featured_video_plus_settings = new featured_video_plus_settings();
	add_action( 'admin_init', array( &$featured_video_plus_settings, 'settings_init' ) );

	// media settings help
	add_action('admin_init',  array( &$featured_video_plus_settings, 'help' ) );
	add_action( 'load-options-media.php', array( &$featured_video_plus_settings, 'tabs' ), 20 ); // $GLOBALS['pagenow']
	if( get_bloginfo('version') < 3.3 )
		add_filter( 'contextual_help', 	array( &$featured_video_plus_settings, 'help_pre_33' ), 10, 3 );

 	if (defined('DOING_AJAX')&&DOING_AJAX){
		add_action( 'wp_ajax_fvp_ajax', 						array( &$featured_video_plus_backend, 'ajax' 			 ) );
		add_action( 'wp_ajax_fvp_get_embed', 			 	array( &$featured_video_plus_backend, 'ajax_get_embed' ));
		add_action( 'wp_ajax_nopriv_fvp_get_embed', array( &$featured_video_plus_backend, 'ajax_get_embed' ));
	}
}


// only on frontend / page
if( !is_admin() ) {
	// init frontend class, located in php/frontend.php
	include_once( FVP_DIR . 'php/frontend.php' );
	$featured_video_plus_frontend = new featured_video_plus_frontend($featured_video_plus);

	// enqueue scripts and styles
	add_action( 'wp_enqueue_scripts', array( &$featured_video_plus, 		 		 'enqueue' ) );
	add_action( 'wp_enqueue_scripts', array( &$featured_video_plus_frontend, 'enqueue' ) );

	// filter get_post_thumbnail output
	add_filter(		'post_thumbnail_html', array( &$featured_video_plus_frontend, 'filter_post_thumbnail'), 99, 5);

	// shortcode
	add_shortcode( 	'featured-video-plus', array( &$featured_video_plus_frontend, 'shortcode' ) );
}
