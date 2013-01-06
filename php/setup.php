<?php
/**
 * Class containing functions to run on plugin activation, deactivation and uninstallation.
 *
 * @author ahoereth
 * @version 2012/12/07
 * @see http://wordpress.stackexchange.com/a/25979
 * @since 1.0
 */
class featured_video_plus_setup {

	/**
	 * Just checks if the class was called directly, if yes: dies.
	 *
	 * @since 1.0
	 *
	 * @param $case = false test parameter
	 */
	function __construct( $case = false ) {
		if ( ! $case )
			wp_die( 'You should not call this class directly!', 'Doing it wrong!' );
	}

	/**
	 * Runs on activation. Required for initializing plugin options on first
	 * activation.
	 *
	 * @since 1.0
	 */
	public function on_activate() {
		$options = get_option( 'fvp-settings' );
		if( !isset($options['version']) || empty($options['version']) )
			featured_video_plus_upgrade( '0' );
	}

	/**
	 * Runs on uninstallation, deletes all data including post&user metadata and video screen captures.
	 *
	 * @since 1.0
	 */
	function on_uninstall() {
		// important: check if the file is the one that was registered with the uninstall hook (function)
		//if ( __FILE__ != WP_UNINSTALL_PLUGIN )
		//    return;

		delete_option( 'fvp-settings' );

		$post_types = get_post_types( array("public" => true) );
		foreach( $post_types as $post_type ) {
			if( $post_type != 'attachment' ) {
				$allposts = get_posts('numberposts=-1&post_type=' . $post_type . '&post_status=any');
				foreach( $allposts as $post ) {
					$meta = unserialize(get_post_meta( $post->ID, '_fvp_video', true ));

					wp_delete_attachment( $meta['img'] );
					delete_post_meta($post->ID, '_fvp_video');
				}
			}
		}

		$users = array_merge( get_users( array( 'role' => 'Administrator' ) ), get_users( array( 'role' => 'Super Admin' ) ) );
		foreach( $users as $user ) {
			delete_user_meta( $user-ID, 'fvp_activation_notification_ignore' );
		}
	}

}

/**
 * Is used on plugin upgrade and on first activation. Initializes options.
 *
 * @since 1.2
 */
function featured_video_plus_upgrade( $departure, $destination = FVP_VERSION ) {

	if( !isset($departure) || empty($departure) )
		return;

	$options = get_option( 'fvp-settings' );

	switch($departure) {

		case '1.1':
			$options = array_merge($options,
				array(
					'version' => $destination,
					'localvideos'  => false
				)
			);

			if($destination == "1.2")
				add_action('admin_notices', 'featured_video_plus_upgrade_11_12' );
			break;

		case '0':
			if( !isset($options) || empty($options) ) {
				$options = array(
					'version' => $destination,
					'overwrite' => true,
					'width' => 'auto',
					'height' => 'auto',
					'vimeo' => array(
						'portrait' => 0,
						'title' => 1,
						'byline' => 1,
						'color' => '00adef'
					),
					'localvideos' => false
				);
			}
			break;

	}

	update_option( 'fvp-settings', $options );
}

/**
 * Upgrade notification 1.1 to 1.2
 *
 * @see http://wptheming.com/2011/08/admin-notices-in-wordpress/
 * @since 1.2
 */
function featured_video_plus_upgrade_11_12() {
	echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
	printf('Featured Video Plus was upgraded. Version 1.2 now supports local featured videos. To activate this feature <span style="font-weight: bold;">take a look at the <a href="%1$s" title="Media Settings">Media Settings</a></span>', get_admin_url(null, '/options-media.php'));
	echo "</p></div>\n";
}

?>