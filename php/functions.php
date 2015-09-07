<?php

if ( ! function_exists('has_post_video') ) :
/**
 * Checks if post has a featured video
 *
 * @since 1.0
 *
 * @param  {int} post_id
 * @return {boolean}
 */
function has_post_video( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! isset( $meta ) || empty( $meta['full'] ) ) {
		return false;
	}

	return true;
}
endif;


/**
 * Returns the posts featured video
 *
 * @since 1.0
 *
 * @param  {int}   post_id
 * @param  {mixed} size
 * @return {string/boolean} html string or false on failure
 */
function get_the_post_video( $post_id = null, $size = null ) {
	global $featured_video_plus;
	return apply_filters(
		'get_the_post_video_filter',
		$featured_video_plus->get_the_post_video( $post_id, $size )
	);
}


/**
 * Echos the current posts featured video
 *
 * @since 1.0
 *
 * @param {mixed} size
 */
function the_post_video( $size = null ) {
	echo get_the_post_video( null, $size );
}


/**
 * Returns the attachment id of the video image.
 *
 * @since  2.0.0
 *
 * @param  {int} $post_id
 * @return {int}
 */
function get_the_post_video_image_id( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! empty( $meta['img'] ) ) {
		return $meta['img'];
	}

	return false;
}


/**
 * Returns the post video image's url
 *
 * @since 1.4
 *
 * @param  {int} post_id
 * @return {string/boolean} url or false on failure
 */
function get_the_post_video_image_url( $post_id = null ) {
	$img_id = get_the_post_video_image_id( $post_id );
	return wp_get_attachment_url( $img_id );
}


/**
 * Returns the post video image img tag including size.
 *
 * @since 1.4
 *
 * @param  {int} post_id
 * @param  {mixed} size
 * @return {string/boolean} html string or false on failure
 */
function get_the_post_video_image( $post_id = null, $size = null ) {
	$img_id = get_the_post_video_image_id( $post_id );
	return wp_get_attachment_image( $img_id, $size );
}


/**
 * Returns the post video url.
 *
 * @since 1.6
 *
 * @param  {int} $post_id
 * @return {string/boolean} url or false
 */
function get_the_post_video_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! isset( $meta ) || empty( $meta['full'] ) ) {
		return false;
	}

	if ( isset( $meta['provider'] ) && 'local' === $meta['provider'] ) {
		return wp_get_attachment_url( $meta['id'] );
	} else if ( isset( $meta['full'] ) ) {
		return $meta['full'];
	}

	return false;
}
