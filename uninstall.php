<?php

/**
 * Runs on uninstallation, deletes all data including post metadata,
 * video screen captures and options.
 *
 * @since 1.2
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'fvp-settings' );

$post_types = get_post_types( array( 'public' => true ) );
foreach ( $post_types AS $post_type ) {
	if ( 'attachment' === $post_type ) {
		continue;
	}

	$selector = 'numberposts=-1&post_type=' . $post_type . '&post_status=any';
	$allposts = get_posts( $selector );

	foreach ( $allposts AS $post ) {
		$meta = get_post_meta( $post->ID, '_fvp_video', true );
		wp_delete_attachment( $meta['img'] );
		delete_post_meta( $meta['img'], '_fvp_image' );
		delete_post_meta( $post->ID, '_fvp_video' );
	}
}
