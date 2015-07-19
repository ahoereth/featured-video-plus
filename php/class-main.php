<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );

/**
 * Class containing all functions needed on front- AND backend. Functions only needed on one of those are found in distinct classes.
 *
 * @since 1.0.0
 */
class Featured_Video_Plus {
	protected $oembed;

	public function __construct() {
		require_once( FVP_DIR . 'php/class-oembed.php' );
		$this->oembed = new FVP_oEmbed();

		add_action( 'plugins_loaded', array( $this, 'language' ) );
	}


	/**
	 * Returns the featured video html, ready to echo.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 * @param string|array $size
	 */
	public function get_the_post_video( $post_id = null, $size = null ) {
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
		$general['autoplay'] = self::parse_autoplay_options($options) ? true : null;

		// Responsive scaling option. Not used when viewing the admin screen.
		$responsive =
			! empty($options['sizing']['responsive']) &&
			$options['sizing']['responsive'] &&
			( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! is_admin() );

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
				$size = $this->get_size( $size, array(
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
					$this->get_size( $size ),
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
	protected function get_size( $size = null, $original = null ) {
		$options = get_option( 'fvp-settings' );

		// fixed size requested as array( width => #, height => # ) or array( #, # )
		if ( is_array( $size ) ) {
				$width = isset( $size['width'] ) && is_numeric( $size['width'] ) ?
					$size['width'] :
					( isset( $size[0] ) && is_numeric( $size[0] ) ? $size[0] : null );
				$height = isset( $size['height'] ) &&is_numeric( $size['height'] ) ?
					$size['height'] :
					( isset( $size[1] ) && is_numeric( $size[1] ) ? $size[1] : null );

			// size requested using a string pointing to a WordPress preset
		} elseif ( is_string( $size ) ) {
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

			// single number provided - use it for the width
		} elseif ( is_numeric( $size ) ) {
			$width = $size;
		}

		if ( empty( $width ) ) {
			$width = ! empty( $options['sizing']['width'] ) ?
				$options['sizing']['width'] : 1280;
		}

		if ( empty( $height ) ) {
			// calculate height relative to width
			$height = ! empty( $original ) ?
				round( $original['height'] * ($width / $original['width']) ) :
				$height = $width / 16 * 9;
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}


	/**
	 * Parse the autoplay options to determine if video should or should not
	 * autoplay.
	 *
	 * @param  {assoic} $options
	 * @return {bool}
	 */
	private static function parse_autoplay_options( $options ) {
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
		//$islazy = 'overlay' === $mode || 'dynamic' === $mode;
		$isajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if (
			! empty( $options['autoplay']['lazy'] ) &&
			$options['autoplay']['lazy'] &&
			$isajax
		) {
			return true;
		}

		if (
			! empty( $options['autoplay']['single'] ) &&
			$options['autoplay']['single'] &&
			is_single()
		) {
			return true;
		}

		return false;
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
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s LIMIT 1",
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
}
