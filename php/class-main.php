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

		if( !has_post_video($post_id) )
			return false;

		$meta    = get_post_meta($post_id, '_fvp_video', true);
		$options = get_option( 'fvp-settings' );

		$size = $this->get_size($size);
		$size = array( 'width' => $size[0], 'height' => $size[1] );

		if( ! is_admin() ) {
			switch ( $options['autoplay'] ) {
				case 'yes':
					$autoplay = '1';
					break;
				case 'auto':
					if (( is_single() ) ||
						  ( defined('DOING_AJAX') && DOING_AJAX &&
						  ( $options['usage'] == 'dynamic' || $options['usage'] == 'overlay')))
						$autoplay = '1';
				case 'no':
				default:
					$autoplay = '0';
					break;
			}
		} else
			$autoplay = '0';

		$valid = $meta['valid'];

		$provider =   ! empty( $meta['provider'] ) ? $meta['provider'] :
		            ( ! empty( $meta['prov'] )     ? $meta['prov']     : null );

		switch ( $provider ) {
			case 'local':
				$videourl = wp_get_attachment_url( $meta['id'] );

				$ext = pathinfo( $videourl, PATHINFO_EXTENSION );
				if( $ext != 'mp4' && $ext != 'ogv' && $ext != 'webm' && $ext != 'ogg' )
					break;

				$videometa = wp_get_attachment_metadata( $meta['id'] );

				$atts = array(
					'src'      => $videourl,
					'poster'   => ! empty( $options['local']['poster'] ) && $options['local']['poster'] && has_post_thumbnail( $post_id ) ? wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) : '',
					'loop'     => ! empty( $options['local']['loop'] ) && $options['local']['loop'] ? 'on' : 'off',
					'autoplay' => $autoplay == '1' ? 'on' : null,
					'preload'  => null, // $size['height'], //$size['width'], //
					'height'   => $options['sizing']['hmode' ] == 'auto' && ! is_admin() ? ( $options['sizing']['wmode' ] == 'auto' ? $videometa['height'] * 8 : $videometa['height'] / $videometa['width'] * $videometa['height'] ) : $size['height'],
					'width'    => $options['sizing']['wmode' ] == 'auto' && ! is_admin() ? $videometa['width'] * 8 : $size['width'],
				);

				$embed = wp_video_shortcode( $atts );
				break;


			default:
				$args = array_merge(
					array( 'fvp' => $this->oembed->time ),
					$size,
					! empty( $meta['parameters'] ) ? $meta['parameters'] : array()
				);

				$embed = $this->oembed->get_html( $meta['full'], $args, $provider );
				break;
		}

		if ( ! $embed ) return '';

		$class = $options['sizing']['wmode' ] == 'auto' ? ' responsive' : '';
		$containerstyle = isset($options['sizing']['align']) ? ' style="text-align: '.$options['sizing']['align'].'"' : '';
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

		if( !is_array($size) ) {
			if( isset($_wp_additional_image_sizes[$size]) )
				$width = $_wp_additional_image_sizes[$size]['width'];
			elseif( $size == 'thumbnail' || $size == 'thumb' )
				$width = get_option( 'thumbnail_size_w' );
			else if( $size == 'medium' )
				$width = get_option( 'medium_size_w' );
			else if( $size == 'large' )
				$width = get_option( 'large_size_w' );
			elseif( isset($options['sizing']['wmode']) && $options['sizing']['wmode'] == 'fixed' )
				$width = $options['sizing']['width']; // auto width is applied by fitvids JS
			else
				$width = 560;

		} elseif( !empty( $size[0] ) && is_numeric( $size[0] ) )
			$width  = $size[0];
		elseif( isset($options['sizing']['wmode']) && $options['sizing']['wmode'] == 'fixed' )
			$width = $options['sizing']['width']; // auto width is applied by fitvids JS
		else
			$width = 560;

		if( isset($size[1]) && !empty( $size[1] ) && is_numeric( $size[1] ) )
			$height = $size[1];
		else
			$height = $options['sizing']['hmode'] == 'auto' ? round($width / 16 * 9) : $options['sizing']['height'];

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
