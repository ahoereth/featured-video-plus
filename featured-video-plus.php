<?php
/*
Plugin Name: Featured Video Plus
Plugin URI: http://yrnxt.com/wordpress/featured-video-plus/
Description: Add Featured Videos to your posts and pages.
Version: 2.3.3
Author: Alexander Höreth
Author URI: http://yrnxt.com
Text Domain: featured-video-plus
Domain Path: /lng
License: GPL-2.0

	Copyright 2009-2016  Alexander Höreth (email: a.hoereth@gmail.com)

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


// ********************
// CONSTANTS
if ( ! defined( 'FVP_VERSION' ) ) {
	define( 'FVP_VERSION', '2.3.3' );
}

$pathinfo = pathinfo( dirname( plugin_basename( __FILE__ ) ) );
if ( ! defined( 'FVP_NAME' ) ) {
	define( 'FVP_NAME', $pathinfo['filename'] );
}

if ( ! defined( 'FVP_DIR' ) ) {
	define( 'FVP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'FVP_URL' ) ) {
	define( 'FVP_URL', plugins_url( FVP_NAME ) . '/' );
}


// ********************
// BACKEND
if ( is_admin() ) {
	require_once( FVP_DIR . 'php/class-backend.php' );
	$featured_video_plus = new FVP_Backend();

	// SETTINGS
	require_once( FVP_DIR . 'php/class-settings.php' );
	new FVP_Settings();

	// HELP TABS
	require_once( FVP_DIR . 'php/class-help.php' );
	new FVP_Help();
}


// ********************
// FRONTEND
if ( ! is_admin() ) {
	require_once( FVP_DIR . 'php/class-frontend.php' );
	$featured_video_plus = new FVP_Frontend();
}


// ********************
// PUBLIC API
include_once( FVP_DIR . 'php/functions.php' );
