<?php
/**
 * Class containing everything regarding plugin settings on media-settings.php
 *
 * @author ahoereth
 * @version 2013/02/12
 * @see ../featured_video_plus.php
 * @since 1.3
 */
class featured_video_plus_settings {
	private $help_shortcode;
	private $help_functions;

	/**
	 * Initialises the plugin settings section, the settings fields and registers the options field and save function.
	 *
	 * @see http://codex.wordpress.org/Settings_API
	 * @since 1.0
	 */
	function settings_init() {
		add_settings_section('fvp-settings-section', 	__('Featured Videos', 'featured-video-plus'), 				array( &$this, 'settings_content' ), 	'media');

		add_settings_field('fvp-settings-overwrite', 	__('Replace Featured Images', 'featured-video-plus'), 		array( &$this, 'settings_overwrite' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-autoplay', 	__('Autoplay', 'featured-video-plus'), 						array( &$this, 'settings_autoplay' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-sizing', 		__('Video Sizing', 'featured-video-plus'), 					array( &$this, 'settings_sizing' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-align', 		__('Video Align', 'featured-video-plus'), 					array( &$this, 'settings_align' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-local', 		__('Local Video Options', 'featured-video-plus'), 			array( &$this, 'settings_local' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-youtube', 		__('YouTube Options', 'featured-video-plus'), 				array( &$this, 'settings_youtube' ), 	'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-vimeo', 		__('Vimeo Options', 'featured-video-plus'), 				array( &$this, 'settings_vimeo' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-dailymotion', 	__('Dailymotion Options', 'featured-video-plus'), 			array( &$this, 'settings_dailymotion' ),'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-rate', 		__('Support', 'featured-video-plus'), 						array( &$this, 'settings_rate' ), 		'media', 'fvp-settings-section');

		register_setting('media', 'fvp-settings', array( &$this, 'settings_save' ));
	}

	/**
	 * The settings section content. Describes the plugin settings, the php functions and the WordPress shortcode.
	 *
	 * @since 1.0
	 */
	function settings_content() {
		$wrap = get_bloginfo('version') >= 3.3 ? '-wrap' : ''; ?>

<p>
<?php printf(__('To display your featured videos you can either make use of the automatic replacement, use the %s or manually edit your theme\'s source files to make use of the plugins PHP-functions.', 'featured-video-plus'), '<code>[featured-video-plus]</code>-Shortcode'); ?>
<?php printf(__('For more information about Shortcode and PHP functions see the %sContextual Help%s.', 'featured-video-plus'), '<a href="#contextual-help'.$wrap.'" id="fvp_help_toggle">', '</a>'); ?>
</p>

<?php }

	/**
	 * Displays the setting if the plugin should display the featured video in place of featured images.
	 *
	 * @since 1.0
	 */
	function settings_overwrite() {
		$options = get_option( 'fvp-settings' );
		$overwrite = isset($options['overwrite']) ? $options['overwrite'] : false;
?>

<span class="fvp_radio_bumper">
	<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-1" value="true" 	<?php checked( true, 	$overwrite, true ) ?>/><label for="fvp-settings-overwrite-1">&nbsp;<?php _e('yes', 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
</span>
<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-2" value="false" 	<?php checked( false, 	$overwrite, true ) ?>/><label for="fvp-settings-overwrite-2">&nbsp;<?php _e('no', 'featured-video-plus'); ?></label>
<p class="description"><?php _e('If a Featured Video is available it can be displayed in place of the Featured Image. Still, a Featured Image is required.', 'featured-video-plus'); ?></p>

<?php
$class = $overwrite ? 'fvp_warning ' : 'fvp_notice ';
if( !current_theme_supports('post-thumbnails') )
	echo '<p class="'.$class.'description"><span style="font-weight: bold;">'.__('The current theme does not support Featured Images', 'featured-video-plus').':</span>&nbsp;'.__('To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>.', 'featured-video-plus').'</p>'."\n";

}


	/**
	 * Displays the setting if videos should autoplay when a single post/page is being viewed.
	 *
	 * @since 1.4
	 */
	function settings_autoplay() {
		$options 	= get_option( 'fvp-settings' );
		$autoplay 	= isset($options['autoplay']) ? $options['autoplay'] : 0;
?>

<span class="fvp_radio_bumper">
	<input type="radio" name="fvp-settings[autoplay]" id="fvp-settings-autoplay-1" value="true" 	<?php checked( 1, 	$autoplay ) ?>/><label for="fvp-settings-autoplay-1">&nbsp;<?php _e('yes', 'featured-video-plus'); ?></label>
</span>
<input type="radio" name="fvp-settings[autoplay]" id="fvp-settings-autoplay-2" value="false" 	<?php checked( 0, 	$autoplay ) ?>/><label for="fvp-settings-autoplay-2">&nbsp;<?php _e('no', 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
<p class="description"><?php _e('YouTube, Vimeo and Dailymotion videos can autoplay when a single post/page is being viewed.', 'featured-video-plus'); ?></p>

<?php }

	/**
	 * Displays the setting if the plugin should fit the width of the videos automatically or use fixed widths.
	 *
	 * @since 1.3
	 */
	function settings_sizing() {
		$options = get_option( 'fvp-settings' );
		$wmode = isset($options['sizing']['wmode']) && $options['sizing']['wmode'] == 'auto' ? 'auto' : 'fixed';
		$hmode = isset($options['sizing']['hmode']) && $options['sizing']['hmode'] == 'auto' ? 'auto' : 'fixed';
		$width = isset($options['sizing']['width' ]) ? $options['sizing']['width' ] : 560;
		$height= isset($options['sizing']['height']) ? $options['sizing']['height'] : 315;
		$wclass= $wmode == 'auto' ? ' fvp_readonly' : '';
		$hclass= $hmode == 'auto' ? ' fvp_readonly' : ''; ?>

<span class="fvp_toggle_input">
	<label class="fvp_grouplable"><?php _e('Width', 'featured-video-plus'); ?>:</label>
	<span class="fvp_grouppart1">
		<input class="fvp_toggle" type="checkbox" name="fvp-settings[sizing][width][auto]" id="fvp-settings-width-auto" value="auto" <?php checked( 'auto', $wmode, true ) ?>/>
		<label for="fvp-settings-width-auto">&nbsp;auto&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
	</span>
	<input class="fvp_input<?php echo $wclass; ?>" type="text" name="fvp-settings[sizing][width][fixed]" id="fvp-settings-width-fixed" value="<?php echo $width; ?>" size="4" maxlength="4" style="text-align: right; width: 3em;" <?php if('auto'==$wmode) echo 'readonly="readonly"'; ?>/>
	<label for="fvp-settings-width-fixed">&nbsp;px</label>
</span>
<br />
<span class="fvp_toggle_input">
	<label class="fvp_grouplable"><?php _e('Height', 'featured-video-plus'); ?>:</label>
	<span class="fvp_grouppart1">
		<input class="fvp_toggle" type="checkbox" name="fvp-settings[sizing][height][auto]" id="fvp-settings-height-auto" value="auto" <?php checked( 'auto', $hmode, true ) ?>/>
		<label for="fvp-settings-height-auto">&nbsp;auto&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>
	</span>
	<input class="fvp_input<?php echo $hclass; ?>" type="text" name="fvp-settings[sizing][height][fixed]" id="fvp-settings-height-fixed" value="<?php echo $height; ?>" size="4" maxlength="4" style="text-align: right; width: 3em;" <?php if('auto'==$hmode) echo 'readonly="readonly"'; ?>/>
	<label for="fvp-settings-height-fixed">&nbsp;px</label>
</span>
<p class="description">
	<?php _e('When using <code>auto</code> the video will be adjusted to fit it\'s parent element while sticking to it\'s ratio. Using a <code>fixed</code> height and width might result in <em>not so pretty</em> black bars.', 'featured-video-plus'); ?>
</p>

<?php }

	/**
	 * How should the videos be aligned? Only interesting when wmode is set to fixed.
	 * Feature integrated in 1.3, got it own function in 1.4
	 *
	 * @since 1.4
	 */
	function settings_align() {
		$align = isset($options['align']) ? $options['align'] : 'center'; ?>

<input type="radio" name="fvp-settings[align]" id="fvp-settings-align-1" value="left" 	<?php checked( 'left', 	$align, true ) ?>/><label for="fvp-settings-align-1">&nbsp;<?php _e('left', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[align]" id="fvp-settings-align-2" value="center" <?php checked( 'center',$align, true ) ?>/><label for="fvp-settings-align-2">&nbsp;<?php _e('center', 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[align]" id="fvp-settings-align-3" value="right"	<?php checked( 'right', $align, true ) ?>/><label for="fvp-settings-align-3">&nbsp;<?php _e('right', 'featured-video-plus'); ?></label>

<?php }
	/**
	 * Displays the settings for local videos
	 *
	 * @see https://github.com/zencoder/video-js/blob/master/docs/skins.md
	 * @see http://jlofstedt.com/moonify/
	 * @see http://videojs.com/
	 * @since 1.5
	 */
	function settings_local() {
		$options = get_option( 'fvp-settings' );
		$videojs['js'] 	= isset($options['local']['videojs']['js'])  ? $options['local']['videojs']['js']  : true;
		$videojs['css'] = isset($options['local']['videojs']['css']) ? $options['local']['videojs']['css'] : true;
		$videojs['cdn'] = isset($options['local']['videojs']['cdn']) ? $options['local']['videojs']['cdn'] : false;
		$videojs['poster'] = isset($options['local']['videojs']['poster']) ? $options['local']['videojs']['poster'] : false; ?>

VideoJS:&nbsp;
<input type="checkbox" name="fvp-settings[local][videojs][cdn]" id="fvp-settings-local-videojs-cdn" value="true" <?php checked( true, $videojs['cdn'], 	1 ) ?>/><label for="fvp-settings-local-videojs-cdn">&nbsp;<?php _e('Use CDN', 		'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[local][videojs][js]" 	id="fvp-settings-local-videojs-js" 	value="true" <?php checked( true, $videojs['js'], 	1 ) ?>/><label for="fvp-settings-local-videojs-js">&nbsp;<?php  _e('Include JS', 	'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[local][videojs][css]" id="fvp-settings-local-videojs-css" value="true" <?php checked( true, $videojs['css'], 	1 ) ?>/><label for="fvp-settings-local-videojs-css">&nbsp;<?php _e('Include CSS', 	'featured-video-plus'); ?></label><br />
<p class="description"><?php _e('Disabling JS and/or CSS will break the local video player. Disable only when you want to replace VideoJS with a different script and know what you are doing.', 'featured-video-plus'); ?></p>
<input type="checkbox" name="fvp-settings[local][videojs][poster]" id="fvp-settings-local-videojs-poster" value="true" <?php checked( true, $videojs['poster'], 	1 ) ?>/><label for="fvp-settings-local-videojs-poster">&nbsp;<?php _e('Use featured image as video thumbnail', 	'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<?php }

	/**
	 * Displays the settings to style the YouTube video player.
	 *
	 * @see https://developers.google.com/youtube/player_parameters
	 * @since 1.3
	 */
	function settings_youtube() {
		$options = get_option( 'fvp-settings' );
		$youtube['theme'] = isset($options['youtube']['theme']) ? $options['youtube']['theme'] 	: 'dark';
		$youtube['color'] = isset($options['youtube']['color']) ? $options['youtube']['color'] 	: 'red';
		$youtube['wmode'] = isset($options['youtube']['wmode']) ? $options['youtube']['wmode'] 	: 'auto';
		$youtube['jsapi'] = isset($options['youtube']['jsapi'])	? $options['youtube']['jsapi'] 	: 0;
		$youtube['info'] 	= isset($options['youtube']['info']) 	? $options['youtube']['info'] 	: 1;
		$youtube['logo'] 	= isset($options['youtube']['logo']) 	? $options['youtube']['logo'] 	: 1;
		$youtube['rel'] 	= isset($options['youtube']['rel']) 	? $options['youtube']['rel'] 		: 1;
		$youtube['fs'] 		= isset($options['youtube']['fs']) 		? $options['youtube']['fs'] 		: 1; ?>

<input type="checkbox" name="fvp-settings[youtube][theme]" 	id="fvp-settings-youtube-theme" value="light" 	<?php checked( 'light', $youtube['theme'], 	1 ) ?>/><label for="fvp-settings-youtube-theme">&nbsp;<?php _e('Light Theme', 		'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][fs]" 	id="fvp-settings-youtube-fs" 	value="true" 	<?php checked( 1, 		$youtube['fs'], 	1 ) ?>/><label for="fvp-settings-youtube-fs">&nbsp;<?php 	_e('Fullscreen Button', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select name="fvp-settings[youtube][wmode]" id="fvp-settings-youtube-wmode" size="1">
	<option<?php selected($youtube['wmode'],'auto'); 		?>>auto</option>
	<option<?php selected($youtube['wmode'],'transparent'); ?>>transparent</option>
	<option<?php selected($youtube['wmode'],'opaque'); 		?>>opaque</option>
</select>
<label for="fvp-settings-youtube-wmode">&quot;wmode&quot;</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br />
<input type="checkbox" name="fvp-settings[youtube][info]" 	id="fvp-settings-youtube-info" 	value="true" 	 <?php checked( 1, 			$youtube['info'], 1 ) ?>/><label for="fvp-settings-youtube-info">&nbsp;<?php 	_e('Info', 					   'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<input type="checkbox" name="fvp-settings[youtube][rel]" 		id="fvp-settings-youtube-rel" 	value="true" 	 <?php checked( 1, 			$youtube['rel'], 	1 ) ?>/><label for="fvp-settings-youtube-rel">&nbsp;<?php 	_e('Related Videos', 	 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][jsapi]" 	id="fvp-settings-youtube-jsapi" value="true" 	 <?php checked( 1, 			$youtube['jsapi'],1 ) ?>/><label for="fvp-settings-youtube-jsapi">&nbsp;<?php _e('Javascript API', 	 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][color]" 	id="fvp-settings-youtube-color" value="white"  <?php checked( 'white',$youtube['color'],1 ) ?>/><label for="fvp-settings-youtube-color">&nbsp;<?php _e('White Progressbar','featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span id="youtube_logoinput_wrapper"<?php if($youtube['color'] != 'red') echo ' class="fvp_hidden"'; ?>>
	<input type="checkbox" name="fvp-settings[youtube][logo]" 	id="fvp-settings-youtube-logo" 	value="true" 	<?php checked( 1, 		$youtube['logo'], 1 ) ?>/><label for="fvp-settings-youtube-logo">&nbsp;<?php  _e('Logo', 'featured-video-plus'); ?></label>
</span>
<?php
	}

	/**
	 * Displays the settings to style the vimeo video player. Default: &amp;title=1&amp;portrait=0&amp;byline=1&amp;color=00adef
	 *
	 * @see http://developer.vimeo.com/player/embedding
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @since 1.0
	 */
	function settings_vimeo() {
		$options = get_option( 'fvp-settings' );
		$vimeo['portrait'] 	= isset($options['vimeo']['portrait']) 	? $options['vimeo']['portrait'] : 0;
		$vimeo['title' ] 	= isset($options['vimeo']['title' ]) 	? $options['vimeo']['title' ] 	: 1;
		$vimeo['byline'] 	= isset($options['vimeo']['byline']) 	? $options['vimeo']['byline'] 	: 1;
		$vimeo['color' ] 	= isset($options['vimeo']['color' ]) 	? $options['vimeo']['color' ] 	: '00adef'; ?>

<div style="position: relative; bottom: .6em;">
	<input type="checkbox" name="fvp-settings[vimeo][portrait]" id="fvp-settings-vimeo-1" value="display" <?php checked( 1, $vimeo['portrait'], 1 ) ?>/><label for="fvp-settings-vimeo-1">&nbsp;<?php _e('Portrait', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][title]" 	id="fvp-settings-vimeo-2" value="display" <?php checked( 1, $vimeo['title'], 	1 ) ?>/><label for="fvp-settings-vimeo-2">&nbsp;<?php _e('Title', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][byline]" 	id="fvp-settings-vimeo-3" value="display" <?php checked( 1, $vimeo['byline'], 	1 ) ?>/><label for="fvp-settings-vimeo-3">&nbsp;<?php _e('Byline', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[vimeo][color]" id="fvp-settings-vimeo-color" class="fvp_colorpicker_input" value="#<?php echo $vimeo['color'] ?>" data-default-color="#00adef" />
		<label for="fvp-settings-vimeo-color" style="display: none;">&nbsp;<?php _e('Color', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-vimeo-colorpicker"></div><?php } ?>
	</span>
</div>
<p class="description"><?php _e('Vimeo Plus Videos might ignore these settings.', 'featured-video-plus'); ?></p>

<?php
	}

	/**
	 * Displays the settings to style the Dailymotion video player.
	 *
	 * @see https://developers.google.com/youtube/player_parameters
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @since 1.3
	 */
	function settings_dailymotion() {
		$options = get_option( 'fvp-settings' );
		$dailymotion['logo'] 		= isset($options['dailymotion']['logo']) 		? $options['dailymotion']['logo'] 		: 1;
		$dailymotion['info'] 		= isset($options['dailymotion']['info']) 		? $options['dailymotion']['info'] 		: 1;
		$dailymotion['synd'] 		= isset($options['dailymotion']['synd']) 		? $options['dailymotion']['syndication']: '';
		$dailymotion['foreground'] 	= isset($options['dailymotion']['foreground']) 	? $options['dailymotion']['foreground'] : 'f7fffd';
		$dailymotion['highlight' ] 	= isset($options['dailymotion']['highlight' ]) 	? $options['dailymotion']['highlight' ] : 'ffc300';
		$dailymotion['background'] 	= isset($options['dailymotion']['background']) 	? $options['dailymotion']['background'] : '171d1b'; ?>

	<input type="checkbox" 	name="fvp-settings[dailymotion][logo]" id="fvp-settings-dailymotion-logo" value="display" <?php checked( 1, $dailymotion['logo'], 1 ) ?>/>	<label for="fvp-settings-dailymotion-logo">&nbsp;<?php _e('Logo', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" 	name="fvp-settings[dailymotion][info]" id="fvp-settings-dailymotion-info" value="display" <?php checked( 1, $dailymotion['info'], 1 ) ?>/>	<label for="fvp-settings-dailymotion-info">&nbsp;<?php _e('Videoinfo', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="text" 		name="fvp-settings[dailymotion][synd]" id="fvp-settings-dailymotion-synd" value="<?php echo $dailymotion['synd']; ?>" size="6" />	<label for="fvp-settings-dailymotion-synd">&nbsp;<?php _e('Syndication Key', 'featured-video-plus'); ?></label>
	<br />
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][foreground]" id="fvp-settings-dailymotion-foreground" class="fvp_colorpicker_input" value="#<?php echo $dailymotion['foreground'] ?>" data-default-color="#f7fffd" />
		<label for="fvp-settings-dailymotion-foreground" style="display: none;">&nbsp;<?php _e('Foreground', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-foreground-colorpicker"></div><?php } ?>
	</span>
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][highlight]" id="fvp-settings-dailymotion-highlight" class="fvp_colorpicker_input" value="#<?php echo $dailymotion['highlight'] ?>" data-default-color="#ffc300" />
		<label for="fvp-settings-dailymotion-highlight" style="display: none;">&nbsp;<?php _e('Highlight', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-highlight-colorpicker"></div><?php } ?>
	</span>
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[dailymotion][background]" id="fvp-settings-dailymotion-background" class="fvp_colorpicker_input" value="#<?php echo $dailymotion['background'] ?>" data-default-color="#171d1b" />
		<label for="fvp-settings-dailymotion-background" style="display: none;">&nbsp;<?php _e('Background', 'featured-video-plus'); ?></label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-dailymotion-background-colorpicker"></div><?php } ?>
	</span>
	<br />
<?php
	}

	/**
	 * Displays info about rating the plugin, giving feedback and requesting new features
	 *
	 * @since 1.0
	 */
	function settings_rate() {
		$options = get_option( 'fvp-settings' );
		$optout = isset($options['out']) ? $options['out'] : false;
		echo '<p>';
		printf(
			__('If you have found a bug or are missing a specific video service, please %slet me know%s in the support forum. Elsewise, if you like the plugin: Please %srate it!%s', 'featured-video-plus'),
			'<a href="http://wordpress.org/support/plugin/featured-video-plus#plugin-title" 				title="Featured Video Plus Support Forum on WordPress.org" 	target="_blank" style="font-weight: bold;">', '</a>',
			'<a href="http://wordpress.org/support/view/plugin-reviews/featured-video-plus#plugin-title" 	title="Rate Featured Video Plus on WordPress.org" 			target="_blank" style="font-weight: bold;">', '</a>'
		);
		echo '</p>';
	}

	/**
	 * Function through which all settings are passed before they are saved. Validate the data.
	 *
	 * @since 1.0
	 */
	function settings_save($input) {
		$hexcolor = '/#?([0123456789abcdef]{3}[0123456789abcdef]{0,3})/i';
		$numbers = '#[0-9]{1,4}#';
		$options  = get_option( 'fvp-settings' );

		// Overwrite
		$options['overwrite'] 	= isset($input['overwrite']) && $input['overwrite'] == 'true' ? true : false;

		// Sizing
		if(isset($input['sizing']['width' ]['fixed'])) {
			preg_match($numbers, $input['sizing']['width' ]['fixed'], $width );
			$options['sizing']['width' ] = isset($width[ 0]) ? $width[ 0] : 560;
		}
		if(isset($input['sizing']['height' ]['fixed'])) {
			preg_match($numbers, $input['sizing']['height']['fixed'], $height);
			$options['sizing']['height'] = isset($height[0]) ? $height[0] : 315;
		}
		$options['sizing']['wmode' ] = isset($input['sizing']['width' ]['auto'])?  'auto' 			: 'fixed';
		$options['sizing']['hmode' ] = isset($input['sizing']['height' ]['auto'])? 'auto' 			: 'fixed';

		// Align
		$options['align' ] = isset($input['align']) ? $input['align'] : 'center';

		// Autoplay
		$options['autoplay'] = isset($input['autoplay'])  && $input['autoplay'] == 'true' ? 1 : 0;

		// Local
		$options['local']['videojs']['js']  = isset( $input['local']['videojs']['js']  ) ? true : false;
		$options['local']['videojs']['css'] = isset( $input['local']['videojs']['css'] ) ? true : false;
		$options['local']['videojs']['cdn'] = isset( $input['local']['videojs']['cdn'] ) ? true : false;
		$options['local']['videojs']['poster'] = isset( $input['local']['videojs']['poster'] ) ? true : false;

		// YouTube
		$options['youtube']['theme'] 	= isset($input['youtube']['theme']) && ( $input['youtube']['theme'] == 'light') ? 'light' : 'dark';
		$options['youtube']['color'] 	= isset($input['youtube']['color']) && ( $input['youtube']['color'] == 'white') ? 'white' : 'red';
		$options['youtube']['wmode'] 	= isset($input['youtube']['wmode']) ? 	 $input['youtube']['wmode'] :  'auto';
		$options['youtube']['jsapi'] 	= isset($input['youtube']['jsapi']) && ( $input['youtube']['jsapi'] == 'true' ) ? 1 : 0;
		$options['youtube']['info'] 	= isset($input['youtube']['info'])	&& ( $input['youtube']['info'] 	== 'true' ) ? 1 : 0;
		$options['youtube']['logo'] 	= isset($input['youtube']['logo'])	&& ( $input['youtube']['logo'] 	== 'true' ) ? 1 : 0;
		$options['youtube']['rel'] 		= isset($input['youtube']['rel'])		&& ( $input['youtube']['rel'] 	== 'true' ) ? 1 : 0;
		$options['youtube']['fs'] 		= isset($input['youtube']['fs'])		&& ( $input['youtube']['fs'] 	 	== 'true' ) ? 1 : 0;

		// Vimeo
		$options['vimeo']['portrait'] 	= isset($input['vimeo']['portrait'])&& ( $input['vimeo']['portrait'] == 'display' ) ? 1 : 0;
		$options['vimeo']['title'] 		= isset($input['vimeo']['title']) 	&& ( $input['vimeo']['title'] 	 == 'display' ) ? 1 : 0;
		$options['vimeo']['byline'] 	= isset($input['vimeo']['byline']) 	&& ( $input['vimeo']['byline'] 	 == 'display' ) ? 1 : 0;

		if( isset($options['vimeo']['color']) ) preg_match($hexcolor, $input['vimeo']['color'], $vimeocolor);
		$options['vimeo']['color'] = isset($vimeocolor[1]) && !empty($vimeocolor[1]) ? $vimeocolor[1] : '00adef';

		// Dailymotion
		$options['dailymotion']['logo'] = isset($input['dailymotion']['logo']) && ( $input['dailymotion']['logo'] == 'display' ) ? 1 : 0;
		$options['dailymotion']['info'] = isset($input['dailymotion']['info']) && ( $input['dailymotion']['info'] == 'display' ) ? 1 : 0;
		$options['dailymotion']['syndication'] = isset($input['dailymotion']['synd']) && !empty($input['dailymotion']['synd']) ? $input['dailymotion']['synd'] : '';

		if( isset($options['dailymotion']['foreground']) ) preg_match($hexcolor, $input['dailymotion']['foreground'], $dm_foreground);
		if( isset($options['dailymotion']['highlight'])  ) preg_match($hexcolor, $input['dailymotion']['highlight'],  $dm_highlight);
		if( isset($options['dailymotion']['background']) ) preg_match($hexcolor, $input['dailymotion']['background'], $dm_background);
		$options['dailymotion']['foreground'] 	= isset($dm_foreground[1]) && !empty($dm_foreground[1])? $dm_foreground[1] : 'f7fffd';
		$options['dailymotion']['highlight'] 	= isset($dm_highlight[ 1]) && !empty($dm_highlight[ 1])? $dm_highlight[ 1] : 'ffc300';
		$options['dailymotion']['background'] 	= isset($dm_background[1]) && !empty($dm_background[1])? $dm_background[1] : '171d1b';

		return $options;
	}

	/**
	 * Initializes the help texts.
	 *
	 * @since 1.3
	 */
	public function help() {
		$this->help_shortcode = '
<ul>
	<li>
		<code>[featured-video-plus]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video in its default size.', 'featured-video-plus').'</span>
	</li>
	<li>
		<code>[featured-video-plus width=560]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video with an width of 300 pixel. Height will be fitted to the aspect ratio.', 'featured-video-plus').'</span>
	</li>
	<li>
		<code>[featured-video-plus width=560 height=315]</code><br />
		<span style="padding-left: 5px;">'.__('Displays the video with an fixed width and height.', 'featured-video-plus').'</span>
	</li>
</ul>'."\n";

		$this->help_functions ='
<ul class="fvp_code_list">
	<li><code>the_post_video( $size )</code></li>
	<li><code>has_post_video( $post_id )</code></li>
	<li><code>get_the_post_video( $post_id, $size )</code></li>
	<li><code>get_the_post_video_image_url( $post_id )</code></li>
	<li><code>get_the_post_video_image( $post_id )</code></li>
</ul>
<p>
	'.sprintf(__('All parameters are optional. If %s the current post\'s id will be used. %s is either a string keyword (thumbnail, medium, large or full) or a 2-item array representing width and height in pixels, e.g. array(32,32).', 'featured-video-plus'), '<code>post_id == null</code>', '<code>$size</code>').'<br />
</p>
<p style="margin-bottom: 0;">
	'.sprintf(__('The functions are implemented corresponding to the original %sfunctions%s: They are intended to be used and to act the same way. Take a look into the WordPress Codex for further guidance:', 'featured-video-plus'), '<a href="http://codex.wordpress.org/Post_Thumbnails#Function_Reference" target="_blank">'.__('Featured Image').'&nbsp;', '</a>').'
</p>
<ul class="fvp_code_list" style="margin-top: 0;">
	<li><code><a href="http://codex.wordpress.org/Function_Reference/the_post_thumbnail" target="_blank">get_the_post_thumbnail</a></code></li>
	<li><code><a href="http://codex.wordpress.org/Function_Reference/wp_get_attachment_image" target="_blank">wp_get_attachment_image</a></code></li>
</ul>'."\n";
	}

	/**
	 * Adds help tabs to contextual help. WordPress 3.3+
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_help_tab
	 *
	 * @since 1.3
	 */
	public function tabs() {
		$screen = get_current_screen();
		if( ($screen->id != 'options-media') || (get_bloginfo('version') < 3.3) )
			return;

		// PHP FUNCTIONS HELP TAB
		$screen->add_help_tab( array(
			'id' => 'fvp_help_functions',
			'title'   => 'Featured Video Plus: '.__('PHP-Functions','featured-video-plus'),
			'content' => $this->help_functions
		));

		// SHORTCODE HELP TAB
		$screen->add_help_tab( array(
			'id' => 'fvp_help_shortcode',
			'title'   => 'Featured Video Plus: Shortcode',
			'content' => $this->help_shortcode
		));
	}

	/**
	 * Adds help text to contextual help. WordPress 3.3-
	 *
	 * @see http://wordpress.stackexchange.com/a/35164
	 *
	 * @since 1.3
	 */
	public function help_pre_33( $contextual_help, $screen_id, $screen ) {
		if( $screen->id != 'options-media' )
			return $contextual_help;

		$contextual_help .= '<hr /><h3>Featured Video Plus: '.__('PHP-Functions','featured-video-plus').'</h3>';
		$contextual_help .= $this->help_functions;
		$contextual_help .= '<h3>Featured Video Plus: Shortcode</h3>';
		$contextual_help .= $this->help_shortcode;

		return $contextual_help;
	}

}
