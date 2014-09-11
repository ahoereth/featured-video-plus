<?php
/**
 * Class containing all functions needed on front- AND backend. Functions only needed on one of those are found in distinct classes.
 *
 * @since 1.0
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
	 * @since 1.0
	 *
	 * @param int $post_id
	 * @param string|array $size
	 * @param bool $allowfullscreen
	 * @param bool $container
	 */
	public function get_the_post_video($post_id = null, $size = null) {
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

		if( ! has_post_video( $post_id ) )
			return false;

		$meta    = get_post_meta( $post_id, '_fvp_video', true );
		$options = get_option( 'fvp-settings' );
		$defaults = $options['default_args'];

		$size = $this->get_size( $size );
		$size = array( 'width' => $size[0], 'height' => $size[1] );

		$defaults['general']['autoplay'] = ! empty( $options['default_args']['general']['autoplay'] ) && ! is_admin();

		$valid = $meta['valid'];

		// provider key was 'prov' pre 2.0.0
		$provider =   ! empty( $meta['provider'] ) ? $meta['provider'] :
		            ( ! empty( $meta['prov'] )     ? $meta['prov']     : null );

		switch ( $provider ) {
			case 'local':
				$videourl  = wp_get_attachment_url( $meta['id'] );
				$videometa = wp_get_attachment_metadata( $meta['id'] );

				// use massive video size/height for responsive videos because
				// fitvids does not upscale videos
				$width  = $size['width'];
				$height = $size['height'];
				if ( $options['sizing']['wmode'] == 'auto' && ! is_admin() ) {
					$width = $videometa['width'] * 8;
				}
				if ( $options['sizing']['hmode'] == 'auto' && ! is_admin() ) {
					if ( $options['sizing']['wmode'] == 'auto' ) {
						$height = $videometa['height'] * 8;
					} else {
						$height = $videometa['height'] / $videometa['width'] * $videometa['height'];
					}
				}

				$atts = array(
					'src'      => $videourl,
					'width'    => $width,
					'height'   => $height,
					'autoplay' => $defaults['general']['autoplay']        ? 'on' : null,
					'loop'     => ! empty( $defaults['general']['loop'] ) ? 'on' : null
				);

				$embed = wp_video_shortcode( $atts );
				break;


			default:
				$args = array_merge(
					array( 'fvp' => $this->oembed->time ),
					$size,
					isset( $options['default_args']['general'] ) ? $options['default_args']['general'] : array(),
					isset( $options['default_args'][$provider] ) ? $options['default_args'][$provider] : array(),
					isset( $meta['parameters'] ) ? $meta['parameters'] : array()
				);

				$embed = $this->oembed->get_html( $meta['full'], $args, $provider );
				break;
		}

		if ( empty( $embed ) )
			return false;

		$class = $options['sizing']['wmode' ] == 'auto' ? ' responsive' : '';
		$containerstyle = isset( $options['sizing']['align'] ) ?
			' style="text-align: '.$options['sizing']['align'].'"' : '';

		$embed = "<div class=\"featured_video_plus{$class}\"{$containerstyle}>{$embed}</div>\n\n";
		$embed = "\n\n<!-- Featured Video Plus v".FVP_VERSION."-->\n" . $embed;

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
	protected function get_size( $size = null ) {
		$options = get_option( 'fvp-settings' );

		// fixed size requested as array( width, height )
		if ( is_array( $size ) ) {
			if ( ! empty( $size[0] ) && is_numeric( $size[0] ) ) {
				$width  = $size[0];
			}
			if ( ! empty( $size[1] ) && is_numeric( $size[1] ) ) {
				$height  = $size[1];
			}

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

		// single number provided - use it for the width, calculate height as 16/9
		} elseif ( is_numeric( $size ) ) {
			$width = $size;
			$height = $options['sizing']['hmode'] == 'auto' ?
				round($width / 16 * 9) : $options['sizing']['height'];
		}

		if ( empty( $width ) ) {
			$width  = ! empty( $options['sizing']['width'] ) ? $options['sizing']['width'] : 640;
		}

		if ( empty( $height ) ) {
			$height = ! empty( $options['sizing']['height'] ) ? $options['sizing']['height'] : 360;
		}

		return array( $width, $height );
	}


	/**
	 * Gets a post by an meta_key meta_value pair. Returns it's post_id.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @see http://dev.mysql.com/doc/refman/5.0/en/regexp.html#operator_regexp
	 * @since 1.0
	 *
	 * @param string $meta_key which meta_key to look for
	 * @param string $meta_value which meta_value to look for
	 */
	protected function get_post_by_custom_meta($meta_key, $meta_value = null) {
		global $wpdb;
		if( $meta_value !== null ) {
			$prepared = $wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s LIMIT 1",
							$meta_key, $meta_value
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
	 * @since 1.3
	 */
	public function language() {
		load_plugin_textdomain('featured-video-plus', FVP_DIR . 'lng/', FVP_NAME . '/lng/' );
	}

}
