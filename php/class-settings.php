<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );

/**
 * Class containing everything regarding the plugin's settings.
 *
 * @since 1.3
 */
class FVP_Settings {
	private static $hook = 'options-media.php';

	private static $section = 'fvp-section';

	private static $page = 'media';

	public function __construct() {
		FVP_HTML::add_screens( self::$hook );

		add_action( 'admin_init',             array( $this, 'settings_init' ) );
		add_action( 'load-options-media.php', array( $this, 'help' ), 20 );
	}


	/**
	 * Initializes the plugin settings section, the settings fields and registers the options field and save function.
	 *
	 * @see http://codex.wordpress.org/Settings_API
	 * @since 1.0
	 */
	public function settings_init() {
		// the featured video settings section on the media settings page
		add_settings_section(
			self::$section,
			esc_html__( 'Featured Videos', 'featured-video-plus' ),
			array( $this, 'section' ),
			self::$page
		);

		// settings fields for auto integration of featured videos - only available
		// in themes with enabled post-thumbnails / featured images
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			add_settings_field(
				'fvp-mode',
				esc_html__( 'Display mode', 'featured-video-plus' ),
				array( $this, 'mode' ),
				self::$page,
				self::$section
			);

			add_settings_field(
				'fvp-conditions',
				esc_html__( 'Display Conditions', 'featured-video-plus' ),
				array( $this, 'conditions' ),
				self::$page,
				self::$section
			);
		}

		// video sizing options
		add_settings_field(
			'fvp-sizing',
			esc_html__( 'Video Sizing', 'featured-video-plus' ),
			array( $this, 'sizing' ),
			self::$page,
			self::$section
		);

		// video align options
		add_settings_field(
			'fvp-align',
			esc_html__( 'Video Align', 'featured-video-plus' ),
			array( $this, 'alignment' ),
			self::$page,
			self::$section
		);

		// video default url argument options
		add_settings_field(
			'fvp-defaults',
			esc_html__( 'Default Arguments', 'featured-video-plus' ),
			array( $this, 'arguments' ),
			self::$page,
			self::$section
		);

		// donation and support notice
		add_settings_field(
			'fvp-message',
			esc_html__( 'Support', 'featured-video-plus' ),
			array( $this, 'message' ),
			self::$page,
			self::$section
		);

		// registering the call to the fvp settings validation handler
		register_setting( 'media', 'fvp-settings', array( $this, 'save' ) );
	}


	/**
	 * The settings section content.
	 * Describes the plugin settings, the PHP functions and the shortcode.
	 *
	 * @since 1.0
	 */
	public function section() {
		echo FVP_HTML::html(
			'p',
			sprintf( esc_html__( 'To display your featured videos you can either make use of the automatic replacement, use the %s or manually edit your theme\'s source files to make use of the plugins PHP-functions.', 'featured-video-plus' ), '<code>[featured-video-plus]</code>-Shortcode' ) .
			sprintf( esc_html__( 'For more information about Shortcode and PHP functions see the %sContextual Help%s.', 'featured-video-plus' ), '<a href="#contextual-help" id="fvp_help_toggle">', '</a>' )
		);

		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			echo FVP_HTML::description(
				FVP_HTML::html(
				 	'span',
					array( 'class' => 'bold' ),
					esc_html__( 'The current theme does not support featured images.', 'featured-video-plus' )
				) .
				esc_html__( 'To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>.', 'featured-video-plus' ),
				array( 'fvp_warning' )
			);
		}
	}


	/**
	 * Displays the different usage options of the plugin
	 *
	 * @since 1.7
	 */
	public function mode() {
		$options = get_option( 'fvp-settings' );

		echo FVP_HTML::radios(
			'fvp-settings[mode]',
			array(
				'replace' => esc_html__( 'Replace featured image automatically.', 'featured-video-plus' ),
				'dynamic' => esc_html__( 'Replace featured image on click.', 'featured-video-plus' ),
				'overlay' => esc_html__( 'Open video overlay when featured image is clicked.', 'featured-video-plus' ),
				'manual'  => esc_html__( 'Manual: PHP-functions or shortcodes.', 'featured-video-plus' ),
			),
			! empty( $options['mode'] ) ? $options['mode'] : 'replace'
		);

		echo FVP_HTML::description(
			sprintf( esc_html__( "Automatic integration (options 1-3) requires your theme to make use of WordPress' native %sfeatured image%s functionality.", 'featured-video-plus' ), '<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">', '</a>' )
		);
	}


	/**
	 * Video replace condition option like is_single or is_home.
	 *
	 * @since  2.0.0
	 */
	public function conditions() {
		$options  = get_option( 'fvp-settings' );

		$auto = ! empty( $options['mode'] ) && $options['mode'] != 'manual';

		echo FVP_HTML::conditional(
			FVP_HTML::description(
				esc_html__( 'View options are not available in manual mode.', 'featured-video-plus' )
			),
			array(
				'fvp-settings[mode]' => 'manual',
				'hidden' => $auto,
			)
		);

		echo FVP_HTML::conditional(
			FVP_HTML::checkboxes(
				'fvp-settings[conditions]',
				array(
					'single' => sprintf(
						esc_html__( 'Only when viewing %ssingle%s posts and pages.', 'featured-video-plus' ),
						'<a href="http://codex.wordpress.org/Function_Reference/is_single" target="_blank">',
						'</a>'
					),
					'home' => sprintf(
						esc_html__( 'Only on the %spost index page%s.', 'featured-video-plus' ),
						'<a href="http://codex.wordpress.org/Function_Reference/is_home" target="_blank">',
						'</a>'
					),
					'main_query' => sprintf(
						esc_html__( 'Only inside the %smain query%s of each page.', 'featured-video-plus' ),
						'<a href="https://developer.wordpress.org/reference/functions/is_main_query/" target="_blank">',
						'</a>'
					),
					'sticky' => sprintf(
						esc_html__( 'Only for %ssticky%s posts.', 'featured-video-plus' ),
						'<a href="http://codex.wordpress.org/Function_Reference/is_sticky" target="_blank">',
						'</a>'
					)
				),
				! empty( $options['conditions'] ) ? $options['conditions'] : array()
			),
			array(
				'fvp-settings[mode]' => '!manual',
				'hidden' => ! $auto,
			)
		);
	}


	/**
	 * Video width and height settings.
	 *
	 * @since 1.3
	 */
	public function sizing() {
		$options = get_option( 'fvp-settings' );
		$responsive =
			! empty( $options['sizing']['responsive'] ) &&
			$options['sizing']['responsive'] &&
			$options['mode'] !== 'overlay';

		echo FVP_HTML::checkbox(
			'fvp-settings[sizing][responsive]',
			esc_html__( 'Responsive', 'featured-video-plus' ),
			'1',
			$responsive
		);

		echo FVP_HTML::conditional(
			FVP_HTML::labeled_input(
				esc_html__( 'Width in pixels:', 'featured-video-plus' ),
				'fvp-settings[sizing][width]',
				array(
					'type' => 'number',
					'size' => 4,
					'value' => ! empty( $options['sizing']['width'] ) ?
						$options['sizing']['width'] : 640,
				)
			),
			array(
				'fvp-settings[sizing][responsive]' => '!1',
				'fvp-settings[mode]' => 'overlay',
				'hidden' => $responsive,
			)
		);

		echo FVP_HTML::description(
			sprintf(
				esc_html__( 'The %1$sresponsive%2$s setting does not work when using the %1$soverlay%2$s display mode and might break completly in some themes - in such cases you should use a fixed width instead.', 'featured-video-plus' ),
				'<code>', '</code>'
			),
			array( 'fvp_warning' )
		);

	}


	/**
	 * How should the videos be aligned?
	 * Only interesting when wmode is set to fixed.
	 *
	 * @since 1.4
	 */
	public function alignment() {
		$options = get_option( 'fvp-settings' );

		echo FVP_HTML::radios(
			'fvp-settings[alignment]',
			array(
				'left'   => esc_html__( 'left', 'featured-video-plus' ),
				'center' => esc_html__( 'center', 'featured-video-plus' ),
				'right'  => esc_html__( 'right', 'featured-video-plus' ),
			),
			! empty( $options['alignment'] ) ? $options['alignment'] : 'center'
		);
	}


	/**
	 * Default settings for video embeds. Can be altered on a per video basis
	 * using video embed URL arguments.
	 *
	 * @since 1.4
	 */
	public function arguments() {
		$options     = get_option( 'fvp-settings' );
		$args        = ! empty( $options['default_args'] ) ? $options['default_args'] : array();
		$vimeo       = ! empty( $args['vimeo'] )       ? $args['vimeo'] : array();
		$youtube     = ! empty( $args['youtube'] )     ? $args['youtube'] : array();
		$dailymotion = ! empty( $args['dailymotion'] ) ? $args['dailymotion'] : array();

		echo FVP_HTML::tabbed( array(
			'general' => array(
				FVP_HTML::checkboxes(
					'fvp-settings[default_args][general]',
					array(
						'autoplay' => esc_html__( 'Autoplay', 'featured-video-plus' ),
						'loop'     => esc_html__( 'Loop', 'featured-video-plus' )
					),
					! empty( $args['general'] ) ? $args['general'] : array()
				),
			),

			'vimeo' => array(
				FVP_HTML::colorpicker(
					'Color',
					'fvp-settings[default_args][vimeo][color]',
					! empty( $vimeo['color'] ) ? $vimeo['color'] : null
				),
				FVP_HTML::checkboxes(
					'fvp-settings[default_args][vimeo]',
					array(
						'portrait' => array(
							'value' => '0',
							'label' => esc_html__( "Hide user's portrait", 'featured-video-plus' )
						),
						'title' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide video title', 'featured-video-plus' )
						),
						'byline' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide video byline', 'featured-video-plus' )
						)
					),
					$vimeo
				),
			),

			'youtube' => array(
				FVP_HTML::checkboxes(
					'fvp-settings[default_args][youtube]',
					array(
						'theme' => array(
							'value' => 'light',
							'label' => esc_html__( 'Light theme', 'featured-video-plus' )
						),
						'modestbranding' => esc_html__( 'Hide YouTube logo', 'featured-video-plus' ),
						'rel' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide related videos', 'featured-video-plus' )
						),
						'fs' => array(
							'value' => '0',
							'label' => esc_html__( 'Disallow fullscreen', 'featured-video-plus' )
						),
						'showinfo' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide video info', 'featured-video-plus' )
						),
						'enablejsapi' => esc_html__( 'Enable JavaScript API', 'featured-video-plus' ),
					),
					$youtube
				),
				FVP_HTML::html(
					'strong',
					'wmode:'
				),
				FVP_HTML::radios(
					'fvp-settings[default_args][youtube][wmode]',
					array(
						'auto' => 'auto',
						'opaque' => 'opaque',
						'transparent' => 'transparent',
					),
					! empty( $youtube['wmode'] ) ? $youtube['wmode'] : null
				),
			),

			'dailymotion' => array(
				FVP_HTML::colorpicker(
					'Foreground color',
					'fvp-settings[default_args][dailymotion][foreground]',
					! empty( $dailymotion['foreground'] ) ? $dailymotion['foreground'] : null
				),
				FVP_HTML::colorpicker(
					'Background color',
					'fvp-settings[default_args][dailymotion][background]',
					! empty( $dailymotion['background'] ) ? $dailymotion['background'] : null
				),
				FVP_HTML::colorpicker(
					'Highlight color',
					'fvp-settings[default_args][dailymotion][highlight]',
					! empty( $dailymotion['highlight'] ) ? $dailymotion['highlight'] : null
				),
				FVP_HTML::labeled_input(
					'Syndication-Key',
					'fvp-settings[default_args][dailymotion][syndication]',
					! empty( $dailymotion['syndication'] ) ? $dailymotion['syndication'] : null
				),
				FVP_HTML::checkboxes(
					'fvp-settings[default_args][dailymotion]',
					array(
						'logo' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide DailyMotion logo', 'featured-video-plus' )
						),
						'info' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide video info', 'featured-video-plus' )
						),
						'related' => array(
							'value' => '0',
							'label' => esc_html__( 'Hide related videos', 'featured-video-plus' )
						),
						'quality' => array(
							'value' => 1080,
							'label' => esc_html__( 'Turn HD on by default', 'featured-video-plus' )
						),
					),
					$dailymotion
				),
			),
		) );
	}


	/**
	 * Message about support forums, rating and donating.
	 *
	 * @since 1.0
	 */
	public function message() {
		echo FVP_HTML::html(
			'p',
			sprintf(
				esc_html__(
					'If you have found a bug or think a specific feature is missing, %slet me know%s in the support forum. Like this plugin? %sRate it%s or %sbuy me a cookie%s!',
					'featured-video-plus'
				),
				'<a href="https://wordpress.org/support/plugin/featured-video-plus#plugin-title" title="Featured Video Plus Support Forum on WordPress.org" target="_blank" style="font-weight: bold;">',
				'</a>',
				'<a href="https://wordpress.org/support/view/plugin-reviews/featured-video-plus#plugin-title" title="Rate Featured Video Plus on WordPress.org" target="_blank" style="font-weight: bold;">',
				'</a>',
				'<a href="https://paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AD8UKMQW2DMM6" title="Gift to the developer!" target="_blank" style="font-weight: bold;">',
				'</a>'
			)
		);
	}


	/**
	 * Function through which all settings are passed before they are saved. Validate the data.
	 *
	 * @since 1.0
	 */
	public function save( $input ) {
		$options = $this->validate( $input );
		return $options;
	}


	private function validate( $src, $tovalidate = null ) {
		$patterns = array(
			'number' => '(\d*)',
			'digit'  => '([0-9])',
			'word'   => '([a-z]*)',
			'string' => '(\w*)',
			'hex'    => '#?([0-9a-f]{3}[0-9a-f]{0,3})',
		);

		$datatypes = array(
			'mode'      => '(replace|dynamic|overlay|manual)',
			'conditions' =>  array(
				'single'     => $patterns['digit'],
				'home'       => $patterns['digit'],
				'main_query' => $patterns['digit'],
				'sticky'     => $patterns['digit'],
			),
			'alignment' => '(left|center|right)',
			'sizing' => array(
				'responsive' => 'BOOLEAN',
				'width'      => $patterns['number'],
			),
			'default_args' => array(
				'general' => array(
					'autoplay' => $patterns['digit'],
					'loop'     => $patterns['digit'],
				),
				'vimeo' => array(
					'portrait' => $patterns['digit'],
					'title'    => $patterns['digit'],
					'byline'   => $patterns['digit'],
					'color'    => $patterns['hex'],
				),
				'youtube' => array(
					'theme'          => $patterns['word'],
					'modestbranding' => $patterns['digit'],
					'fs'             => $patterns['digit'],
					'rel'            => $patterns['digit'],
					'showinfo'       => $patterns['digit'],
					'enablejsapi'    => $patterns['digit'],
					'wmode'          => '(auto|opaque|transparent)',
				),
				'dailymotion' => array(
					'syndication' => $patterns['number'],
					'logo'        => $patterns['digit'],
					'info'        => $patterns['digit'],
					'related'     => $patterns['digit'],
					'quality'     => $patterns['number'],
					'background'  => $patterns['hex'],
					'foreground'  => $patterns['hex'],
					'highlight'   => $patterns['hex'],
				),
			)
		);

		if ( is_null( $tovalidate ) ) {
			$tovalidate = $datatypes;
		}

		$validated = array();

		foreach ( $tovalidate as $key => $value ) {
			if ( ! isset( $src[ $key ] ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$nextleaf = $this->validate( $src[ $key ], $value );
				if ( ! empty( $nextleaf ) ) {
					$validated[ $key ] = $nextleaf;
				}
			} elseif ( 'BOOLEAN' == $value ) {
				$validated[ $key ] = (bool) $src[ $key ];
			} else {
				preg_match( '/' . $value . '/i', $src[ $key ], $match );
				if (
					! empty( $match[1] ) ||
					( isset( $match[1] ) && '0' === $match[1] )
				) {
					$validated[ $key ] = $match[1];
				}
			}
		}

		return $validated;
	}


	/**
	 * Adds help tabs to contextual help. WordPress 3.3+
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_help_tab
	 *
	 * @since 1.3
	 */
	public function help() {
		$screen = get_current_screen();
		if ( 'options-media' !== $screen->id ) {
			return;
		}

		// PHP FUNCTIONS HELP TAB
		$screen->add_help_tab( array(
			'id'      => 'fvp_help_functions',
			'title'   => 'Featured Video Plus: '. esc_html__( 'PHP-Functions','featured-video-plus' ),
			'content' => implode( '', array(
				FVP_HTML::unordered_list( array(
					'<code>the_post_video( $size )</code>',
					'<code>has_post_video( $post_id )</code>',
					'<code>get_the_post_video( $post_id, $size )</code>',
					'<code>get_the_post_video_url( $post_id )</code>',
					'<code>get_the_post_video_image_url( $post_id )</code>',
					'<code>get_the_post_video_image( $post_id )</code>',
				) ),
				FVP_HTML::html(
					'p',
					sprintf(
						esc_html__(
							'All parameters are optional. If %s the current post\'s id will be used. %s is either a string keyword (thumbnail, medium, large or full) or a 2-item array representing width and height in pixels, e.g. array(32,32).',
							'featured-video-plus'
						),
						'<code>post_id == null</code>',
						'<code>$size</code>'
					)
				),
				FVP_HTML::html(
					'p',
					sprintf(
						esc_html__(
							'The functions are implemented corresponding to the original %sfunctions%s: They are intended to be used and to act the same way. Take a look into the WordPress Codex for further guidance:',
							'featured-video-plus'
						),
						'<a href="http://codex.wordpress.org/Post_Thumbnails#Function_Reference" target="_blank">' . esc_html__( 'Featured Image' ) . '&nbsp;',
						'</a>'
					)
				),
				FVP_HTML::unordered_list( array(
					'<code><a href="https://developer.wordpress.org/reference/functions/the_post_thumbnail/" target="_blank">get_the_post_thumbnail</a></code>',
					'<code><a href="https://developer.wordpress.org/reference/functions/wp_get_attachment_image/" target="_blank">wp_get_attachment_image</a></code>',
				) ),
			) )
		) );

		// SHORTCODE HELP TAB
		$screen->add_help_tab( array(
			'id'      => 'fvp_help_shortcode',
			'title'   => 'Featured Video Plus: Shortcode',
			'content' => FVP_HTML::unordered_list( array(
				'<code>[featured-video-plus]</code><br />' .
					'<span>' .
						esc_html__( 'Displays the video in its default size.', 'featured-video-plus' ) .
					'</span>',
				'<code>[featured-video-plus width=560]</code><br />' .
					'<span>' .
						esc_html__( 'Displays the video with an width of 300 pixel. Height will be fitted to the aspect ratio.', 'featured-video-plus' ) .
					'</span>',
				'<code>[featured-video-plus width=560 height=315]</code><br />' .
					'<span>' .
						esc_html__( 'Displays the video with an fixed width and height.', 'featured-video-plus' ) .
					'</span>',
			) )
		) );
	}

}
