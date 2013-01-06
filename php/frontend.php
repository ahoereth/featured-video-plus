<?php
/**
 * Class containing functions required on frontend. Enqueue scripts/styles, replace featured images by featured videos.
 *
 * @author ahoereth
 * @version 2012/12/07
 * @see ../featured_video_plus.php
 * @see featured_video_plus in general.php
 * @since 1.0
 *
 * @param featured_video_plus instance
 */
class featured_video_plus_frontend {
	private $featured_video_plus = null;

	/**
	 * Creates a new instace of this class, saves the featured_video_instance.
	 *
	 * @since 1.0
	 *
	 * @param featured_video_plus_instance required, dies without
	 */
	function __construct( $featured_video_plus_instance ) {
        if ( !isset($featured_video_plus_instance) )
            wp_die( 'featured_video_plus general instance required!', 'Error!' );

		$this->featured_video_plus = $featured_video_plus_instance;
	}

	/**
	 * Enqueue all scripts and styles needed when viewing the frontend.
	 *
	 * @since 1.0
	 */
	public function enqueue() {
	}

	/**
	 * Display featured videos in place of featured images if a featured video is available and only if so desired by user.
	 *
	 * @see http://wordpress.stackexchange.com/a/41858
	 * @since 1.0
	 *
	 * @param string $html featured image html, ready to echo
	 * @param int $post_id id of target post
	 * @param int $post_thumbnail_id id of featured image
	 * @param string|array $size desired size of featured image / video
	 * @param array $attr
	 */
	public function filter_post_thumbnail($html, $post_id, $post_thumbnail_id, $size, $attr) {
		global $_wp_additional_image_sizes;

		$options = get_option( 'fvp-settings' );
		if( !$options['overwrite'] || !$this->featured_video_plus->has_post_video( $post_id ) )
			return $html;

		if( isset($_wp_additional_image_sizes[$size]) )
			$size = array( $_wp_additional_image_sizes[$size]['width'], $_wp_additional_image_sizes[$size]['height'] );
		else {

			if( $size == 'thumbnail' || $size == 'thumb' )
				$size = array( get_option( 'thumbnail_size_w' ), get_option( 'thumbnail_size_h' ) );
			else if( $size == 'medium' )
				$size = array( get_option( 'medium_size_w' ), get_option( 'medium_size_h' ) );
			else if( $size == 'large' )
				$size = array( get_option( 'large_size_w' ), get_option( 'large_size_h' ) );

		}

		return $this->featured_video_plus->get_the_post_video( $post_id, $size );

	}
}
?>