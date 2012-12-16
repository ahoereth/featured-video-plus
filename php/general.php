<?php
/**
 * Class containing all functions needed on front- AND backend. Functions only needed on one of those are found in distinct classes.
 *
 * @author ahoereth
 * @version 2012/12/07
 * @see ../featured_video_plus.php
 * @see featured_video_plus_backend in backend.php
 * @see featured_video_plus_frontend in frontend.php
 * @since 1.0
 */
class featured_video_plus {

	/**
	 * Returns the featured video html, ready to echo.
	 *
	 * @param int $post_id
	 * @param string|array $size
	 * @param bool $allowfullscreen
	 * @param bool $container
	 */
	public function get_the_post_video($post_id = null, $size = array( 560, 315 ), $allowfullscreen = true, $container = true) {

		if($post_id == null)
			$post_id = $GLOBALS['post']->ID;

		if( !$this->has_post_video($post_id) )
			return false;

		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );
		$options = get_option( 'fvp-settings' );

		$width = $size[0];
		$height = ($options['height'] == 'auto') ? round($width / 16 * 9) : $size[1];

		if( isset($meta['id']) && !empty($meta['id']) ) {
			switch( $meta['prov'] ) {

				/*case 'local':
					$ext = pathinfo($meta['full'], PATHINFO_EXTENSION);
					//$embed = '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="' . $meta['full'] . '" /> <embed type="video/flash" width=' . $width . ' height="' . $height . '" src="' . $meta['full'] . '"></embed></object>';
					//$embed = '<video width="' . $width . '" height="' . $height . '" controls><source src="' . $meta['full'] . '" type="video/' . $ext . '">Your browser does not support the video tag.</video>' . "\n";
					$embed = '<video src="' . $meta['full'] . '" controls="controls" width="' . $width . '" height="' . $height . '"></video>';
					break;
				*/

				case 'vimeo':
					$options = get_option( 'fvp-settings' );
					$fs = $allowfullscreen ? ' webkitAllowFullScreen mozallowfullscreen allowFullScreen' : '';
					$embed = "\n" . '<iframe src="http://player.vimeo.com/video/'.$meta['id'].'?badge=0&amp;portrait='.$options['vimeo']['portrait'].'&amp;title='.$options['vimeo']['title'].'&amp;byline='.$options['vimeo']['byline'].'&amp;color='.$options['vimeo']['color'].'" width="'.$width.'" height="'.$height.'" frameborder="0"'.$fs.'></iframe>' . "\n";
					break;

				case 'youtube':
					$fs = $allowfullscreen ? 'allowfullscreen' : '';
					$attr = '#t=' . $meta['attr'];
					$embed = "\n" . '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$meta['id'].'?rel=0'.$attr.'" frameborder="0" ' . $fs . '></iframe>' . "\n";
					break;

				case 'dailymotion':

					$embed = "\n" . '<iframe src="http://www.dailymotion.com/embed/video/'.$meta['id'].'?logo=1&amp;info='.$options['vimeo']['title'].'" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>' . "\n";

					break;
			}

			if($container)
				$embed = '<div class="featured_video_plus">' . $embed . '</div>';

			return $embed;
		}

	}

	/**
	 * Checks if current post or post provided by parameter has a featured video.
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
	 * @param array $atts can contain the width and/or height how the featured video should be displayed in px, optional
	 */
	function shortcode($atts){

		$w = isset($atts['width'])  ? $atts['width'] : '560';
		$h = isset($atts['height']) ? $atts['height'] : '315';

		if($this->has_post_video())
			echo $this->get_the_post_video(null, $w, $h, true, false);

	}
}
?>