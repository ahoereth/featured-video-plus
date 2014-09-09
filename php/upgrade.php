<?php
if ( empty( $version ) || empty( $options ) || empty( $options_org ) ) {
	exit('Featured Video Plus Error:
		Upgrade can not be executed directly!
		Must be called through FVP_Backend->upgrade().');
}

switch ( $version ) {
	case '1.0':
	case '1.1':
		$users = array_merge(
			get_users( array( 'role' => 'Administrator' ) ),
			get_users( array( 'role' => 'Super Admin'   ) )
		);

		foreach( $users as $user ) {
			delete_user_meta( $user->ID, 'fvp_activation_notification_ignore' );
		}


	case '1.2':
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
			'foreground'  => 'F7FFFD',
			'highlight'   => 'FFC300',
			'background'  => '171D1B',
			'logo'        => 1,
			'info'        => 1
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
		$options['out']                        = 0;
		$options['autoplay']                   = 0;
		$options['youtube']['logo']            = 1;
		$options['dailymotion']['syndication'] = '';
		$options['align'] = $options['sizing']['wmode'] == 'auto' ? 'center' : $options['sizing']['align'];
		unset( $options['sizing']['align'] );


	case '1.4':
		$options['youtube']['wmode']        = 'auto';
		$options['local']['videojs']['js']  = true;
		$options['local']['videojs']['css'] = true;
		$options['local']['videojs']['cdn'] = false;
		unset( $options['videojs'] );


	case '1.5':
	case '1.5.1':
		$options['youtube']['jsapi']           = 0;
		$options['local']['videojs']['poster'] = false;
		unset( $options['reged'], $options['out'] );


	case '1.6':
	case '1.6.1':
		$options['usage'] = $options['overwrite'] ? 'replace' : 'manual'; // replace;manual;overlay
		unset( $options['overwrite'] );


	case '1.7':
	case '1.7.1':
		$options['local']['cdn']     = $options['local']['videojs']['cdn'];
		$options['local']['enabled'] = $options['local']['videojs']['js'];
		$options['local']['poster']  = $options['local']['videojs']['poster'];
		unset( $options['local']['videojs'] );

		$options['local']['foreground'] = "cccccc";
		$options['local']['highlight']  = "66a8cc";
		$options['local']['background'] = "000000";
		$options['local']['controls']   = true;
		$options['local']['loop']       = false;
		$options['autoplay'] = $options['autoplay'] ? 'yes' : 'no'; //yes/auto/no


	case '1.8':
		unset(
			$options['local']['cdn'],
			$options['local']['enabled'],
			$options['local']['foreground'],
			$options['local']['background'],
			$options['local']['controls']
		);

		// check all featured video post metas
		global $featured_video_plus;
		$ids = $featured_video_plus->get_post_by_custom_meta( '_fvp_video' );
		foreach ( $ids as $id ) {
			$meta = $meta_old = maybe_unserialize( get_post_meta( $id, '_fvp_video', true ) );
			// update video data ('attr' to 'time') and fix serialization, was in case '1.4'
			if ( isset( $meta['attr'] ) ) {
				$meta['time'] = $meta['attr'];
				unset( $meta['attr'] );
			}
			// remove 'sec_id', only one local video file is used now.
			if ( isset( $meta['sec_id'] ) ) {
				unset( $meta['sec_id'] );
			}
			if ( $meta != $meta_old ) {
				update_post_meta( $id, '_fvp_video', $meta );
			}
		}


	case '1.9':
		$options['issingle'] = false;


	case '1.9.1':
		$options['ishome'] = false;

		// remove all previous defaults
		$options['default_args'] = array(
			'general' => array_diff_assoc(
				array(
					'autoplay' => $options['autoplay'] == 'true' ? true : false,
					'loop'     => $options['local']['loop'],
				),
				array(
					'autoplay' => false,
					'loop'     => false,
				)
			),
			'vimeo' => array_diff_assoc(
				$options['vimeo'],
				array(
					'portrait' => 0,
					'title'    => 1,
					'byline'   => 1,
					'color'    => '00adef',
				)
			),
			'youtube' => array_diff_assoc(
				$options['youtube'],
				array(
					'theme' => 'dark',
					'color' => 'red',
					'info'  => 1,
					'rel'   => 1,
					'fs'    => 1,
					'logo'  => 1,
					'wmode' => 'auto',
					'jsapi' => 0,
				)
			),
			'dailymotion' => array_diff_assoc(
				$options['dailymotion'],
				array(
					'foreground'  => 'F7FFFD',
					'highlight'   => 'FFC300',
					'background'  => '171D1B',
					'logo'        => 1,
					'info'        => 1,
					'syndication' => '',
				)
			),
		);
		unset(
			$options['autoplay'],
			$options['vimeo'],
			$options['youtube'],
			$options['dailymotion'],
			$options['local']
		);

	default:
		$options['version'] = FVP_VERSION;
		update_option( 'fvp-settings', $options );
		break;
}
