<?php
/**
 * Class containing all functions needed on front- AND backend. Functions only needed on one of those are found in distinct classes.
 *
 * @author ahoereth
 * @see ../featured_video_plus.php
 * @see featured_video_plus_backend in backend.php
 * @see featured_video_plus_frontend in frontend.php
 * @since 1.0
 */
class featured_video_plus {

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

		$meta 	= get_post_meta($post_id, '_fvp_video', true);
		$options= get_option( 'fvp-settings' );

		$size 	= $this->get_size($size);
		$size 	= array( 'width' => $size[0], 'height' => $size[1] );

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

		switch ( $meta['prov'] ) {
			case 'local':
				// mediaelement.js is only available in WordPress 3.6 and higher.
				if( get_bloginfo('version') < 3.6 ) break;

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
					'height'   => $options['sizing']['hmode' ] == 'auto' && ! is_admin() ? $videometa['height'] * 8 : $size['height'],
					'width'    => $options['sizing']['wmode' ] == 'auto' && ! is_admin() ? $videometa['width'] * 8 : $size['width'],
				);

				$embed = wp_video_shortcode( $atts );
				break;

			case 'vimeo':
				$options = get_option( 'fvp-settings' );
				$src = 'http://player.vimeo.com/video/'.$meta['id'].'?badge=0&amp;portrait='.$options['vimeo']['portrait'].'&amp;title='.$options['vimeo']['title'].'&amp;byline='.$options['vimeo']['byline'].'&amp;color='.$options['vimeo']['color'].'&autoplay='.$autoplay;
				$embed = "\n\t" . '<iframe src="'.$src.'" width="'.$size['width'].'" height="'.$size['height'].'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' . "\n";
				break;

			case 'youtube':
				$theme = isset($options['youtube']['theme']) ? $options['youtube']['theme'] : 'dark';
				$color = isset($options['youtube']['color']) ? $options['youtube']['color'] : 'red';
				$jsapi = isset($options['youtube']['jsapi']) ? $options['youtube']['jsapi'] : '0&playerapiid=fvpyt'.$post_id;
				$info  = isset($options['youtube']['info'])  ? $options['youtube']['info'] 	: 1;
				$logo  = isset($options['youtube']['logo'])  ? $options['youtube']['logo'] 	: 1;
				$rel 	 = isset($options['youtube']['rel']) 	 ? $options['youtube']['rel'] 	: 1;
				$fs 	 = isset($options['youtube']['fs']) 	 ? $options['youtube']['fs'] 		: 1;
				$wmode = isset($options['youtube']['wmode'])&& $options['youtube']['wmode'] != 'auto' ? '&wmode='.$options['youtube']['wmode'] : '';

				$src = 'http://www.youtube.com/embed/'.$meta['id'].'?theme='.$theme.$wmode.'&color='.$color.'&showinfo='.$info.'&modestbranding='.$logo.'&enablejsapi='.$jsapi.'&origin='.esc_attr(home_url()).'&rel='.$rel.'&fs='.$fs.'&start='.$meta['time'].'&autoplay='.$autoplay;
				$embed = "\n\t" . '<iframe width="'.$size['width'].'" height="'.$size['height'].'" src="'.$src.'" type="text/html" frameborder="0" id="fvpyt'.$post_id.'"></iframe>' . "\n";
				break;

			case 'dailymotion':
				$foreground  = isset($options['dailymotion']['foreground'])  ? 	$options['dailymotion']['foreground'] : 'F7FFFD';
				$highlight 	 = isset($options['dailymotion']['highlight']) 	 ? 	$options['dailymotion']['highlight'] 	: 'FFC300';
				$background  = isset($options['dailymotion']['background'])  ? 	$options['dailymotion']['background'] : '171D1B';
				$logo 	     = isset($options['dailymotion']['logo']) 			 ? 	$options['dailymotion']['logo'] 			: 1;
				$hideinfo 	 = isset($options['dailymotion']['info']) 			 ?1-$options['dailymotion']['info'] 			: 0;
				$syndication = empty($options['dailymotion']['syndication']) ? 	'' : '&syndication='.$options['dailymotion']['syndication'];

				$dm['src'] = 'http://www.dailymotion.com/embed/video/'.$meta['id'].'?logo='.$logo.'&hideInfos='.$hideinfo.'&foreground=%23'.$foreground.'&highlight=%23'.$highlight.'&background=%23'.$background.$syndication.'&start='.$meta['time'].'&autoplay='.$autoplay;
				$embed = "\n" . '<iframe width="'.$size['width'].'" height="'.$size['height'].'" src="'.$dm['src'].'" frameborder="0"></iframe>' . "\n";
				break;

			case 'liveleak':
			$embed = "\n" . '<iframe width="'.$size['width'].'" height="'.$size['height'].'" src="http://www.liveleak.com/ll_embed?f='.$meta['id'].'" frameborder="0" allowfullscreen></iframe>';
				break;

			case 'prochan':
				$embed = "\n" . '<iframe width="'.$size['width'].'" height="'.$size['height'].'" src="http://www.prochan.com/embed?f='.$meta['id'].'" frameborder="0" allowfullscreen></iframe>';
				break;

			default:
				$embed = wp_oembed_get($meta['full'], $size);
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
	 */
	function get_size($size = null) {
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
	function get_post_by_custom_meta($meta_key, $meta_value = null) {
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
	function language() {
		load_plugin_textdomain('featured-video-plus', FVP_DIR . 'lng/', FVP_NAME . '/lng/' );
	}
}
