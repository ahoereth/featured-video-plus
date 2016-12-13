<?php

// dependencies
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing frontend functionality.
 *
 * Enqueue scripts and styles specific to the frontend.
 *
 * @since 1.0.0
 */
class FVP_Frontend extends Featured_Video_Plus {

	/**
	 * Creates a new instace of this class, saves the featured_video_instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}


	/**
	 * Enqueue all scripts and styles needed when viewing the frontend.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$options = get_option( 'fvp-settings' );
		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;

		wp_register_script(
			'jquery.fitvids',
			FVP_URL . "js/jquery.fitvids$min.js",
			array( 'jquery' ),
			'master-2015-08',
			false
		);

		wp_register_script(
			'jquery.domwindow',
			FVP_URL . "js/jquery.domwindow$min.js",
			array( 'jquery' ),
			FVP_VERSION
		);

		// Basic dependencies. Is extended in the following.
		$jsdeps = array( 'jquery' );
		$cssdeps = array();

		// If the video loading is performed in a lazy fashion we cannot know onload
		// if there is a local (html5) video - we need to require mediaelement.js
		// just for the possibility that one will be loaded.
		if ( 'overlay' === $mode || 'dynamic' === $mode ) {
			$jsdeps[] = 'wp-mediaelement';
			$cssdeps[] = 'wp-mediaelement';
		}

		// Is responsive video functionality required? Only when width is set to
		// 'auto' and display mode is not set to overlay.
		if (
			! empty($options['sizing']['responsive']) &&
			$options['sizing']['responsive']
		) {
			$jsdeps[] = 'jquery.fitvids';
		}

		// Is modal functionality required?
		if ( 'overlay' === $mode ) {
			$jsdeps[] = 'jquery.domwindow';
		}

		// general frontend script
		wp_enqueue_script(
			'fvp-frontend',
			FVP_URL . "js/frontend$min.js",
			$jsdeps,
			FVP_VERSION
		);

		// some context for JS
		wp_localize_script( 'fvp-frontend', 'fvpdata', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( self::get_nonce_action( 'frontend' ) ),
			'fitvids'  => ! empty( $options['sizing']['responsive'] ) &&
			              $options['sizing']['responsive'],
			'dynamic'  => 'dynamic' === $mode,
			'overlay'  => 'overlay' === $mode,
			'opacity'  => 0.75,
			'color'    => 'overlay' === $mode ? 'w' : 'b',
			'width'    => ! empty( $options['sizing']['width'] ) ?
				$options['sizing']['width'] : null
		));

		// general frontend styles
		wp_enqueue_style(
			'fvp-frontend',
			FVP_URL . 'styles/frontend.css',
			$cssdeps,
			FVP_VERSION
		);
	}


}
