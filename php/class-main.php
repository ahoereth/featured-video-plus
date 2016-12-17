<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );

/**
 * Class containing all functions needed on front- AND backend. Functions only
 * needed on one of those are found in the individual FVP_Frontend and
 * FVP_Backend classes.
 *
 * @since 1.0.0
 */
class Featured_Video_Plus {
	protected $oembed;

	public function __construct() {
		require_once( FVP_DIR . 'php/class-oembed.php' );
		$this->oembed = new FVP_oEmbed();

		add_action( 'plugins_loaded', array( $this, 'language' ) );

		add_shortcode( 'featured-video-plus', array( $this, 'shortcode' ) );

		// Mainly frontend stuff, but lives here because it also needs to be
		// available on the backend because thats where AJAX requests are processed.
		add_filter( 'post_thumbnail_html', array( $this, 'filter_post_thumbnail' ), 99, 5 );
		add_filter( 'post_class', array( $this, 'has_post_video_class' ) );
	}


	/**
	 * Returns the featured video html, ready to echo.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 * @param string|array $size
	 */
	public function get_the_post_video(
		$post_id = null,
		$size = null,
		$ajax = null
	) {
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

		if ( ! has_post_video( $post_id ) ) {
			return '';
		}

		$meta    = get_post_meta( $post_id, '_fvp_video', true );
		$options = get_option( 'fvp-settings' );

		// Extract default and default->general options for easy access.
		$defaults = ! empty( $options['default_args'] ) ?
			$options['default_args'] : array();
		$general = ! empty( $defaults['general'] ) ? $defaults['general'] : array();

		// Autoplay option. Suppressed when viewing admin.
		$general['autoplay'] = self::parse_autoplay_options( $options, $ajax );

		// Responsive scaling option.
		$responsive = ! empty( $options['sizing']['responsive'] ) &&
		              $options['sizing']['responsive'];

		// Alignment option
		$align = ! empty($options['alignment']) ? $options['alignment'] : 'center';

		$args = array(
			'id' => ! empty( $meta['id'] ) ? $meta['id'] : null,
			'provider' => ! empty( $meta['provider'] ) ? $meta['provider'] : null,
		);

		$provider = $args['provider'];
		switch ( $provider ) {
			case 'local':
				$img_meta = wp_get_attachment_metadata( $meta['id'] );
				$size = self::get_size( $size, array(
					'width'  => ! empty($img_meta['width'] ) ? $img_meta['width']  : null,
					'height' => ! empty($img_meta['height']) ? $img_meta['height'] : null,
				) );

				$atts = array(
					'src'      => wp_get_attachment_url( $meta['id'] ),
					'autoplay' => $general['autoplay'] ? 'on' : null,
					'loop'     => ! empty( $general['loop'] ) ? 'on' : null,
					// use massive video size/height for responsive videos because
					// fitvids does not upscale videos
					'width'    => $responsive ? $size['width'] * 8  : $size['width'],
					'height'   => $responsive ? $size['height'] * 8 : $size['height'],
				);

				$args = array_merge( $args, $atts );
				$embed = wp_video_shortcode( $atts );
				$embed = apply_filters( 'fvp-local', $embed, $args );
				break;

			case 'raw':
				$embed = do_shortcode( $meta['full'] );
				break;

			default:
				$atts = array_merge(
					$general,
					self::get_size( $size ),
					! empty( $defaults[ $provider ] ) ? $defaults[ $provider ] : array(),
					isset( $meta['parameters'] ) ? $meta['parameters'] : array()
				);

				$args = array_merge( $args, $atts );
				$embed = $this->oembed->get_html( $meta['full'], $atts, $provider );
				$embed = apply_filters( 'fvp-oembed', $embed, $args );
				break;
		}

		if ( empty( $embed ) ) {
			return '';
		}

		$classnames = array(
			'featured-video-plus' => true,
			'post-thumbnail' => true,
			'fvp-responsive' => $responsive,
		);
		$classnames[ 'fvp-' . $provider ] = ! empty( $provider );
		$classnames[ 'fvp-' . $align ] = ! empty( $align );

		$embed = sprintf(
			"<!-- Featured Video Plus v%s -->\n<div%s>%s</div>\n\n",
			FVP_VERSION,
			FVP_HTML::class_names($classnames, true, true),
			$embed
		);

		return $embed;
	}


	/**
	 * Shortcode for usage in post or page entries. Echos the post's featured video.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts width and height specifications, optional
	 */
	public function shortcode( $atts = null ) {
		$w = isset( $atts['width'] )  ? $atts['width']  : '';
		$h = isset( $atts['height'] ) ? $atts['height'] : '';

		if ( has_post_video() ) {
			return get_the_post_video( null, array( $w, $h ) );
		}
	}


	/**
	 * Filter the post thumbnail to eventually replace it with the
	 * featured video.
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
		$options = get_option( 'fvp-settings' );
		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;
		$conditions = ! empty( $options['conditions'] ) ?
			$options['conditions'] : null;
		$single_replace = is_singular() &&
			! empty( $options['single_replace'] ) && $options['single_replace'];
			$responsive = ! empty( $options['sizing']['responsive'] ) &&
			              $options['sizing']['responsive'];

		// Don't show a video.
		if ( ( 'manual' === $mode ) ||
		     ( ! self::check_conditions( $conditions ) ) ||
		     ( ! has_post_video( $post_id ) )
		) {
			return $html;
		}


		// On-load JavaScript for initalizing FVP JS functionality.
		// Doing this here in order to also have it fire when posts are loaded
		// over AJAX.
		$onload = '<img class="fvp-onload" ' .
		            'src="'. FVP_URL . 'img/playicon.png" ' .
		            'alt="Featured Video Play Icon" ' .
		            'onload="(function() {' .
		              "('initFeaturedVideoPlus' in this) && ".
		              "('function' === typeof initFeaturedVideoPlus) && ".
		              "initFeaturedVideoPlus();" .
		            '})();" ' .
		          '/>';

		// Action icon overlay container.
		$actionicon = '<div class="fvp-actionicon"></div>';

		// Show the video on-click - lazy load.
		if ( 'dynamic' === $mode && ! $single_replace ) {
			return sprintf(
				'<a href="#" data-id="%s" class="fvp-dynamic post-thumbnail">%s</a>%s',
				$post_id,
				$actionicon . $html,
				$onload
			);
		}

		// Show the video on-click in an overlay.
		if ( 'overlay' === $mode && ! $single_replace ) {
			return sprintf(
				'<a href="#" data-id="%s" class="fvp-overlay post-thumbnail">%s</a>%s',
				$post_id,
				$actionicon . $html,
				$onload
			);
		}

		// Replace the featured image with the video.
		return get_the_post_video( $post_id, $responsive ? $size : null ) . $onload;
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
	 * Initializes i18n
	 *
	 * @since 1.3.0
	 */
	public function language() {
		load_plugin_textdomain(
			'featured-video-plus',
			FVP_DIR . 'lng/',
			FVP_NAME . '/lng/'
		);
	}


	/**
	 * Determine featured video size
	 *
	 * @since  1.4.0
	 *
	 * @param  {array|string} Either a array containing a fixed width and height
	 *                        at key 0 and 1 respectively or a string specifying
	 *                        a predefined size:
	 *                          thumbnail | thumb | medium | large
	 * @return {array}        The desired video size also taking the options set
	 *                        in the media settings into consideration.
	 */
	protected static function get_size( $size = null, $original = null ) {
		$options = get_option( 'fvp-settings' );

		if ( is_array( $size ) ) {
				// Fixed size as array( width => #, height => # ) or array( #, # ).
				$width = isset( $size['width'] ) && is_numeric( $size['width'] ) ?
					$size['width'] :
					( isset( $size[0] ) && is_numeric( $size[0] ) ? $size[0] : null );
				$height = isset( $size['height'] ) && is_numeric( $size['height'] ) ?
					$size['height'] :
					( isset( $size[1] ) && is_numeric( $size[1] ) ? $size[1] : null );

		} elseif ( is_string( $size ) ) {
			// Size as string pointing to a WordPress preset.
			global $_wp_additional_image_sizes;
			$presets = get_intermediate_image_sizes();
			foreach ( $presets as $preset ) {
				if ( $preset == $size ) {
					if ( in_array( $preset, array( 'thumbnail', 'medium', 'large' ) ) ) {
						$width  = get_option( $preset . '_size_w' );
						$height = get_option( $preset . '_size_h' );
					} elseif ( isset( $_wp_additional_image_sizes[ $preset ] ) ) {
						$width  = $_wp_additional_image_sizes[ $preset ]['width'];
						$height = $_wp_additional_image_sizes[ $preset ]['height'];
					}
				}
			}

		} elseif ( is_numeric( $size ) ) {
			// Single number provided - use it for the width.
			$width = $size;
		}

		if ( empty( $width ) ) {
			$width = ! empty( $options['sizing']['width'] ) ?
				floatval( $options['sizing']['width'] ) : 1280;
		}

		if ( empty( $height ) ) {
			// Calculate height relative to width.
			$height = ! empty( $original ) ?
				round( $original['height'] * ($width / $original['width']) ) :
				round( $width / 16 * 9 );
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}


	/**
	 * Gets a post by an meta_key meta_value pair. Returns it's post_id.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @see http://dev.mysql.com/doc/refman/5.0/en/regexp.html#operator_regexp
	 * @since 1.0.0
	 *
	 * @param string $meta_key which meta_key to look for
	 * @param string $meta_value which meta_value to look for
	 */
	protected static function get_post_by_custom_meta(
		$meta_key,
		$meta_value = null
	) {
		global $wpdb;
		if ( $meta_value !== null ) {
			$prepared = $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} " .
					"WHERE meta_key=%s AND meta_value=%s LIMIT 1",
				$meta_key,
				$meta_value
			);
			return $wpdb->get_var( $prepared );
		} else {
			$prepared = $wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s",
				$meta_key
			);
			return $wpdb->get_col( $prepared );
		}
	}


	/**
	 * Generate a standardized nonce action string.
	 *
	 * @param  int/string $identifier
	 * @return string
	 */
	protected static function get_nonce_action( $identifier ) {
		return FVP_NAME . FVP_VERSION . $identifier;
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


	/**
	 * Parse the autoplay options to determine if video should or should not
	 * autoplay.
	 *
	 * @param  {assoic} $options
	 * @return {bool}
	 */
	private static function parse_autoplay_options(
		$options = array(),
		$ajax = null
	) {
		if ( empty( $options['autoplay'] ) ) {
			return false;
		}

		if (
			! empty( $options['autoplay']['always'] ) &&
			$options['autoplay']['always']
		) {
			return true;
		};

		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;
		//$ajax = defined( 'DOING_AJAX' ) && DOING_AJAX && $ajax;

		if (
			! empty( $options['autoplay']['lazy'] ) &&
			$options['autoplay']['lazy'] &&
			$ajax
		) {
			return true;
		}

		if (
			! empty( $options['autoplay']['single'] ) &&
			$options['autoplay']['single'] &&
			is_singular()
		) {
			return true;
		}

		return false;
	}


}
