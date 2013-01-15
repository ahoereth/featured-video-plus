<?php
/**
 * Class containing all functions needed on front- AND backend. Functions only needed on one of those are found in distinct classes.
 *
 * @author ahoereth
 * @version 2013/01/15
 * @see ../featured_video_plus.php
 * @see featured_video_plus_backend in backend.php
 * @see featured_video_plus_frontend in frontend.php
 * @since 1.0
 */
class featured_video_plus {

	/**
	 * Enqueue all scripts and styles needed when viewing the frontend and backend.
	 *
	 * @see http://videojs.com/
	 * @since 1.2
	 */
	public function enqueue($hook_suffix) {
		// just required on post.php
		if( !is_admin() || ( $hook_suffix == 'post.php' && isset($_GET['post']) ) ) {

			// http://videojs.com/
			wp_enqueue_script( 'videojs', 'http://vjs.zencdn.net/c/video.js' );
			wp_enqueue_style(  'videojs', 'http://vjs.zencdn.net/c/video-js.css' );

			$options = get_option( 'fvp-settings' );
			if( $options['sizing']['wmode'] == 'auto' )
				//wp_enqueue_script('fvp_fitvids', FVP_URL . '/js/jquery.fitvids_fvp-min.js', array( 'jquery' ), FVP_VERSION, true ); 	// production
				wp_enqueue_script('fvp_fitvids', FVP_URL . '/js/jquery.fitvids_fvp.js', array( 'jquery' ), FVP_VERSION, true ); 		// development
		}
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

		if($post_id == null)
			$post_id = $GLOBALS['post']->ID;

		if( !$this->has_post_video($post_id) )
			return false;

		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );
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
		else
			$width = 560;

		if( isset($size[1]) && !empty( $size[1] ) && is_numeric( $size[1] ) )
			$height = $size[1];
		else
			$height = $options['sizing']['hmode'] == 'auto' ? round($width / 16 * 9) : $options['sizing']['height'];

		if( isset($meta['id']) && !empty($meta['id']) ) {
			switch( $meta['prov'] ) {

				case 'local':
					$featimg = has_post_thumbnail($post_id) ? wp_get_attachment_url( get_post_thumbnail_id($post_id) ) : '';

					$ext = pathinfo( $meta['full'], PATHINFO_EXTENSION );
					if( $ext != 'mp4' && $ext != 'ogv' && $ext != 'webm' && $ext != 'ogg' ) break;
					$ext = $ext == 'ogv' ? 'ogg' : $ext;
					$embed = "\n\t".'<video class="video-js vjs-default-skin" controls preload="auto" width="'.$width.'" height="'.$height.'" poster="" data-setup="{}">'; // poster="'.$featimg.'" data-setup="{}"
					$embed .= "\n\t\t".'<source src="' . $meta['full'] . '" type="video/'.$ext.'">';

					if( isset($meta['sec_id']) && !empty($meta['sec_id']) ) {
						$ext2 = pathinfo( $meta['sec'], PATHINFO_EXTENSION );
						$ext2 = $ext2 == 'ogv' ? 'ogg' : $ext2;
						if( $ext2 == 'mp4' || $ext2 == 'ogv' || $ext2 == 'webm' || $ext2 == 'ogg' )
							$embed .= "\n\t\t".'<source src="' . $meta['sec'] . '" type="video/'.$ext2.'">';
					}

					$embed .= "\n\t</video>\n";
					break;

				case 'vimeo':
					$options = get_option( 'fvp-settings' );
					$embed = "\n\t" . '<iframe src="http://player.vimeo.com/video/'.$meta['id'].'?badge=0&amp;portrait='.$options['vimeo']['portrait'].'&amp;title='.$options['vimeo']['title'].'&amp;byline='.$options['vimeo']['byline'].'&amp;color='.$options['vimeo']['color'].'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' . "\n";
					break;

				case 'youtube':
					$youtube['attr'] 	= isset($meta['attr']) && !empty($meta['attr']) ? '#t=' . $meta['attr'] : '';
					$youtube['theme'] 	= isset($options['youtube']['theme']) 	? $options['youtube']['theme'] 	: 'dark';
					$youtube['color'] 	= isset($options['youtube']['color']) 	? $options['youtube']['color'] 	: 'red';
					$youtube['info'] 	= isset($options['youtube']['info']) 	? $options['youtube']['info'] 	: 1;
					$youtube['rel'] 	= isset($options['youtube']['rel']) 	? $options['youtube']['rel'] 	: 1;
					$youtube['fs'] 		= isset($options['youtube']['fs']) 		? $options['youtube']['fs'] 	: 1;

					$src = 'http://www.youtube.com/embed/'.$meta['id'].'?theme='.$youtube['theme'].'&color='.$youtube['color'].'&showinfo='.$youtube['info'].'&origin='.home_url().'&rel='.$youtube['rel'].'&fs='.$youtube['fs'].$youtube['attr'];
					$embed = "\n\t" . '<iframe width="'.$width.'" height="'.$height.'" src="'.$src.'" type="text/html" frameborder="0"></iframe>' . "\n";
					break;

				case 'dailymotion':
					$dm['foreground'] 	= isset($options['dailymotion']['foreground']) 	? 	$options['dailymotion']['foreground'] 	: 'F7FFFD';
					$dm['highlight'] 	= isset($options['dailymotion']['highlight']) 	? 	$options['dailymotion']['highlight'] 	: 'FFC300';
					$dm['background'] 	= isset($options['dailymotion']['background']) 	? 	$options['dailymotion']['background'] 	: '171D1B';
					$dm['logo'] 		= isset($options['dailymotion']['logo']) 		? 	$options['dailymotion']['logo'] 		: 1;
					$dm['hideinfo'] 	= isset($options['dailymotion']['info']) 		? 1-$options['dailymotion']['info'] 		: 0;
					$dm['src'] = 'http://www.dailymotion.com/embed/video/'.$meta['id'].'?logo='.$dm['logo'].'&hideInfos='.$dm['hideinfo'].'&foreground=%23'.$dm['foreground'].'&highlight=%23'.$dm['highlight'].'&background=%23'.$dm['background'];
					$embed = "\n" . '<iframe width="'.$width.'" height="'.$height.'" src="'.$dm['src'].'" frameborder="0"></iframe>' . "\n";
					break;

				case 'liveleak':
					$embed = "\n" . '<iframe width="'.$width.'" height="'.$height.'" src="http://www.liveleak.com/ll_embed?f='.$meta['id'].'" frameborder="0" allowfullscreen></iframe>';
					break;

				case 'prochan':
					$embed = "\n" . '<iframe width="'.$width.'" height="'.$height.'" src="http://www.prochan.com/embed?f='.$meta['id'].'" frameborder="0" allowfullscreen></iframe>';
					break;

				default:
					$embed = '';
					$container = false;
					break;

			}

			$containerstyle = isset($options['sizing']['align']) ? 'style="text-align: '.$options['sizing']['align'].'"' : '';
			$embed = "<div class=\"featured_video_plus\"{$containerstyle}>{$embed}</div>\n\n";

			$embed = "\n\n<!-- Featured Video Plus v".FVP_VERSION."-->\n" . $embed;

			return $embed;
		}

	}

	/**
	 * Checks if current post or post provided by parameter has a featured video.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id id of post to check for featured video
	 */
	public function has_post_video($post_id = null){

		if($post_id == null)
			$post_id = $GLOBALS['post']->ID;

		$meta = unserialize( get_post_meta( $post_id, '_fvp_video', true ) );

		if( !isset($meta) || empty($meta['id']) )
			return false;

		return true;

	}

	/**
	 * Shortcode for usage in post or page entries. Echos the post's featured video.
	 *
	 * @since 1.0
	 *
	 * @param array $atts can contain the width and/or height how the featured video should be displayed in px, optional
	 */
	function shortcode($atts){

		$w = isset($atts['width'])  ? $atts['width'] : '';
		$h = isset($atts['height']) ? $atts['height'] : '';

		if($this->has_post_video())
			echo $this->get_the_post_video(null, array($w, $h));

	}

	/**
	 * Initializes localization i18n
	 *
	 * @since 1.3
	 */
	function language() {
		load_plugin_textdomain('featured-video-plus', FVP_DIR . 'lng/', FVP_NAME . '/lng/' );
	}
}
?>