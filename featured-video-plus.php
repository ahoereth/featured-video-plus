<?php
/**
Plugin Name: Featured Video Plus
Plugin URI: http://yrnxt.com/wordpress/featured-video-plus/
Description: Add Featured Videos to your posts and pages.
Author: Alexander Höreth
Version: 1.9.1
Author URI: http://yrnxt.com
License: GPL2

    Copyright 2009-2014  Alexander Höreth (email: a.hoereth@gmail.com)

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
	define('FVP_VERSION', '1.9.1');

// symlink proof
$pathinfo = pathinfo(dirname(plugin_basename(__FILE__)));
if (!defined('FVP_NAME'))
	define('FVP_NAME', $pathinfo['filename']);
if (!defined('FVP_DIR'))
	define('FVP_DIR', plugin_dir_path(__FILE__));
if (!defined('FVP_URL'))
	define('FVP_URL', plugins_url(FVP_NAME) . '/');

include_once( FVP_DIR . 'php/class-oembed.php' );
$oembed = new FVP_oEmbed();

// init general class, located in php/general.php
include_once( FVP_DIR . 'php/general.php' );
$fvp_general = new featured_video_plus( $oembed );


// include api functions which are intended to be used by developers
include_once( FVP_DIR . 'php/functions.php' );

// init translations
add_action( 'plugins_loaded', array( $fvp_general, 'language' ) );


// only on backend / administration interface
if( is_admin() ) {
	// init backend class, located in php/backend.php
	include_once( FVP_DIR . 'php/backend.php' );
	$featured_video_plus_backend = new featured_video_plus_backend( $fvp_general, $oembed );

	// plugin upgrade/setup
	include_once( FVP_DIR . '/php/upgrade.php' );
	add_action( 'admin_init', 'featured_video_plus_upgrade' );

	// admin settings
	include_once( FVP_DIR . 'php/settings.php' );
	$featured_video_plus_settings = new featured_video_plus_settings();
	add_action( 'admin_init', array( &$featured_video_plus_settings, 'settings_init' ) );

	// media settings help
	add_action('admin_init', array( &$featured_video_plus_settings, 'help' ) );
	add_action( 'load-options-media.php', array( &$featured_video_plus_settings, 'tabs' ), 20 ); // $GLOBALS['pagenow']
}


// only on frontend / page
if( !is_admin() ) {
	// init frontend class, located in php/frontend.php
	include_once( FVP_DIR . 'php/frontend.php' );
	$featured_video_plus_frontend = new featured_video_plus_frontend( $fvp_general );

	// enqueue scripts and styles
	add_action( 'wp_enqueue_scripts', array( &$featured_video_plus_frontend, 'enqueue' ) );

	// filter get_post_thumbnail output
	add_filter( 'post_thumbnail_html', array( &$featured_video_plus_frontend, 'filter_post_thumbnail'), 99, 5);

	// shortcode
	add_shortcode( 'featured-video-plus', array( &$featured_video_plus_frontend, 'shortcode' ) );
}
