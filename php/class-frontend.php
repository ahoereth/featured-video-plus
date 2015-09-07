<?php

// dependencies
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing frontend functionality.
 *
 * Enqueue scripts/styles, replace featured images by featured videos or
 * insert the ajax request handlers, add 'has-post-video' class and
 * register the [featured-video-plus] shortcode.
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

		add_filter( 'post_thumbnail_html', array( $this, 'filter_post_thumbnail' ), 99, 5 );
		add_filter( 'post_class', array( $this, 'has_post_video_class' ) );

		add_shortcode( 'featured-video-plus', array( $this, 'shortcode' ) );
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
			'nonce'    => wp_create_nonce( FVP_NAME . FVP_VERSION ),
			'fitvids'  => ! empty( $options['sizing']['responsive'] ) &&
			              $options['sizing']['responsive'],
			'dynamic'  => 'dynamic' === $mode,
			'overlay'  => 'overlay' === $mode,
			'opacity'  => 0.75,
			'loadicon' => 'overlay' === $mode ? FVP_URL . 'img/loadicon_w.gif' :
			                                    FVP_URL . 'img/loadicon_b.gif',
			'playicon' => FVP_URL . 'img/playicon.png',
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


	/**
	 * Display featured videos in place of featured images if a featured video is available and only if so desired by user.
	 *
	 * @see http://wordpress.stackexchange.com/a/41858
	 * @since 1.0.0
	 *
	 * @param string $html featured image html, ready to echo
	 * @param int $post_id id of target post
	 * @param int $post_thumbnail_id id of featured image
	 * @param string|array $size desired size of featured image / video
	 * @param array $attr
	 */
	public function filter_post_thumbnail(
		$html,
		$post_id,
		$post_thumbnail_id,
		$size,
		$attr
	) {
		$size = $this->get_size();

		$options = get_option( 'fvp-settings' );
		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;
		$conditions = ! empty( $options['conditions'] ) ?
			$options['conditions'] : null;
		$single_replace = is_single() &&
			! empty( $options['single_replace'] ) && $options['single_replace'];

		// Don't show a video.
		if ( ( 'manual' === $mode ) ||
		     ( ! self::check_conditions( $conditions ) ) ||
		     ( ! has_post_video( $post_id ) )
		) {
			return $html;
		}


		// Playicon with onload JavaScript for initalizing FVP JS functionality
		// which has to be done from here because of infinite scroll plugins.
		$onload = '<img class="playicon onload" ' .
		            'src="'. FVP_URL . 'img/playicon.png" ' .
		            'alt="Featured Video Play Icon" ' .
		            'onload="(function() {' .
		              "('initFeaturedVideoPlus' in this) && ".
		              "('function' === typeof initFeaturedVideoPlus) && ".
		              "initFeaturedVideoPlus();" .
		            '})();" ' .
		          '/>';

		// Show the video on-click - lazy load.
		if ( 'dynamic' === $mode && ! $single_replace ) {
			return sprintf(
				'<a href="#" data-id="%s" class="fvp-dynamic post-thumbnail">%s</a>%s',
				$post_id,
				$html,
				$onload
			);
		}

		// Show the video on-click in an overlay.
		if ( 'overlay' === $mode && ! $single_replace ) {
			return sprintf(
				'<a href="#" data-id="%s" class="fvp-overlay post-thumbnail">%s</a>%s',
				$post_id,
				$html,
				$onload
			);
		}

		// Replace the featured image with the video.
		return get_the_post_video( $post_id, $size ) . $onload;
	}


	/**
	 * Add a 'has-post-video' class to posts if appropriate.
	 *
	 * @since 2.0.0
	 *
	 * @param  {array} $classes Existing classes
	 * @return {array}          Updated classes
	 */
	public function has_post_video_class( $classes ) {
		global $post;

		if ( has_post_video( $post->ID ) ) {
			$classes[] = 'has-post-video';
		}
		return $classes;
	}


	/**
	 * Shortcode for usage in post or page entries. Echos the post's featured video.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts can contain the width and/or height how the featured video should be displayed in px, optional
	 */
	public function shortcode($atts){
		$w = isset($atts['width'])  ? $atts['width'] : '';
		$h = isset($atts['height']) ? $atts['height'] : '';

		if ( has_post_video() ) {
			return get_the_post_video( null, array( $w, $h ) );
		}
	}


	/**
	 * Check a given set of display conditions if one or more of them hold. If
	 * an empty set is given, return true.
	 *
	 * @param {assoc} $conditions
	 * @return {bool}
	 */
	private static function check_conditions( $conditions ) {
		if ( empty( $conditions ) ) {
			return true;
		}

		$conditions_hold = false;
		foreach ( $conditions AS $fun => $value ) {
			$negate = false;
			if ( '!' === $fun[0] ) {
				$negate = true;
				$fun = substr( $fun, 1 );
			}

			if ( $value && function_exists( 'is_' . $fun ) ) {
				$call = call_user_func( 'is_' . $fun );
				$conditions_hold = $conditions_hold || ( $negate ? ! $call : $call );
			}
		}

		return $conditions_hold;
	}
}
