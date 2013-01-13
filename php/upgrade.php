<?php
/**
 * Is used on plugin upgrade and on first activation. Initializes and upgrades options, places notice etc.
 *
 * @since 1.2
 */
function featured_video_plus_upgrade() {
	$options = get_option( 'fvp-settings', 'none' );

	if( !isset($options) || $options == 'none')
		$version = '0';
	elseif( !isset($options['version']) )
		$version = '1.1';
	elseif( $options['version'] == FVP_VERSION )
		return;
	else
		$version = $options['version'];

	switch( $version ) {
		case '0':
			$notice = 'initial_activation';

			$options = array(
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


		case '1.0':
		case '1.1':
			$notice = isset($notice) ? $notice : 'upgrade_11';

			if( $version == '1.0' || $version == '1.1' ) {
				// removed this user meta in 1.2
				$users = array_merge( 	get_users( array( 'role' => 'Administrator' ) ),
										get_users( array( 'role' => 'Super Admin' ) ) );
				foreach( $users as $user )
					delete_user_meta( $user->ID, 'fvp_activation_notification_ignore' );
			}


		case '1.2':
			$notice = isset($notice) ? $notice : 'upgrade_12';

			$options['reg'] = '';
			$options['videojs'] = array(
					'skin' => 'videojs' //videojs,moonfy,tubejs
				);
			$options['youtube'] = array(
					'theme' => 'dark',
					'color' => 'red',
					'info' 	=> 1,
					'rel' 	=> 1,
					'fs' 	=> 1
				);
			$options['dailymotion'] = array(
					'foreground' => 'F7FFFD',
					'highlight' => 'FFC300',
					'background' => '171D1B',
					'logo' 	 => 1,
					'info' 	 => 1
				);
			$options['sizing'] = array(
					'wmode'  => $options['width'],
					'hmode'  => $options['height'],
					'width'  => 560,
					'height' => 315
				);
			unset( $options['width'], $options['height'] );


			// this has to be refreshed in every version, stays last
			$options['version'] = FVP_VERSION;
			break;
	}

	$url 	= 'http://fvp.ahoereth.yrnxt.com/fvp_reg.php';
	$params = '?fvp_reg='.$options['reg'].'&fvp_version='.urlencode(FVP_VERSION).'&wp_version='.urlencode(get_bloginfo('version')).'&wp_language='.urlencode(get_bloginfo('language'));
	$result = wp_remote_get( $url . $params );
	if( strlen($result['body']) == 23 && empty($options['reg']) )
		$options['reg'] = $result['body'];

	update_option( 'fvp-settings', $options );

	$featured_video_plus_notices = new featured_video_plus_notices();
	add_action('admin_notices', array( &$featured_video_plus_notices, $notice ) );
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
	 * Upgrade notification 1.2 to current version
	 *
	 * @since 1.3
	 */
	function upgrade_12() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf('Featured Video Plus was <span style="font-weight: bold;">upgraded</span>. Version <span style="font-weight: bold;">'.FVP_VERSION.'</span> features more customization settings and a more dynamic admin interface. If you like the plugin please <a href="%1$s">rate it</a>.', 'http://wordpress.org/extend/plugins/featured-video-plus/');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.1 to current version
	 *
	 * @since 1.2
	 */
	function upgrade_11() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf('Featured Video Plus was <span style="font-weight: bold;">upgraded</span>. Version <span style="font-weight: bold;">'.FVP_VERSION.'</span> now supports <span style="font-weight: bold;">local videos</span>. If you like the plugin please <a href="%1$s">rate it</a>.', 'http://wordpress.org/extend/plugins/featured-video-plus/');
		echo "</p></div>\n";
	}

	/**
	 * Notification shown when plugin is newly activated.
	 * Upgrade from 0 to current version
	 *
	 * @since 1.0
	 */
	function initial_activation() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf('Featured Video Plus is ready to use. There is a new box on post & page edit screens for you to add video URLs. <span style="font-weight: bold;">Take a look at your new <a href="%1$s" title="Media Settings">Media Settings</a></span>.', get_admin_url(null, '/options-media.php'));
		echo "</p></div>\n";
	}

}
?>