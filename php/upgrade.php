<?php
/**
 * Is used on plugin upgrade and on first activation. Initializes and upgrades options, places notice etc.
 *
 * @since 1.2
 */
function featured_video_plus_upgrade() {

	$featured_video_plus_notices = new featured_video_plus_notices();

	$options = get_option( 'fvp-settings', 'none' );
	if( !isset($options) || $options == 'none')
		$version = '0';
	elseif( !isset($options['version']) )
		$version = '1.1';
	else
		$version = $options['version'];

	switch( $version ) {

		case '0':
			$options = array(
				'version' => '1.2',
				'overwrite' => true,
				'width' => 'auto',
				'height' => 'auto',
				'vimeo' => array(
					'portrait' => 0,
					'title' => 1,
					'byline' => 1,
					'color' => '00adef'
				)
			);

			add_action('admin_notices',  array( &$featured_video_plus_notices, 'initial_activation' ) );
			break;

		case '1.0':
		case '1.1':
			$options['version'] = '1.2';

			// remove no longer needed user meta
			$users = array_merge( get_users( array( 'role' => 'Administrator' ) ), get_users( array( 'role' => 'Super Admin' ) ) );
			foreach( $users as $user ) {
				delete_user_meta( $user->ID, 'fvp_activation_notification_ignore' );
			}

			add_action('admin_notices',  array( &$featured_video_plus_notices, 'upgrade_11' ) );
			break;

	}

	update_option( 'fvp-settings', $options );

}

/**
 * Class containing notices for upgrading the plugin
 *
 * @author ahoereth
 * @version 2013/01/08
 * @see http://wptheming.com/2011/08/admin-notices-in-wordpress/
 * @since 1.2
 */
class featured_video_plus_notices {

	/**
	 * Upgrade notification 1.1 to 1.2
	 *
	 * @since 1.2
	 */
	function upgrade_11() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf('Featured Video Plus was <span style="font-weight: bold;">upgraded</span>. Version <span style="font-weight: bold;">1.2</span> now supports <span style="font-weight: bold;">local videos</span>. If you like the plugin please <a href="%1$s">rate it</a>.', 'http://wordpress.org/extend/plugins/featured-video-plus/');
		echo "</p></div>\n";
	}

	/**
	 * Notification shown when plugin is newly activated.
	 *
	 * @since 1.0
	 */
	function initial_activation() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf('Featured Video Plus is ready to use. There is a new box on post & page edit screens for you to add video URLs. <span style="font-weight: bold;">Take a look at your new <a href="%1$s" title="Media Settings">Media Settings</a></span>.', get_admin_url(null, '/options-media.php?fvp_activation_notification_ignore=0'));
		echo "</p></div>\n";
	}

}
?>