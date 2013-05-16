<?php
/**
 * Is used on plugin upgrade and on first activation. Initializes and upgrades options, places notice etc.
 *
 * @since 1.2
 */
function featured_video_plus_upgrade() {
	$options = $options_org = get_option( 'fvp-settings' );

	if (!isset($options['overwrite'])&&!isset($options['usage']))
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
					'width' 	=> 'auto',
					'height' 	=> 'auto',
					'vimeo' 	=> array(
						'portrait' 	=> 0,
						'title' 	=> 1,
						'byline' 	=> 1,
						'color' 	=> '00adef'
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
						'foreground' 	=> 'F7FFFD',
						'highlight' 	=> 'FFC300',
						'background' 	=> '171D1B',
						'logo' 		=> 1,
						'info' 		=> 1
					);
				$options['sizing'] = array(
						'wmode' 	=> $options['width'],
						'hmode' 	=> $options['height'],
						'width' 	=> 560,
						'height' 	=> 315,
						'align' 	=> 'left'
					);
				unset( $options['width'], $options['height'] );

			case '1.3':
				$notice = isset($notice) ? $notice : 'upgrade_13';

				$options['out'] 						= 0;
				$options['autoplay'] 				= 0;
				$options['youtube']['logo'] = 1;
				$options['dailymotion']['syndication'] = '';

				$options['align'] = $options['sizing']['wmode'] == 'auto' ? 'center' : $options['sizing']['align'];
				unset( $options['sizing']['align'] );

			case '1.4':
				$notice = isset($notice) ? $notice : 'upgrade_14';

				$options['youtube']['wmode'] 				= 'auto';
				$options['local']['videojs']['js'] 	= true;
				$options['local']['videojs']['css'] = true;
				$options['local']['videojs']['cdn'] = false;
				unset($options['videojs']);

				// update video data ('attr' to 'time') and fix serialization
				$ids = $GLOBALS['featured_video_plus']->get_post_by_custom_meta('_fvp_video');
				foreach( $ids as $id ) {
					$meta = maybe_unserialize(get_post_meta( $id, '_fvp_video', true ));
					if( isset( $meta['attr'] ) ) {
						$meta['time'] = $meta['attr'];
						unset($meta['attr']);
						update_post_meta($id, '_fvp_video', $meta);
					}
				}

			case '1.5':
			case '1.5.1':
				$notice = isset($notice) ? $notice : 'upgrade_15';

				$options['youtube']['jsapi'] = 0;
				$options['local']['videojs']['poster'] = false;
				unset($options['reged'], $options['out']);

			case '1.6':
			case '1.6.1':
				$options['usage'] = $options['overwrite'] ? 'replace' : 'manual'; // replace;manual;overlay
				unset($options['overwrite']);

			case '1.7':
			case '1.7.1':
				$options['local']['cdn'] 		 = $options['local']['videojs']['cdn'];
				$options['local']['enabled'] = $options['local']['videojs']['js'];
				$options['local']['poster']  = $options['local']['videojs']['poster'];
				unset($options['local']['videojs']);

				$options['local']['foreground'] = "cccccc";
				$options['local']['highlight']  = "66a8cc";
				$options['local']['background'] = "000000";


		// *************************************************************
		//default:
				$options['version'] = FVP_VERSION;
				break;
		}

		$featured_video_plus_notices = new featured_video_plus_notices();
		if( isset($notice) )
			add_action('admin_notices', array( &$featured_video_plus_notices, $notice ) );
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
	private $pluginpage;

	/**
	 * Initialize class variables.
	 *
	 * @since 1.4
	 */
	function __construct(){
		$this->pluginpage = 'http://wordpress.org/extend/plugins/featured-video-plus#plugin-title';
	}

	/**
	 * Upgrade notification 1.4 to current version
	 *
	 * @since 1.5
	 */
	function upgrade_15() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp'.__('%s brings new options for YouTube and local videos, fixes a whole bunch of glitches and introduces a new PHP-function.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), '<strong>Version&nbsp;'.FVP_VERSION.'</strong>', '<a href="'.$this->pluginpage.'" target="_blank">', '</a>');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.4 to current version
	 *
	 * @since 1.5
	 */
	function upgrade_14() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp'.__('%s <strong>ajax</strong>ifies the Featured Video box, introduces new options for YouTube and local videos and a new PHP-function.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), '<strong>Version&nbsp;'.FVP_VERSION.'</strong>', '<a href="'.$this->pluginpage.'" target="_blank">', '</a>');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.3 to current version
	 *
	 * @since 1.4
	 */
	function upgrade_13() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp'.__('%s features a seamless <strong>WP3.5 Media Manager</strong> integration, an all new <strong>ajax</strong>ified metabox, time-links (#t=4m2s) for YouTube and Dailymotion, new PHP functions for developers and more.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), '<strong>Version&nbsp;'.FVP_VERSION.'</strong>', '<a href="'.$this->pluginpage.'" target="_blank">', '</a>');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.2 to current version
	 *
	 * @since 1.3
	 */
	function upgrade_12() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp'.__('%s features a seamless WP3.5 Media Manager integration, an ajaxified metabox, time-links (#t=4m2s) for YouTube and Dailymotion, more customization settings and internationalization.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), '<strong>Version&nbsp;'.FVP_VERSION.'</strong>', '<a href="'.$this->pluginpage.'" target="_blank">', '</a>');
		echo "</p></div>\n";
	}

	/**
	 * Upgrade notification 1.1 to current version
	 *
	 * @since 1.2
	 */
	function upgrade_11() {
		echo "\n" . '<div class="updated" id="fvp_activation_notification"><p>';
		printf(__('Featured Video Plus was <strong>upgraded</strong>.', 'featured-video-plus').'&nbsp;'.__('%s features support for <strong>Local Videos</strong>, a seamless WP3.5 Media Manager integration, an ajaxified metabox, time-links (#t=4m2s) for YouTube and Dailymotion and many more customization settings.', 'featured-video-plus').'&nbsp;'.__('If you like the plugin, please %srate it%s.', 'featured-video-plus'), '<strong>Version&nbsp;'.FVP_VERSION.'</strong>', '<a href="'.$this->pluginpage.'" traget="_blank">','</a>');
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
