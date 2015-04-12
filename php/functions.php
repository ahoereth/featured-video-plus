<?php

/**
 * Checks if post has a featured video
 *
 * @since 1.0
 *
 * @param post_id
 */
function has_post_video( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! isset( $meta ) || empty( $meta['full'] ) ) {
		return false;
	}

	return true;
}

/**
 * Returns the posts featured video
 *
 * @since 1.0
 *
 * @param post_id
 * @param size
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
 * @param size
 */
function the_post_video( $size = null ) {
	echo get_the_post_video( null, $size );
}

/**
 * Returns the post video image's url
 *
 * @since 1.4
 *
 * @param post_id
 */
function get_the_post_video_image_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! isset( $meta ) || empty( $meta['full'] ) ) {
		return false;
	}

	// new way, post 2.0.0
	if ( ! empty( $meta['img_url'] ) ) {
		return $meta['img_url'];
	}

	// old way, pre 2.0.0
	global $featured_video_plus;
	$video_img = $featured_video_plus->get_post_by_custom_meta(
		'_fvp_image',
		$meta['provider'].'?'.$meta['id']
	);
	return wp_get_attachment_url( $video_img );
}

/**
 * Returns the post video image img tag including size.
 *
 * @since 1.4
 *
 * @param post_id
 * @param size
 */
function get_the_post_video_image( $post_id = null, $size = null ) {
	$meta = get_post_meta( $post_id, '_fvp_video', true );
	if ( ! isset( $meta ) || empty( $meta['full'] ) ) {
		return false;
	}

	global $featured_video_plus;
	$size = $featured_video_plus->get_size( $size );

	// new, post 2.0.0
	if ( ! empty( $meta['img'] ) ) {
		return wp_get_attachment_image( $meta['img'], $size );
	}

	// old, pre 2.0.0
	$id = $featured_video_plus->get_post_by_custom_meta(
		'_fvp_image',
		$meta['provider'] . '?' . $meta['id']
	);
	return wp_get_attachment_image( $id, $size );
}

/**
 * Returns the post video url.
 *
 * @since 1.6
 *
 * @param  int $post_id
 * @return mixed boolean (false) when no url/ string with url
 */
function get_the_post_video_url( $post_id ) {
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
