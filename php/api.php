<?php
/* requires an featured_video_plus class instance, located in php/general.php
 *
 * @see featured-video-plus.php
 * @see php/general.php
 */

/**
 * echos the current posts featured video
 *
 * @since 1.0
 */
function the_post_video( $size = null) {
	echo get_the_post_video(null, $size);
}

/**
 * returns the posts featured video
 *
 * @since 1.4
 */
function get_the_post_video($post_id = null, $size = null) {
	global $featured_video_plus;
	return $featured_video_plus->get_the_post_video($post_id, $size);
}

/**
 * checks if post has a featured video
 *
 * @since 1.0
 */
function has_post_video($post_id = null){
	global $featured_video_plus;
	return $featured_video_plus->has_post_video($post_id);
}

/**
 * Returns the post video images source url
 *
 * @since 1.4
 */
function the_post_video_image_src($post_id = null) {
	if($post_id == null)
		$post_id = $GLOBALS['post']->ID;

	$meta = unserialize( get_post_meta( $post_id, '_fvp_video', true ) );

	if( !isset($meta) || empty($meta['id']) )
		return false;

	$video_img = $featured_video_plus->get_post_by_custom_meta('_fvp_image', $meta['prov'] . '?' . $meta['id']);

}
?>