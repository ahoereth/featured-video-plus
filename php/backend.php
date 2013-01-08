<?php
/**
 * Class containing functions required WordPress administration panels. Metabox on post/page edit views and options section under settings->media.
 *
 * @author ahoereth
 * @version 2012/12/07
 * @see ../featured_video_plus.php
 * @see featured_video_plus in general.php
 * @since 1.0
 *
 * @param featured_video_plus instance
 */
class featured_video_plus_backend {
	private $featured_video_plus;
	private $default_value;
	private $default_value_sec;

	/**
	 * Creates a new instace of this class, saves the featured_video_instance and default value for the meta box input.
	 *
	 * @since 1.0
	 *
	 * @param featured_video_plus_instance required, dies without
	 */
	function __construct( $featured_video_plus_instance ){
		if ( !isset($featured_video_plus_instance) )
			wp_die( 'featured_video_plus general instance required!', 'Error!' );

		$this->featured_video_plus 	= $featured_video_plus_instance;
		$this->default_value 		= 'YouTube, Vimeo, Dailymotion; Local Media';
		$this->default_value_sec 	= 'Fallback: same video, different format';
	}

	/**
	 * Enqueue all scripts and styles needed when viewing the backend.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://ottopress.com/2010/passing-parameters-from-php-to-javascripts-in-plugins/
	 * @see http://codex.wordpress.org/Function_Reference/wp_localize_script
	 * @since 1.0
	 */
	public function enqueue($hook_suffix) {
		// just required on options-media.php and would produce errors pre WordPress 3.1
		if( ($hook_suffix == 'options-media.php') && (get_bloginfo('version') >= 3.1) ) {
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) {
				// >=WP3.5
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'fvp_backend_35', FVP_URL . '/js/backend_35.js', array( 'wp-color-picker', 'jquery' ) );
			} else {
				// <WP3.5, fallback for the new WordPress Color Picker which was added in 3.5
				wp_enqueue_style( 'farbtastic' );
				wp_enqueue_script( 'farbtastic' );
				wp_enqueue_script( 'fvp_backend_pre35', FVP_URL . '/js/backend_pre35.js', array( 'jquery' ) );
			}
		}

		// just required on post.php
		if($hook_suffix == 'post.php' && isset($_GET['post']) ) {
			wp_enqueue_script( 'jquery.autosize', FVP_URL . '/js/jquery.autosize-min.js', array( 'jquery' ) );
			wp_enqueue_script( 'fvp_backend', FVP_URL . '/js/backend-min.js', array( 'jquery','jquery.autosize' ) ); 	// productive
			//wp_enqueue_script( 'fvp_backend', FVP_URL . '/js/backend.js', array( 'jquery','jquery.autosize' ) ); 		// for development

			$upload_dir = wp_upload_dir();
			wp_localize_script( 'fvp_backend', 'fvp_backend_data', array(
				'wp_upload_dir' 	=> $upload_dir['baseurl'],
				'default_value' 	=> $this->default_value,
				'default_value_sec' => $this->default_value_sec
			) );
		}

		//wp_enqueue_style( 'fvp_backend', FVP_URL . '/css/backend.css' );
		wp_enqueue_style( 'fvp_backend', FVP_URL . '/css/backend-min.css' );
	}

	/**
	 * Registers the metabox on post/page edit views.
	 *
	 * @since 1.0
	 */
	function metabox_register() {
		$post_types = get_post_types(array("public" => true));
		foreach ($post_types as $post_type) {
			if($post_type != 'attachment')
				add_meta_box("featured_video_plus-box", 'Featured Video', array( &$this, 'metabox_content' ), $post_type, 'side', 'core');
		}
	}

	/**
	 * Callback function of the metabox; generates the HTML content.
	 *
	 * @since 1.0
	 */
	function metabox_content() {
		wp_nonce_field( FVP_NAME, 'fvp_nonce');

		if( isset($_GET['post']) )
			$post_id = $_GET['post'];
		else
			$post_id = $GLOBALS['post']->ID;

		// required for conditionals
		$tmp1 = get_post_thumbnail_id($post_id);
		$tmp2 = get_post_meta( $tmp1, '_fvp_image', true);
		$has_featimg = empty($tmp1) ? false : true;
		$featimg_is_fvp = empty($tmp2) ? false : true;
		$has_post_video = $this->featured_video_plus->has_post_video($post_id);

		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );

		echo "\n\n\n<!-- Featured Video Plus Metabox -->\n";
		// displays the current featured video
		if( $has_post_video )
			echo '<div id="featured_video_preview" class="featured_video_plus" style="display:block">' . $this->featured_video_plus->get_the_post_video( $post_id, array(256,144) ) . "</div>\n";

		// input box containing the featured video URL
		$full = isset($meta['full']) ? $meta['full'] : $this->default_value;
		echo '<textarea class="fvp_input" id="fvp_video" name="fvp_video" type="text" title="' . $this->default_value . '" />' . $full . '</textarea>' . "\n";

		$sec = isset($meta['sec']) ? $meta['sec'] : $this->default_value_sec;
		echo '<textarea class="fvp_input" id="fvp_sec" name="fvp_sec" type="text" title="' . $this->default_value_sec . '" />' . $sec . '</textarea>' . "\n";

		// local video format warning
		echo '<div id="fvp_localvideo_format_warning" class="fvp_warning fvp_hidden">'."\n\t".'<p class="description">'."\n\t\t";
		echo '<span style="font-weight: bold;">Supported Video Formats:</span> <code>mp4</code>, <code>webM</code> or <code>ogg/ogv</code>. <a href="http://wordpress.org/extend/plugins/featured-video-plus/faq/">More information</a>.';
		echo "\n\t</p>\n</div>\n";

		// local videos not distinct warning
		echo '<div id="fvp_localvideo_notdistinct_warning" class="fvp_warning fvp_hidden">'."\n\t".'<p class="description">'."\n\t\t";
		echo '<span style="font-weight: bold;">Fallback Video:</span> The two input fields should contain the same video but in distinct formats.';
		echo "\n\t</p>\n</div>\n";

		// how to use a local video notice
		$class = !isset($meta['sec']) || empty($meta['sec']) ? '' : ' fvp_hidden' ;
		echo "<div id=\"fvp_localvideo_notice\" class=\"fvp_notice".$class."\">\n\t<p class=\"description\">\n\t\t";
		echo '<span style="font-weight: bold;">Local Media:</span> Use the <code>Link To Media File</code> from your <a href="#" class="insert-media" title="Add Media">Media Library</a>.';
		echo "\n\t</p>\n</div>\n";

		// no featured image warning
		$fvp_settings = get_option( 'fvp-settings' );
		$class = $has_featimg || !$has_post_video || (isset($fvp_settings['overwrite']) && !$fvp_settings['overwrite']) ? ' fvp_hidden' : '';
		echo '<div id="fvp_featimg_warning" class="fvp_notice'.$class.'">'."\n\t".'<p class="description">';
		echo '<span style="font-weight: bold;">Featured Image:</span> For automatically displaying the Featured Video a Featured Image is required.';
		echo "</p>\n</div>\n";

		// set as featured image
		$class = $meta['prov'] == 'local' || !$has_post_video || ($has_featimg && $featimg_is_fvp) ? ' class="fvp_hidden"' : '';
		$text  = 'Set as Featured Image';
		echo '<p id="fvp_set_featimg_box"'.$class.'>'."\n\t".'<span id="fvp_set_featimg_input">'."\n\t\t".'<input id="fvp_set_featimg" name="fvp_set_featimg" type="checkbox" value="set_featimg" />'."\n\t\t".'<label for="fvp_set_featimg">&nbsp;'.$text.'</label>'."\n\t".'</span>'."\n";
		echo "\t".'<a style="display: none;" id="fvp_set_featimg_link" href="#">'.$text.'</a>'."\n".'</p>'."\n";

		echo "<!-- Featured Video Plus Metabox End-->\n\n\n";
	}

	/**
	 * Saves the changes made in the metabox: Splits URL in its parts, saves provider and id, pulls the screen capture, adds it to the gallery and as featured image.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id
	 */
	public function metabox_save($post_id, $set_featimg = false){

		if (( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 	||	// Autosave, do nothing
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) 			|| 	// AJAX? Not used here
			( !current_user_can( 'edit_post', $post_id ) ) 		|| 	// Check user permissions
			( false !== wp_is_post_revision( $post_id ) ) 		||	// Return if it's a post revision
			( ( isset($_POST['fvp_nonce']) && !wp_verify_nonce( $_POST['fvp_nonce'], FVP_NAME ) ) &&
			  !is_string($set_featimg) )
		   ) return;

		$meta = unserialize( get_post_meta($post_id, '_fvp_video', true) );
		if( is_string($set_featimg) ) {
			$video = $set_featimg;
			$set_featimg = true;
		} else {
			$set_featimg = isset($_POST['fvp_set_featimg']) && !empty($_POST['fvp_set_featimg']) ? true : $set_featimg;

			if( !isset($_POST['fvp_video']) && isset( $meta ) )
				$video = $meta['full'];
			else
				$video = trim($_POST['fvp_video']);
		}

		$sec = isset($_POST['fvp_sec']) && !empty($_POST['fvp_sec']) ? trim($_POST['fvp_sec']) : '';

		// something changed
		if( ( empty($video) ) || 							// no video or
			( isset($meta) && ($video != $meta['full'])) ) 	// different video?
			{

			if(!empty($meta)) { // different video!

				// the current featured image is part of fvp
				$tmp = get_post_meta( get_post_thumbnail_id($post_id), '_fvp_image', true);
				if( !empty( $tmp ) ) {

					// are there other posts with the same featured image?
					$tmp2 = $this->get_post_by_custom_meta( '_thumbnail_id', $meta['img'], $post_id );
					if( !empty( $tmp2 ) ) { // there aren't, so delete it
						$img = $this->get_post_by_custom_meta('_fvp_image', $meta['prov'] . '?' . $meta['id'] );
						wp_delete_attachment( $img );
						delete_post_meta( $img, '_fvp_image', $meta['prov'] . '?' . $meta['id'] );
					}

					delete_post_meta( $post_id, '_thumbnail_id' );
				}

				delete_post_meta( $post_id, '_fvp_video' );
			}

		}

		if( empty($video) )
			return;

		if( ($video == $meta['full']) &&
			(!$set_featimg) &&
			(empty($sec) || ( isset($meta['sec']) && $meta['sec'] == $sec ) ) ) // different secondary video?
			return;

		$options = get_option( 'fvp-settings' );

/*
REGEX tested using: http://www.rubular.com/

Tested URLs:
http://youtu.be/G_Oj7UI0-pw
https://youtu.be/G_Oj7UI0-pw
http://vimeo.com/32071937
https://vimeo.com/32071937
http://vimeo.com/32071937#embed
http://youtu.be/9Tjg6V1Eoz4?t=2m29s
http://www.youtube.com/watch?v=9Tjg6V1Eoz4&t=2m29s
http://www.youtube.com/watch?v=G_Oj7UI0-pw
http://www.youtube.com/watch?feature=blub&v=G_Oj7UI0-pw
*/

		$local = wp_upload_dir();
		// match 		different provider(!)

		preg_match('/(vimeo|youtu|dailymotion|' . preg_quote($local['baseurl'], '/') . ')/i', $video, $video_provider);
		if(!isset($video_provider[1]))
			return;

		$video_prov = $video_provider[1] == "youtu" ? "youtube" : $video_provider[1];

		switch ($video_prov) {

			case $local['baseurl']:
				$ext = pathinfo( $video, PATHINFO_EXTENSION );
				if( !isset($ext) || ($ext != 'mp4' && $ext != 'ogv' && $ext != 'webm' && $ext != 'ogg') ) return; // wrong extension

				$video_id 		= $this->get_post_by_url($video);
				$video_prov 	= 'local';

				if( !empty($sec) ) {
					preg_match('/(' . preg_quote($local['baseurl'], '/') . ')/i', $sec, $sec_prov);
					$ext2 = pathinfo( $sec, PATHINFO_EXTENSION );
					if ( isset($sec_prov[1]) && isset($ext2) && $sec_prov[1] == $video_provider[1] && $ext != $ext2 &&
					   ($ext2 == 'mp4' || $ext2 == 'ogv' || $ext2 == 'webm' || $ext2 == 'ogg'))
						$video_sec_id = $this->get_post_by_url($sec);
					else $sec = ''; // illegal second video, remove it
				}

				break;

			case 'youtube':
				//match			provider				watch		feature							id(!)					attr(!)
				preg_match('/youtu(?:be\.com|\.be)\/(?:watch)?(?:\?feature=[^\?&]*)*(?:[\?&]v=)?([^\?&\s]+)(?:(?:[&\?]t=)(\d+m\d+s))?/', $video, $video_data);
				$video_id = $video_data[1];
				if( isset($video_data[2] ) )
					$video_attr = $video_data[2];

				// title, allow_embed 0/1, keywords, author, iurlmaxres, thumbnail_url, timestamp, avg_rating
				$tmp = download_url( 'http://youtube.com/get_video_info?video_id=' . $video_id );
				$data = file_get_contents($tmp);
				parse_str($data, $data);
				@unlink( $tmp );

				// generate video metadata
				$video_info = array(
					'title' => $data['title'],
					'description' => $data['keywords'],
					'filename' => sanitize_file_name($data['title']),
					'timestamp' => $data['timestamp'],
					'author' => $data['author'],
					'tags' => $data['keywords'],
					'img' => ( isset($data['']) && !empty($data['iurlmaxres']) ) ? $data['iurlmaxres'] : 'http://img.youtube.com/vi/' . $video_id . '/0.jpg'
				);
				break;

			case 'vimeo': // http://developer.vimeo.com/apis/simple
				preg_match('/vimeo.com\/([^#]+)/', $video, $video_data);
				$video_id = $video_data[1];

				// title, description, upload_date, thumbnail_large, user_name, tags
				$url = 'http://vimeo.com/api/v2/video/' . $video_id . '.php';

				if( ini_get('allow_url_fopen') )
					$data = unserialize(file_get_contents( $url ));
				if( !isset( $data ) || empty( $data ) ) {
					$tmp = download_url( $url );
					$data = unserialize(file_get_contents($tmp));
					@unlink( $tmp );
				}

				// generate video metadata
				$video_info = array(
					'title' => $data[0]['title'],
					'description' => $data[0]['description'],
					'filename' => sanitize_file_name( $data[0]['title'] ),
					'timestamp' => strtotime( $data[0]['upload_date'] ),
					'author' => $data[0]['user_name'],
					'tags' => $data[0]['tags'],
					'img' => $data[0]['thumbnail_large'],
					'url' => $data[0]['url']
				);
				break;

			case 'dailymotion': // http://www.dailymotion.com/doc/api/obj-video.html
				preg_match('/dailymotion.com\/video\/([^_]+)/', $video, $video_data);
				$video_id = $video_data[1];

				// http://codex.wordpress.org/HTTP_API
				//thumbnail_url,aspect_ratio,description,created_time,embed_url,owner (owner.screenname),tags,title,url
				$url = 'https://api.dailymotion.com/video/'.$video_id.'?fields=title,description,created_time,owner.screenname,tags,thumbnail_url,thumbnail_large_url,url,aspect_ratio';
				$request = new WP_Http;
				$result = $request->request( $url, array( 'method' => 'GET', 'sslverify' => false) );
				$data = json_decode($result['body'], true);

				// generate video metadata
				$video_info = array(
					'title' => $data['title'],
					'description' => $data['description'],
					'filename' => sanitize_file_name($data['title']),
					'timestamp' => $data['created_time'],
					'author' => $data['owner.screenname'],
					'tags' => $data['tags'],
					'img' => ( isset($data['thumbnail_url']) && !empty($data['thumbnail_url']) ) ? $data['thumbnail_url'] : $data['thumbnail_large_url'],
					'url' => $data['url']
				);
				break;
		}

		if( !isset($video_id) )
			return;

		// do we have a screen capture to pull?
		if( !empty($video_info['img']) ) {

			// is this screen capture already existing in our media library?
			$video_img = $this->get_post_by_custom_meta('_fvp_image', $video_prov . '?' . $video_id);
			if( !isset($video_img) ) {

				// generate attachment post metadata
				$video_img_data = array(
					'post_content' => $video_info['description'],
					'post_title' => $video_info['title'],
					'post_name' => $video_info['filename']
				);

				// pull external img to local server and add to media library
				include_once( FVP_DIR . 'php/somatic_attach_external_image.php' );
				$video_img = somatic_attach_external_image($video_info['img'], $post_id, false, $video_info['filename'], $video_img_data);

				// generate picture metadata
				$video_img_meta = wp_get_attachment_metadata( $video_img );
				$video_img_meta['image_meta'] = array(
					'aperture' => 0,
					'credit' => $video_id,
					'camera' => $video_prov,
					'caption' => $video_info['description'],
					'created_timestamp' => $video_info['timestamp'],
					'copyright' => $video_info['author'],
					'focal_length' => 0,
					'iso' => 0,
					'shutter_speed' => 0,
					'title' => $video_info['title']
				);

				// save picture metadata
				wp_update_attachment_metadata($video_img, $video_img_meta);
				update_post_meta( $video_img, '_fvp_image', $video_prov . '?' . $video_id );
			}

			if( (get_bloginfo('version') >= 3.1) 	&& // set_post_thumbnail was added in 3.1
				( (!has_post_thumbnail( $post_id )) ||
				  ($set_featimg) ) )
				set_post_thumbnail( $post_id, $video_img );

		}

		$meta = array(
			'full' => ( isset($data['url']) && !empty($data['url']) ) ? $data['url'] : $video,
			'id' => $video_id,
			'sec' => $sec,
			'sec_id' => ( isset($video_sec_id) && !empty($video_sec_id) ) ? $video_sec_id : '',
			'img' => isset($video_img) ? $video_img : '',
			'prov' => $video_prov,
			'attr' => isset($video_attr) ? $video_attr : '',
			'warn_featimg' => true
		);

		update_post_meta( $post_id, '_fvp_video', serialize($meta) );

		return;
	}

	/**
	 * Initialises the plugin settings section, the settings fields and registers the options field and save function.
	 *
	 * @see http://codex.wordpress.org/Settings_API
	 * @since 1.0
	 */
	function settings_init() {
		add_settings_section('fvp-settings-section', 	'Featured Video', 			array( &$this, 'settings_content' ), 		'media');

		add_settings_field('fvp-settings-overwrite', 	'Replace featured images', 	array( &$this, 'settings_overwrite' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-width', 		'Video width', 				array( &$this, 'settings_width' ), 			'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-height', 		'Video height', 			array( &$this, 'settings_height' ), 		'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-vimeo', 		'Vimeo Player Design', 		array( &$this, 'settings_vimeo' ), 			'media', 'fvp-settings-section');
		add_settings_field('fvp-settings-rate', 		'Support', 					array( &$this, 'settings_rate' ), 			'media', 'fvp-settings-section');

		register_setting('media', 'fvp-settings', array( &$this, 'settings_save' ));
	}

	/**
	 * The settings section content. Describes the plugin settings, the php functions and the WordPress shortcode.
	 *
	 * @since 1.0
	 */
	function settings_content() { ?>

<p>To display your featured videos you can either make use of the automatical replacement, use the <code>[featured-video]</code>-shortcode or manually edit your theme's source files to make use of the plugins PHP-functions.</p>
<table>
	<tr style="vertical-align: top;">
		<td style="width: 50%;">
			<h4 style="margin-top: 0">WordPress Shortcode Usage</h4>
			<table>
				<tr style="vertical-align: top;">
					<td><code>[featured-video]</code></td>
					<td>Displays the video in its default size, if <code>width</code> below is set to <code>auto</code> 100%, elsewise 560px.</td>
				</tr>
				<tr style="vertical-align: top;">
					<td><code>[featured-video width=300]</code></td>
					<td>Displays the video with an width of 300 pixel. Height will be fitted to the aspect ratio.</td>
				</tr>
			</table>
		</td>
		<td style="width: 50%;">
			<h4 style="margin-top: 0">PHP Function Reference</h4>
			<ul>
				<li><code>the_post_video(array(width, height), allow_fullscreen = true)</code></li>
				<li><code>has_post_video(post_id = null)</code></li>
				<li><code>get_the_post_video(post_id = null, array(width, height), allow_fullscreen = true)</code></li>
			</ul>
			<p class="description">
				All parameters are optional. If <code>post_id == null</code> the current post's id will be used.
				The functions are implemented with their <a href="http://codex.wordpress.org/Post_Thumbnails#Function_Reference" title="Post Thumbnails Function Reference">"featured image"-counterparts</a> in mind, they can be used the same way.
			</p>
		</td>
	</tr>
</table>

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

<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-1" value="true" 	<?php checked( true, $overwrite, true ) ?>/><label for="fvp-settings-overwrite-1">&nbsp;yes&nbsp;<span style="font-style: italic;">(default)</span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[overwrite]" id="fvp-settings-overwrite-2" value="false" 	<?php checked( false, $overwrite, true ) ?>/><label for="fvp-settings-overwrite-2">&nbsp;no</label>
<p class="description">If a featured video is available, it can be displayed in place of the featured image.<br />For some themes this could result in displaying errors. When using this, try different <code>width</code> and <code>height</code> settings.</p>

<?php }

	/**
	 * Displays the setting if the plugin should fit the width of the videos automatically or use fixed widths.
	 *
	 * @since 1.0
	 */
	function settings_width() {
		$options = get_option( 'fvp-settings' );
		$width = isset($options['width']) ? $options['width'] : 'auto'; ?>

<input type="radio" name="fvp-settings[width]" id="fvp-settings-width-1" value="auto" 	<?php checked( 'auto', 	$width, true ) ?>/><label for="fvp-settings-width-1">&nbsp;auto&nbsp;<span style="font-style: italic;">(default)</span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[width]" id="fvp-settings-width-2" value="fixed" 	<?php checked( 'fixed', $width, true ) ?>/><label for="fvp-settings-width-2">&nbsp;fixed</label>
<p class="description">Using <code>auto</code> the video's width will be adjusted to fit the parent element. Works best in combination with height setted to <code>auto</code> as well.</p>

<?php }

	/**
	 * Displays the setting if the plugin should fit the height of the videos automatically to their width/height ratio or use fixed heights, which might result in black bars.
	 *
	 * @since 1.0
	 */
	function settings_height() {
		$options = get_option( 'fvp-settings' );
		$height = isset($options['height']) ? $options['height'] : 'auto'; ?>

<input type="radio" name="fvp-settings[height]" id="fvp-settings-height-1" value="auto" 	<?php checked( 'auto', 	$height, true ) ?>/><label for="fvp-settings-height-1">&nbsp;auto&nbsp;<span style="font-style: italic;">(default)</span></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="fvp-settings[height]" id="fvp-settings-height-2" value="fixed" 	<?php checked( 'fixed', $height, true ) ?>/><label for="fvp-settings-height-2">&nbsp;fixed</label>
<p class="description">If using <code>fixed</code> videos may lose their ascpect radio, resulting in <span style="font-style: italic;">not so pretty</span> black bars.</p>

<?php }

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
		$vimeo['portrait'] 	= isset($options['vimeo']['portrait']) ? $options['vimeo']['portrait'] : 0;
		$vimeo['title'] 	= isset($options['vimeo']['title']) ? $options['vimeo']['title'] : 1;
		$vimeo['byline'] 	= isset($options['vimeo']['byline']) ? $options['vimeo']['byline'] : 1;
		$vimeo['color'] 	= isset($options['vimeo']['color']) ? $options['vimeo']['color'] : '00adef'; ?>

<div style="position: relative; bottom: .6em;">
	<input type="checkbox" name="fvp-settings[vimeo][portrait]" id="fvp-settings-vimeo-1" value="display" <?php checked( 1, $vimeo['portrait'], 1 ) ?>/><label for="fvp-settings-vimeo-1">&nbsp;Portrait</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][title]" 	id="fvp-settings-vimeo-2" value="display" <?php checked( 1, $vimeo['title'], 	1 ) ?>/><label for="fvp-settings-vimeo-2">&nbsp;Title</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="fvp-settings[vimeo][byline]" 	id="fvp-settings-vimeo-3" value="display" <?php checked( 1, $vimeo['byline'], 	1 ) ?>/><label for="fvp-settings-vimeo-3">&nbsp;Byline</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<span class="color-picker" style="position: relative;<?php if( wp_style_is( 'wp-color-picker', 'done' ) ) echo ' top: .6em;'; ?>" >
		<input type="text" name="fvp-settings[vimeo][color]" id="fvp-settings-vimeo-color" value="#<?php echo $vimeo['color'] ?>" data-default-color="#00adef" />
		<label for="fvp-settings-vimeo-color" style="display: none;">&nbsp;Color</label>
		<?php if( !wp_style_is('wp-color-picker', 'registered' ) ) { ?><div style="position: absolute; bottom: 0; right: -197px; background-color: #fff; z-index: 100; border: 1px solid #ccc;" id="fvp-settings-vimeo-colorpicker"></div><?php } ?>
	</span>
</div>
<p class="description">These settings could be overwritten by videos from Vimeo Plus members.</p>

<?php
	}

	/**
	 * Displays info about rating the plugin, giving feedback and requesting new features
	 *
	 * @since 1.0
	 */
	function settings_rate() { ?>

<p>
	Found a bug or <span style="font-weight: bold;">missing a specific video service</span>? <a href="http://wordpress.org/extend/plugins/featured-video/" title="Featured Video Plus Support Forum on wordpress.org" style="font-weight: bold;">Leave a note</a> in the plugins support forum!<br />
	No? Than please <a href="http://wordpress.org/extend/plugins/featured-video/" title="Featured Video Plus on wordpress.org" style="font-weight: bold;">rate it</a>.<br />
</p>

<?php }

	/**
	 * Function through which all settings are passed before they are saved. Validate the data.
	 *
	 * @since 1.0
	 */
	function settings_save($input) {
		$options = get_option( 'fvp-settings' );

		$options['overwrite'] 	= $input['overwrite'] 	== 'true' ? true : false;

		$options['vimeo']['portrait'] = isset($input['vimeo']['portrait'])&& ( $input['vimeo']['portrait'] == 'display' ) ? 1 : 0;
		$options['vimeo']['title'] 	= isset($input['vimeo']['title']) 	&& ( $input['vimeo']['title'] 	 == 'display' ) ? 1 : 0;
		$options['vimeo']['byline'] 	= isset($input['vimeo']['byline']) 	&& ( $input['vimeo']['byline'] 	 == 'display' ) ? 1 : 0;

		if( isset($options['vimeo']['color']) ) {
			preg_match('/#?([0123456789abcdef]{3}[0123456789abcdef]{0,3})/i', $input['vimeo']['color'], $color);
			$options['vimeo']['color'] = $color[1];
		} else
			$options['vimeo']['color'] = '00adef';


		return $options;
	}

	/**
	 * Function to allow more upload mime types.
	 *
	 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
	 * @since 1.2
	 */

	function add_upload_mimes( $mimes=array() ) {
		$mimes['webm'] = 'video/webm';

		return $mimes;
	}

	/**
	 * Adds a media settings link to the plugin info
	 *
	 * @since 1.2
	 */
	function plugin_action_link($links, $file) {
		if ($file == FVP_NAME . '/' . FVP_NAME . '.php') {
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-media.php">Media Settings</a>';
			array_unshift($links, $settings_link);
		}

		return $links;
	}

	/**
	 * Gets a post by an meta_key meta_value pair. Returns it's post_id.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @see http://dev.mysql.com/doc/refman/5.0/en/regexp.html#operator_regexp
	 * @since 1.0
	 *
	 * @param string $meta_key which meta_key to look for
	 * @param string $meta_value which meta_value to look for
	 */
	function get_post_by_custom_meta($meta_key, $meta_value, $notThisId = 0) {
		global $wpdb;
		if( $notThisId > 0 )
			$prepared = $wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s AND post_id!=%d;",
							$meta_key, $meta_value, $notThisId
						);
		else
			$prepared = $wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s;",
							$meta_key, $meta_value
						);

		return $wpdb->get_var( $prepared );
	}

	/**
	 * Gets post id by it's url / guid.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @since 1.0
	 *
	 * @param string $url which url to look for
	 */
	function get_post_by_url($url) {
		global $wpdb;
		$id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE guid=%s;",
					$url
				)
			);
		return $id;
	}
}
?>