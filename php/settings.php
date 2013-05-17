<?php
/**
 * Class containing everything regarding plugin settings on media-settings.php
 *
 * @author ahoereth
 * @version 2013/04/16
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
		add_settings_section('fvp-settings-section', 	__('Featured Videos', 'featured-video-plus'), 			 array( &$this, 'settings_content' ), 	 'media');

		add_settings_field('fvp-settings-usage', 			__('Usage', 'featured-video-plus'), 								 array( &$this, 'settings_usage' 		),   'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-autoplay', 	__('Autoplay', 'featured-video-plus'), 							 array( &$this, 'settings_autoplay' ), 	 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-sizing', 		__('Video Sizing', 'featured-video-plus'), 					 array( &$this, 'settings_sizing' ), 		 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-align', 			__('Video Align', 'featured-video-plus'), 					 array( &$this, 'settings_align' ), 		 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-local', 			__('Local Video Options', 'featured-video-plus'), 	 array( &$this, 'settings_local' ), 		 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-youtube', 		__('YouTube Options', 'featured-video-plus'), 			 array( &$this, 'settings_youtube' ), 	 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-vimeo', 			__('Vimeo Options', 'featured-video-plus'), 				 array( &$this, 'settings_vimeo' ), 		 'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-dailymotion',__('Dailymotion Options', 'featured-video-plus'), 	 array( &$this, 'settings_dailymotion' ),'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-rate', 			__('Support', 'featured-video-plus'), 							 array( &$this, 'settings_rate' ), 			 'media', 'fvp-settings-section');

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
	 * Displays the different usage options of the plugin
	 *
	 * @since 1.7
	 */
	function settings_usage() {
		$options = get_option( 'fvp-settings' );
		$usage = isset($options['usage']) ? $options['usage'] : 'replace';
?>


<input type="radio" name="fvp-settings[usage]" id="fvp-settings-usage-1" value="replace" <?php checked( 'replace', $usage ) ?>/><label for="fvp-settings-usage-1">&nbsp;<?php _e('Replace featured image automatically if possible', 					 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label><br />
<input type="radio" name="fvp-settings[usage]" id="fvp-settings-usage-2" value="overlay" <?php checked( 'overlay', $usage ) ?>/><label for="fvp-settings-usage-2">&nbsp;<?php _e('Open video overlay when featured image is clicked. Define width below!', 'featured-video-plus'); ?></label><br />
<input type="radio" name="fvp-settings[usage]" id="fvp-settings-usage-3" value="dynamic" <?php checked( 'dynamic', $usage ) ?>/><label for="fvp-settings-usage-3">&nbsp;<?php _e('Replace featured image with video on click and auto play if possible','featured-video-plus'); ?></label><br />
<input type="radio" name="fvp-settings[usage]" id="fvp-settings-usage-4" value="manual"	 <?php checked( 'manual',  $usage ) ?>/><label for="fvp-settings-usage-4">&nbsp;<?php _e('None of the above: Manually use PHP-functions or shortcodes','featured-video-plus'); ?></label>
<p class="description"><?php printf(__('The first three options require your theme to make use of WordPress\' %sfeatured image%s capabilities.', 'featured-video-plus'),'<a href="http://codex.wordpress.org/Post_Thumbnails" target="_blank">','</a>'); ?></p>

<?php
if( !current_theme_supports('post-thumbnails') )
	echo '<p class="fvp_warning description"><span style="font-weight: bold;">'.__('The current theme does not support featured images', 'featured-video-plus').':</span>&nbsp;'.__('To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>.', 'featured-video-plus').'</p>'."\n";

}


	/**
	 * Displays the setting if videos should autoplay when a single post/page is being viewed.
	 *
	 * @since 1.4
	 */
	function settings_autoplay() {
		$options 	= get_option( 'fvp-settings' );
		$autoplay = isset($options['autoplay']) ? $options['autoplay'] : 'no';
?>

<input type="radio" name="fvp-settings[autoplay]" id="fvp-settings-autoplay-1" value="yes" <?php checked( 'yes', 	$autoplay ) ?>/>
<label for="fvp-settings-autoplay-1">&nbsp;<?php _e('yes', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[autoplay]" id="fvp-settings-autoplay-2" value="auto" <?php checked( 'auto', 	$autoplay ) ?>/>
<label for="fvp-settings-autoplay-2">&nbsp;<?php _e('auto', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[autoplay]" id="fvp-settings-autoplay-3" value="no" <?php checked( 'no', 	$autoplay ) ?>/>
<label for="fvp-settings-autoplay-2">&nbsp;<?php _e('no', 'featured-video-plus'); ?>&nbsp;<span style="font-style: italic;">(<?php _e('default', 'featured-video-plus'); ?>)</span></label>

<p class="description"><?php printf(__('%1$syes%2$s is only relevant with manual usage or usage set to default. %1$sauto%2$s behaves differently depending on the usage case (set-able above):', 'featured-video-plus'),'<code>','</code>'); ?><br />
<?php _e( 'Autoplay when only a single post is being displayed, the video overlay is opened or the featured image is dynamically replaced with the featured video.', 'featured-video-plus' ); ?></li>
</p>

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
		$local['enabled']  = isset($options['local']['enabled']) ? $options['local']['enabled'] : true;
		$local['cdn']    	 = isset($options['local']['cdn']) 	   ? $options['local']['cdn'] 		: false;
		$local['poster'] 	 = isset($options['local']['poster'])	 ? $options['local']['poster']  : false;
		$local['loop']   	 = isset($options['local']['loop'])	   ? $options['local']['loop']    : false;
		$local['controls'] = isset($options['local']['controls'])? $options['local']['controls']: true;
		$local['foreground']= isset($options['local']['foreground'])? $options['local']['foreground'] : 'cccccc';
		$local['highlight' ]= isset($options['local']['highlight' ])? $options['local']['highlight' ] : '66a8cc';
		$local['background']= isset($options['local']['background'])? $options['local']['background'] : '000000'; ?>

<input type="checkbox" name="fvp-settings[local][enabled]"  id="fvp-settings-local-enabled"  value="true" <?php checked( true, $local['enabled'], 1 ) ?>/><label for="fvp-settings-local-enabled">&nbsp;<?php _e('Enable', 'featured-video-plus'); ?></label>&nbsp;<a href="http://www.videojs.com/" target="_blank">Video.js</a>
<div id="fvp-settings-local-box"<?php if( !$local['enabled'] ) echo ' style="display: none;"'; ?>>
<input type="checkbox" name="fvp-settings[local][cdn]" 			id="fvp-settings-local-cdn" 		 value="true" <?php checked( true, $local['cdn'], 		1 ) ?>/><label for="fvp-settings-local-cdn">&nbsp;<?php _e('Use CDN', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[local][poster]" 	id="fvp-settings-local-poster" 	 value="true" <?php checked( true, $local['poster'],  1 ) ?>/><label for="fvp-settings-local-poster">&nbsp;<?php _e('Use featured image as video thumbnail', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[local][loop]" 		id="fvp-settings-local-loop" 		 value="true" <?php checked( true, $local['loop'], 		1 ) ?>/><label for="fvp-settings-local-loop">&nbsp;<?php _e('Loop videos', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[local][controls]" id="fvp-settings-local-controls" value="true" <?php checked( true, $local['controls'],1 ) ?>/><label for="fvp-settings-local-controls">&nbsp;<?php _e('Show controls', 'featured-video-plus'); ?></label>
<div id="fvp-settings-local-box2"<?php if( !$local['controls'] ) echo ' style="display: none;"'; ?>>
<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
	<input type="text" name="fvp-settings[local][foreground]" id="fvp-settings-local-foreground" class="fvp_colorpicker_input" value="#<?php echo $local['foreground'] ?>" data-default-color="#cccccc" />
	<label for="fvp-settings-local-foreground" style="display: none;">&nbsp;<?php _e('Foreground', 'featured-video-plus'); ?></label>
	<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-local-foreground-colorpicker"></div><?php } ?>
</span>
<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
	<input type="text" name="fvp-settings[local][highlight]" id="fvp-settings-local-highlight" class="fvp_colorpicker_input" value="#<?php echo $local['highlight'] ?>" data-default-color="#66a8cc" />
	<label for="fvp-settings-local-highlight" style="display: none;">&nbsp;<?php _e('Highlight', 'featured-video-plus'); ?></label>
	<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-local-highlight-colorpicker"></div><?php } ?>
</span>
<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
	<input type="text" name="fvp-settings[local][background]" id="fvp-settings-local-background" class="fvp_colorpicker_input" value="#<?php echo $local['background'] ?>" data-default-color="#000000" />
	<label for="fvp-settings-local-background" style="display: none;">&nbsp;<?php _e('Background', 'featured-video-plus'); ?></label>
	<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div class="fvp_colorpicker" id="fvp-settings-local-background-colorpicker"></div><?php } ?>
</span>
</div>
</div>

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

<input type="checkbox" name="fvp-settings[youtube][theme]" id="fvp-settings-youtube-theme" value="light" 	<?php checked( 'light', $youtube['theme'],1 ) ?>/><label for="fvp-settings-youtube-theme">&nbsp;<?php _e('Light Theme', 		'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][fs]" 	 id="fvp-settings-youtube-fs" 	 value="true" 	<?php checked( 1, 			$youtube['fs'], 	1 ) ?>/><label for="fvp-settings-youtube-fs">&nbsp;<?php 	_e('Fullscreen Button', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select name="fvp-settings[youtube][wmode]" id="fvp-settings-youtube-wmode" size="1">
	<option<?php selected($youtube['wmode'],'auto'); 				?>>auto</option>
	<option<?php selected($youtube['wmode'],'transparent'); ?>>transparent</option>
	<option<?php selected($youtube['wmode'],'opaque'); 			?>>opaque</option>
</select>
<label for="fvp-settings-youtube-wmode">&quot;wmode&quot;</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br />
<input type="checkbox" name="fvp-settings[youtube][info]" 	id="fvp-settings-youtube-info" 	value="true" 	<?php checked( 1, 		 $youtube['info'], 1 ) ?>/><label for="fvp-settings-youtube-info">&nbsp;<?php 	_e('Info', 					  'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<input type="checkbox" name="fvp-settings[youtube][rel]" 		id="fvp-settings-youtube-rel" 	value="true" 	<?php checked( 1, 		 $youtube['rel'],  1 ) ?>/><label for="fvp-settings-youtube-rel">&nbsp;<?php 	 _e('Related Videos', 	'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][jsapi]" 	id="fvp-settings-youtube-jsapi" value="true" 	<?php checked( 1, 		 $youtube['jsapi'],1 ) ?>/><label for="fvp-settings-youtube-jsapi">&nbsp;<?php _e('Javascript API', 	'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="fvp-settings[youtube][color]" 	id="fvp-settings-youtube-color" value="white" <?php checked( 'white',$youtube['color'],1 ) ?>/><label for="fvp-settings-youtube-color">&nbsp;<?php _e('White Progressbar','featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<span id="youtube_logoinput_wrapper"<?php if($youtube['color'] != 'red') echo ' class="fvp_hidden"'; ?>>
	<input type="checkbox" name="fvp-settings[youtube][logo]" id="fvp-settings-youtube-logo" 	value="true" 	<?php checked( 1, 		 $youtube['logo'], 1 ) ?>/><label for="fvp-settings-youtube-logo">&nbsp;<?php  _e('Logo', 'featured-video-plus'); ?></label>
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
		$vimeo['portrait']= isset($options['vimeo']['portrait'])? $options['vimeo']['portrait'] : 0;
		$vimeo['title' ] 	= isset($options['vimeo']['title' ]) 	? $options['vimeo']['title' ] 	: 1;
		$vimeo['byline'] 	= isset($options['vimeo']['byline']) 	? $options['vimeo']['byline'] 	: 1;
		$vimeo['color' ] 	= isset($options['vimeo']['color' ]) 	? $options['vimeo']['color' ] 	: '00adef'; ?>

<div style="position: relative; bottom: .6em;">
	<input type="checkbox" name="fvp-settings[vimeo][portrait]" id="fvp-settings-vimeo-1" value="display" <?php checked( 1, $vimeo['portrait'], 1 ) ?>/><label for="fvp-settings-vimeo-1">&nbsp;<?php _e('Portrait', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][title]" 		id="fvp-settings-vimeo-2" value="display" <?php checked( 1, $vimeo['title'], 		1 ) ?>/><label for="fvp-settings-vimeo-2">&nbsp;<?php _e('Title', 'featured-video-plus'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
		$dailymotion['logo'] 			= isset($options['dailymotion']['logo']) 			? $options['dailymotion']['logo'] 			: 1;
		$dailymotion['info'] 			= isset($options['dailymotion']['info']) 			? $options['dailymotion']['info'] 			: 1;
		$dailymotion['synd'] 			= isset($options['dailymotion']['synd']) 			? $options['dailymotion']['syndication']: '';
		$dailymotion['foreground']= isset($options['dailymotion']['foreground'])? $options['dailymotion']['foreground'] : 'f7fffd';
		$dailymotion['highlight' ]= isset($options['dailymotion']['highlight' ])? $options['dailymotion']['highlight' ] : 'ffc300';
		$dailymotion['background']= isset($options['dailymotion']['background'])? $options['dailymotion']['background'] : '171d1b'; ?>

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

		// Usage
		$options['usage'] = isset($input['usage']) ? $input['usage'] : 'replace';

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
		$options['autoplay'] = isset( $input['autoplay'] ) 	? $input['autoplay'] : 'no'; //yes/auto/no

		// Local
		$options['local']['enabled'] 	= isset( $input['local']['enabled'] ) 	? true : false;
		$options['local']['cdn'] 			= isset( $input['local']['cdn'] ) 			? true : false;
		$options['local']['poster']		= isset( $input['local']['poster'] ) 		? true : false;
		$options['local']['controls'] = isset( $input['local']['controls'] ) 	? true : false;
		$options['local']['loop']		  = isset( $input['local']['loop'] ) 			? true : false;

		if( isset($options['local']['foreground']) ) preg_match($hexcolor, $input['local']['foreground'], $local_foreground);
		if( isset($options['local']['highlight'])  ) preg_match($hexcolor, $input['local']['highlight'],  $local_highlight);
		if( isset($options['local']['background']) ) preg_match($hexcolor, $input['local']['background'], $local_background);
		$options['local']['foreground'] = isset($local_foreground[1]) && !empty($local_foreground[1])? $local_foreground[1] : 'cccccc';
		$options['local']['highlight'] 	= isset($local_highlight[ 1]) && !empty($local_highlight[ 1])? $local_highlight[ 1] : '66a8cc';
		$options['local']['background'] = isset($local_background[1]) && !empty($local_background[1])? $local_background[1] : '000000';

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
		$options['vimeo']['portrait'] = isset($input['vimeo']['portrait'])&& ( $input['vimeo']['portrait'] == 'display' ) ? 1 : 0;
		$options['vimeo']['title'] 		= isset($input['vimeo']['title']) 	&& ( $input['vimeo']['title'] 	 == 'display' ) ? 1 : 0;
		$options['vimeo']['byline'] 	= isset($input['vimeo']['byline']) 	&& ( $input['vimeo']['byline'] 	 == 'display' ) ? 1 : 0;

		if( isset($options['vimeo']['color']) ) preg_match($hexcolor, $input['vimeo']['color'], $vimeocolor);
		$options['vimeo']['color'] = isset($vimeocolor[1]) && !empty($vimeocolor[1]) ? $vimeocolor[1] : '00adef';

		// Dailymotion
		$options['dailymotion']['logo'] 			= isset($input['dailymotion']['logo']) && ( $input['dailymotion']['logo'] == 'display' ) ? 1 : 0;
		$options['dailymotion']['info'] 			= isset($input['dailymotion']['info']) && ( $input['dailymotion']['info'] == 'display' ) ? 1 : 0;
		$options['dailymotion']['syndication']= isset($input['dailymotion']['synd']) && !empty($input['dailymotion']['synd']) ? $input['dailymotion']['synd'] : '';

		if( isset($options['dailymotion']['foreground']) ) preg_match($hexcolor, $input['dailymotion']['foreground'], $dm_foreground);
		if( isset($options['dailymotion']['highlight'])  ) preg_match($hexcolor, $input['dailymotion']['highlight'],  $dm_highlight);
		if( isset($options['dailymotion']['background']) ) preg_match($hexcolor, $input['dailymotion']['background'], $dm_background);
		$options['dailymotion']['foreground'] = isset($dm_foreground[1]) && !empty($dm_foreground[1])? $dm_foreground[1] : 'f7fffd';
		$options['dailymotion']['highlight'] 	= isset($dm_highlight[ 1]) && !empty($dm_highlight[ 1])? $dm_highlight[ 1] : 'ffc300';
		$options['dailymotion']['background'] = isset($dm_background[1]) && !empty($dm_background[1])? $dm_background[1] : '171d1b';

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
	<li><code>get_the_post_video_url( $post_id )</code></li>
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
			'id' 			=> 'fvp_help_functions',
			'title'   => 'Featured Video Plus: '.__('PHP-Functions','featured-video-plus'),
			'content' => $this->help_functions
		));

		// SHORTCODE HELP TAB
		$screen->add_help_tab( array(
			'id' 			=> 'fvp_help_shortcode',
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
