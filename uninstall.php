<?php

/**
 * Runs on uninstallation, deletes all data including post metadata,
 * video screen captures and options.
 */
function featured_video_plus_uninstall() {
	global $wpdb;

	// Get posts with featured videos.
	$ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s",
		'_fvp_video'
	) );

	// For each post remove FVP data.
	foreach ( $ids AS $id ) {
		$meta = get_post_meta( $id, '_fvp_video', true );

		if ( ! empty( $meta ) ) {
			wp_delete_attachment( $meta['img'] );
			delete_post_meta( $meta['img'], '_fvp_image' );
			delete_post_meta( $id, '_fvp_video' );
		}
	}

	// Delete options row.
	delete_option( 'fvp-settings' );
}

// Run uninstall.
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	featured_video_plus_uninstall();
}
