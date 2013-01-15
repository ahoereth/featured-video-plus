<?php
/**
 * Is used on plugin upgrade and on first activation. Initializes and upgrades options, places notice etc.
 *
 * @since 1.2
 */
function featured_video_plus_upgrade() {
	$options = $options_org = get_option( 'fvp-settings', 'none' );

	if( !isset($options) || $options == 'none')
		$version = '0';
	elseif( !isset($options['version']) )
		$version = '1.1';
	else
		$version = $options['version'];

	if( $version != FVP_VERSION ) {
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

				$options['notice'] 	= $notice;
				$options['reged'] 	= false;
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

		$featured_video_plus_notices = new featured_video_plus_notices();
		add_action('admin_notices', array( &$featured_video_plus_notices, $notice ) );
	}

	// Logs Plugin Version, WordPress Version, WordPress Language once.
	// Less than WordPress.org, but much more informative for future development
	if( !isset($options['reged']) || !$options['reged'] || ($version != FVP_VERSION) ) {
		$options['reged'] = false;
		$response = wp_remote_post( 'http://fvp.ahoereth.yrnxt.com/fvp_reg.php', array('body' => array( 'fvp_version' => FVP_VERSION, 'wp_version' => get_bloginfo('version'), 'wp_language' => get_bloginfo('language'), 'fvp_notice' => isset($options['notice']) ? $options['notice'] : 'initial_activation' )));
		if( !is_wp_error( $response ) && strlen($response['body']) == 'success.' )
			$options['reged'] = true;
	}

	if( $options != $options_org )
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
	 * Upgrade notification 1.2 to current version
	 *
	 * @since 1.3
	 */
	function upgrade_12() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp'.__('Version %s features more customization settings, internationalization, better error handling and experimental LiveLeak integration.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), FVP_VERSION, '<a href="http://wordpress.org/extend/plugins/featured-video-plus/" target="_blank">', '</a>');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.1 to current version
	 *
	 * @since 1.2
	 */
	function upgrade_11() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp;'.__('Version %s now supports <strong>Local Videos</strong>, LiveLeak integration (experimental), many more customization settings and better error handling.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), FVP_VERSION, '<a href="http://wordpress.org/extend/plugins/featured-video-plus/" traget="_blank">','</a>');
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
		printf(__('Featured Video Plus is <strong>ready to use</strong>. There is a new box on post & page edit screens for you to add videos. <strong>Take a look at your new %sMedia Settings%s</strong>.', 'featured-video-plus'), '<a href="'.get_admin_url(null, '/options-media.php').'" title="Media Settings">', '</a>');
		echo "</p></div>\n";
	}

}
?>