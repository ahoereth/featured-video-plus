<?php
/**
 * Class containing functions required WordPress administration panels. Metabox on post/page edit views and options section under settings->media.
 *
 * @author ahoereth
 * @see ../featured_video_plus.php
 * @see featured_video_plus in general.php
 * @since 1.0
 *
 * @param featured_video_plus instance
 */
class featured_video_plus_backend {
	private $super;
	private $help_localmedia;
	private $help_urls;


	/**
	 * Creates a new instance of this class, saves the featured_video_instance and default value for the meta box input.
	 *
	 * @since 1.0
	 *
	 * @param featured_video_plus_instance required, dies without
	 */
	function __construct( $featured_video_plus_instance ){
		if ( !isset($featured_video_plus_instance) )
			wp_die( 'featured_video_plus general instance required!', 'Error!' );

		$this->super = $featured_video_plus_instance;
	}


	/**
	 * Enqueue all scripts and styles needed when viewing the backend.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_style_is
	 * @see http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/
	 * @see http://ottopress.com/2010/passing-parameters-from-php-to-javascripts-in-plugins/
	 * @see http://codex.wordpress.org/Function_Reference/wp_localize_script
	 *
	 * @since 1.0
	 */
	public function enqueue($hook_suffix) {
		$min = SCRIPT_DEBUG ? '' : '.min';

		// jQuery script for automatically resizing <textarea>s
		wp_register_script(
			'jquery.autosize',
			FVP_URL . "js/jquery.autosize$min.js",
			array( 'jquery' ),
			FVP_VERSION,
			false
		);

		// general backend style
		wp_register_style(
			'fvp-backend',
			FVP_URL . "css/backend$min.css",
			array(),
			FVP_VERSION,
			'all'
		);

		// Settings -> Media screen: options-media.php
		if ( $hook_suffix == 'options-media.php' ) {

			// script handling color pickers and dynamically hiding/showing options
			wp_enqueue_script(
				'fvp-settings',
				FVP_URL . 'js/settings.js',
				array(
					'jquery',
					'iris'
				),
				FVP_VERSION
			);

			// color picker
			wp_enqueue_style( 'wp-color-picker' );

			// see style registration above
			wp_enqueue_style( 'fvp-backend' );

		}

		// Edit & new post screen: post.php?post=%d & post-new.php
		if( ( $hook_suffix == 'post.php' && isset( $_GET['post'] ) ) || $hook_suffix == 'post-new.php' ) {

			// script handling featured video form interactions with ajax requests etc
			wp_enqueue_script(
				'fvp-post',
				FVP_URL . "js/post$min.js",
				array(
					'jquery',
					'jquery.autosize'
				),
				FVP_VERSION
			);

			// some variables required in JS context
			$upload_dir = wp_upload_dir();
			wp_localize_script( 'fvp-post', 'fvp_post', array(
				'wp_upload_dir'     => $upload_dir['baseurl'],
				'loading_gif'       => get_admin_url( null, 'images/loading.gif' )
			));

			// HTML5 video handling
			wp_enqueue_script( 'wp-mediaelement' );
			wp_enqueue_style( 'wp-mediaelement' );

			// see style registration above
			wp_enqueue_style( 'fvp-backend' );

		}

	}


	/**
	 * Registers the metabox on post/page edit views.
	 *
	 * @since 1.0
	 */
	function metabox_register() {
		$post_types = get_post_types( array( "public" => true ) );

		// cycle through all post types
		foreach ( $post_types as $post_type ) {

			// ignore attachment post type
			if( $post_type == 'attachment' )
				continue;

			// register metabox
			add_meta_box(
				"featured_video_plus-box",
				__('Featured Video', 'featured-video-plus'),
				array( &$this, 'metabox_content' ),
				$post_type,
				'side',
				'core'
			);
		}
	}


	/**
	 * Callback function of the metabox; generates the HTML content.
	 *
	 * @since 1.0
	 */
	function metabox_content() {
		wp_nonce_field( FVP_NAME, 'fvp_nonce');

		// get current post's id
		if( isset( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		} else {
			$post_id = $GLOBALS['post']->ID;
		}

		// required for conditionals
		$post_thumbnail_id   = get_post_thumbnail_id($post_id);
		$post_thumbnail_meta = get_post_meta( $post_thumbnail_id, '_fvp_image', true);

		$has_post_image = ! empty( $post_thumbnail_id )   ? true : false;
		$featimg_is_fvp = ! empty( $post_thumbnail_meta ) ? true : false;
		$has_post_video = has_post_video( $post_id );

		$options = get_option( 'fvp-settings' );
		$meta    = get_post_meta( $post_id, '_fvp_video', true );

		$content = '';

		// current featured video
		$hide     = $has_post_video ? '' : ' style="height:0px;"';
		$video    = $has_post_video ? get_the_post_video( $post_id, array( 256, 144 ) ) : '';
		$content .= "<div id='fvp_current_video'{$hide}>{$video}</div>\n\n";

		// input box containing the featured video URL including functionality for
		// the media chooser
		$valid = ! empty( $meta['valid'] ) && !$meta['valid'] && ! empty( $meta['full'] ) ? ' fvp_invalid' : '';
		$full  = $has_post_video ? get_the_post_video_url( $post_id ) : '';
		$content .= '<div class="fvp_input_wrapper" data-title="'.__('Set Featured Video', 'featured-video-plus').'" data-button="'.__('Set featured video', 'featured-video-plus').'" data-target="#fvp_video">'."\n\t";
		$content .= "<textarea class='fvp_input{$valid}'' id='fvp_video' name='fvp_video' type='text' placeholder='".__('Video URL', 'featured-video-plus')."'>{$full}</textarea>\n\t";
		$content .= "<input type='hidden' class='fvp_mirror' value='{$full}' />\n\t";
		$content .= '<a href="#" class="fvp_video_choose"><span class="fvp_media_icon" style="background-image: url(\''.get_bloginfo('wpurl').'/wp-admin/images/media-button.png\');"></span></a>'."\n";
		$content .= "</div>\n";

		// local video format warning
		$content .= '<div id="fvp_localvideo_format_warning" class="fvp_warning fvp_hidden"><p class="description">';
		$content .= '<span style="font-weight: bold;">'.__('Supported Video Formats', 'featured-video-plus').':</span> <code>mp4</code>, <code>webM</code>, <code>m4v</code>, <code>wmv</code>, <code>flv</code> '.__('or', 'featured-video-plus').' <code>ogv</code>. <a href="http://mediaelementjs.com/#devices">'.__('More information', 'featured-video-plus').'</a>.';
		$content .= "</p></div>\n";

		// no featured image warning
		$class = $has_post_image || !$has_post_video || ( isset( $options['usage'] ) && $options['usage'] == 'manual' ) ? ' fvp_hidden' : '';
		$content .= "<div id='fvp_featimg_warning' class='fvp_notice{$class}'><p class='description'>";
		$content .= '<span style="font-weight: bold;">'.__('Featured Image').':</span>&nbsp;'.__('For automatically displaying the Featured Video a Featured Image is required.', 'featured-video-plus');
		$content .= "</p></div>\n";

		// set as featured image
		$class = isset($meta['prov']) && $meta['prov'] == 'local' || !$has_post_video || ($has_post_image && $featimg_is_fvp) ? ' class="fvp_hidden"' : '';
		$content .= sprintf('<p id="fvp_set_featimg_box"'.$class.'>'."\n\t".'<span id="fvp_set_featimg_input">'."\n\t\t".'<input id="fvp_set_featimg" name="fvp_set_featimg" type="checkbox" value="set_featimg" />'."\n\t\t".'<label for="fvp_set_featimg">&nbsp;%s</label>'."\n\t".'</span>'."\n\t".'<a class="fvp_hidden" id="fvp_set_featimg_link" href="#">%s</a>'."\n".'</p>'."\n", __('Set as Featured Image', 'featured-video-plus'), __('Set as Featured Image', 'featured-video-plus') );

		// current theme does not support Featured Images warning
		if( !current_theme_supports('post-thumbnails') && $options['usage'] != 'manual' ) {
			$content .= '<p class="fvp_warning description"><span style="font-weight: bold;">'.__('The current theme does not support Featured Images', 'featured-video-plus').':</span>&nbsp;'.sprintf(__('To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>. To hide this notice deactivate &quot;<em>Replace Featured Images</em>&quot; in the %sMedia Settings%s.', 'featured-video-plus'), '<a href="'.get_admin_url(null, '/options-media.php').'">', '</a>' )."</p>\n\n";
		}

		echo "\n\n\n<!-- Featured Video Plus Metabox -->\n";
		echo $content;
		echo "<!-- Featured Video Plus Metabox End-->\n\n\n";
	}


	/**
	 * Saves the changes made in the metabox: Splits URL in its parts, saves provider and id, pulls the screen capture, adds it to the gallery and as featured image.
	 *
	 * @since 1.0
	 *
	 * @param int $post_id
	 */
	public function metabox_save($post_id){

		if (( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || // Autosave, do nothing
		    ( defined( 'DOING_AJAX' )     && DOING_AJAX )     || // AJAX?
		    ( ! current_user_can( 'edit_post', $post_id ) )   || // Check user permissions
		    ( false !== wp_is_post_revision( $post_id ) )        // Return if it's a post revision
		   ) return;

		$post = array(
			'id'              => $post_id,
			'fvp_nonce'       => ! empty( $_POST['fvp_nonce'] )       ? $_POST['fvp_nonce']       : '',
			'fvp_set_featimg' => ! empty( $_POST['fvp_set_featimg'] ) ? $_POST['fvp_set_featimg'] : '',
			'fvp_video'       => ! empty( $_POST['fvp_video'] )       ? $_POST['fvp_video']       : '',
			'fvp_sec'         => ! empty( $_POST['fvp_sec'] )         ? $_POST['fvp_sec']         : ''
		);

		$this->save($post);

		return;
	}

	/**
	 * Forwards ajax save requests to the $this->save function and generates a response.
	 *
	 * @since 1.5
	 */
	public function ajax() {
		$post = array(
			'id'              => $_POST['id'],
			'fvp_nonce'       => ! empty( $_POST['fvp_nonce'] )       ? $_POST['fvp_nonce']       : '',
			'fvp_set_featimg' => ! empty( $_POST['fvp_set_featimg'] ) ? $_POST['fvp_set_featimg'] : '',
			'fvp_video'       => ! empty( $_POST['fvp_video'] )       ? $_POST['fvp_video']       : '',
			'fvp_sec'         => ! empty( $_POST['fvp_sec'] )         ? $_POST['fvp_sec']         : ''
		);

		// this also verifies the nonce
		$meta = $this->save( $post );

		$img = _wp_post_thumbnail_html( get_post_thumbnail_id( $post['id'] ), $post['id'] );

		if (has_post_video($post['id'])){
			$video = get_the_post_video( $post['id'], array( 256, 144 ) );
			$response = json_encode(array(
				'type'  => 'update',
				'valid' => $meta['valid'],
				'video' => $video,
				'img'   => $img,
				'prov'  => $meta['provider']
			));
		} else {
			$response = json_encode(array(
				'task'  => 'remove',
				'valid' => $meta['valid'],
				'img'   => $img
			));
		}

		echo $response;
		die();
	}


	/**
	 * Used for processing a save request.
	 *
	 * @since 1.5
	 *
	 * @see http://codex.wordpress.org/Function_Reference/update_post_meta
	 */
	function save( $post ) {
		if( ( isset( $post['fvp_nonce'] ) && ! wp_verify_nonce( $post['fvp_nonce'], FVP_NAME ) ) )
			return false;

		// get fvp_video post meta data
		$meta = get_post_meta( $post['id'], '_fvp_video', true );

		// parse video url
		$url = ! empty( $post['fvp_video'] ) ? trim( $post['fvp_video'] ) : '';

		// url did not change or is and was empty
		if ( ( ! empty( $meta['full'] ) && $url == $meta['full'] ) || ( empty( $meta['full'] ) && empty( $url ) ) ) {
			return false;
		}

		// there was a video and we want to delete it
		if( ! empty( $meta['full'] ) && empty( $url ) ) {
			delete_post_meta( $post['id'], '_fvp_video' );
			$this->delete_featured_video_image( $post['id'], $meta );
			return false;
		}

		$data = $this->get_video_data( $url );

		// Do we have a screen capture to pull?
		if( ! empty( $data['img_url'] ) ) {
			$img = $this->set_featured_video_image( $post['id'], $data );
		}

		$meta = array_merge(
			array(
				'full'  => $url,
				'img'   => ! empty( $img ) ? $img : null,
				'valid' => true // can be overwritten by $data
			),
			$data
		);

		update_post_meta( $post['id'], '_fvp_video', $meta );
		return $meta;
	}


	/**
	 * Returns an array containing video information like id provider imgurl etc
	 * Code existing since 1.0, got it own function in 1.5
	 *
	 * @see   http://oembed.com/
	 * @since 1.5
	 *
	 * @param string video a video url
	 */
	function get_video_data( $url ) {
		$data = array();

		$local = wp_upload_dir();
		preg_match('/'.preg_quote($local['baseurl'], '/').'/i', $url, $prov_data);

		// handle local videos
		if( isset($prov_data[1]) ) {
			$provider = 'local';
		} else {
			$raw = $this->oembed_fetch($url);
			$provider = strtolower( $raw->provider_name );

			// If no provider is returned the URL is invalid
			if ( empty($provider) ) {
				return array( 'valid' => false );
			}

			$data = array(
				'id'          => null,
				'provider'    => $provider,
				'title'       => ! empty( $raw->title )         ? $raw->title                       : null,
				'author'      => ! empty( $raw->author_name )   ? $raw->author_name                 : null,
				'description' => ! empty( $raw->description )   ? $raw->description                 : null,
				'img_url'     => ! empty( $raw->thumbnail_url ) ? $raw->thumbnail_url               : null,
				'filename'    => ! empty( $raw->title )         ? sanitize_file_name( $raw->title ) : null,
			);
		}

		/*print_r($raw);
		print_r($data);
		die();*/

		$parameters = $this->parse_query($url);

		// provider specific handling
		switch ($provider) {

			// local video
			case 'local':
				$ext_legal = array( 'mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv' );
				$ext = pathinfo( $url, PATHINFO_EXTENSION );

				// check if extension is legal
				if ( empty( $ext ) || ! in_array( $ext, $ext_legal ) ) {
					return array( 'valid' => false );
				}

				$data = array(
					'id'  => $this->get_post_by_url($url),
					'url' => $url
				);
				break;

			// youtube.com
			case 'youtube':
				$legal_parameters = array(
					'start',
					'end'
				);

				// #t=...

				break;

			// vimeo.com
			case 'vimeo':
				// #t=80s

				break;

			// dailymotion.com
			case 'dailymotion':

				// extract info of a time-link
			/*	preg_match('/t=(?:(\d+)m)?(?:(\d+)s)?/', $url, $attr);
				if( !empty($attr[1] ) || !empty($attr[2]) ) {
					$min = !empty($attr[1]) ? $attr[1]*60 : 0;
					$sek = !empty($attr[2]) ? $attr[2]    : 0;
					$video_time = $min + $sek;
				} else {
					preg_match('/start=(\d+)/', $url, $attr);
					if( !empty($attr[1] ) )
						$video_time = $attr[1];
					else
						$video_time = 0;
				}*/

				break;
		}
		return isset($data) ? $data : false;
	}


	/**
	 * Pulls the new featured image picture to local and sets it as featured image.
	 * Since 1.0, got it own function in 1.5
	 *
	 * @since 1.5
	 */
	function set_featured_video_image( $post_id, $data ) {
		// Is this screen capture already existing in our media library?
		$img  = $this->super->get_post_by_custom_meta('_fvp_image', $data['provider'] . '?' . $data['id']);
		$img2 = $this->super->get_post_by_custom_meta('_fvp_image_url', $data['img_url']);
		$img = ! empty( $img ) ? $img : $img2;

		if( empty( $img ) ) {

			// Generate attachment post metadata
			$img_data = array(
				'post_content' => $data['description'],
				'post_title'   => $data['title'],
				'post_name'    => $data['filename']
			);

			// attach external image
			include_once( FVP_DIR . 'php/somatic_attach_external_image.php' );
			$img = somatic_attach_external_image(
				$data['img_url'],
				$post_id,
				false, // make featured image automatically
				$data['filename'],
				$img_data
			);

			// generate picture metadata
			$img_meta = wp_get_attachment_metadata( $img );
			$img_meta['image_meta'] = array_merge(
				$img_meta['image_meta'],
				array(
					'credit'       => $data['id'],
					'camera'       => $data['provider'],
					'caption'      => $data['description'],
					'copyright'    => $data['author'],
					'title'        => $data['title']
				)
			);

			// save picture metadata
			wp_update_attachment_metadata( $img, $img_meta );

			// TODO: HANDLE THIS FROM OLD VERSIONS!
			//update_post_meta( $img, '_fvp_image', $data['provider'] . '?' . $data['id'] );

			$url_without_protocol = str_replace( parse_url( $data['img_url'], PHP_URL_SCHEME ), '', $data['img_url'] );
			update_post_meta( $img, '_fvp_image_url', $url_without_protocol );
		}

		if ( ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $img );
		}

		return $img;
	}


	/**
	 * Removes the old featured image
	 * Used since 1.0, got it own function in 1.4
	 *
	 * @since 1.4
	 */
	function delete_featured_video_image($post_id, $meta) {
		if ( empty( $meta['img'] ) )
			return false;

		// Unset featured image if it is from this video
		delete_post_meta( $post_id, '_thumbnail_id', $meta['img'] );

		// Check if other posts use the image, if not we can delete it completely
		$other = $this->super->get_post_by_custom_meta( '_thumbnail_id', $meta['img'] );
		if ( empty( $other ) ) {
			wp_delete_attachment( $meta['img'] );
			delete_post_meta( $meta['img'], '_fvp_image', $meta['provider'] . '?' . $meta['id'] );
			delete_post_meta( $meta['img'], '_fvp_image_url', $meta['img_url'] );
		}
	}


	/**
	 *
	 * @since 1.7
	 */
	public function ajax_get_embed(){
		header( "Content-Type: application/json" );

		if ( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'featured-video-plus-nonce') ){
			echo json_encode(array('success' => false, 'html' => 'invalid nonce'));
			exit();
		}

		if (has_post_video($_POST['id'])){
			$meta = get_post_meta($_POST['id'], '_fvp_video', true);

			$video = get_the_post_video( $_POST['id'] );
			echo json_encode(array('success' => 'true', 'html' => $video, 'id' => $meta['id']));
		} else{
			$image = get_the_post_thumbnail($_POST['id']);
			echo json_encode(array('success' => 'false','html' => $image));
		}
		exit;
	}

	/*
	 * Initializes the help texts.
	 *
	 * @since 1.3
	 */
	public function help() {
		$mediahref = (get_bloginfo('version') >= 3.5) ? '<a href="#" class="insert-media" title="Add Media">' : '<a href="media-upload.php?post_id=4&amp;type=video&amp;TB_iframe=1&amp;width=640&amp;height=207" id="add_video" class="thickbox" title="Add Video">';
		$general   = (get_bloginfo('version') >= 3.6) ? sprintf( __('To use local videos, copy the <code>Link To Media File</code> from your %sMedia Library%s and paste it into the text field.', 'featured-video-plus'), $mediahref, '</a>' ) :
		                                                sprintf( __('To use local videos as Featured Videos WordPress 3.6 or higher is required.', 'featured-video-plus'), $mediahref, '</a>' );

		$this->help_localmedia = '
<h4 style="margin-bottom: 0;"></h4>
<p>'.$general.'</p>
<h4 style="margin-bottom: 0;">'.__('Supported Video Formats','featured-video-plus').':</h4>
<p style="margin-top: 0;"><code>webM</code>, <code>mp4</code>, <code>ogv</code>, <code>m4v</code>, <code>wmv</code>, <code>flv</code></p>
<h4 style="margin-bottom: 0;">'.__('Converting your videos','featured-video-plus').':</h4>
<p style="margin-top: 0;">'.sprintf(__('Take a look at the %sMiro Video Converter%s. It is open source, lightweight and compatible with Windows, Mac and Linux.','featured-video-plus'),'<a href="http://www.mirovideoconverter.com/" target="_blank">','</a>').'</p>
<h4 style="margin-bottom: 0;">'.__('Fixing upload errors','featured-video-plus').':</h4>
<ul style="margin-top: 0;">
<li>'.sprintf(__('Read %sthis%s on how to increase the <strong>maximum file upload size</strong>.','featured-video-plus'),'<a href="http://www.wpbeginner.com/wp-tutorials/how-to-increase-the-maximum-file-upload-size-in-wordpress/" target="_blank">','</a>').'</li>
</ul>'."\n";

		$dir = wp_upload_dir();
		$this->help_urls = '
<p>'.__('These are some of the tested URL formats. Everything in bold is required, everything in brackets is optional.','featured-video-plus').'</p>
<ul>
	<li>Local Videos:
	<ul><li><code><strong>'.$dir['baseurl'].'/<em>FOLDER/FILENAME.webm|mp4|ogg|ogv</em></strong></code></li></ul></li>
	<li>YouTube:
	<ul><li><code>[http(s)://](www.)<strong>youtu.be/<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li>
	<li><code>[http(s)://](www.)<strong>youtube.com/watch?v=<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li>
	<li><code>[http(s)://](www.)<strong>youtube.com/v/<em>ELEVENCHARS</em></strong>(?random=13)(?t=1m3s)</code></li></ul></li>
	<li>Vimeo:
	<ul><li><code>(http(s)://)(www.)<strong>vimeo.com/<em>UNIQUEID</em></strong>(#stuff)</code></li></ul></li>
	<li>Dailymotion:
	<ul><li><code>(http(s)://)(www.)<strong>dailymotion.com/video/<em>UNIQUEID</em></strong>(_video_title)(#stuff)</code></li></ul></li>
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
		if( $screen->id != 'post' )
			return;

		if( get_bloginfo('version') >= 3.3 ) {
			// LOCALVIDEOS HELP TAB
			$screen->add_help_tab( array(
				'id'      => 'fvp_help_localvideos',
				'title'   => __('Featured Video','featured-video-plus').':&nbsp;'.__('Local Media', 'featured-video-plus'),
				'content' => $this->help_localmedia
			));

			// LEGAL URLs HELP TAB
			$screen->add_help_tab( array(
				'id'      => 'fvp_help_urls',
				'title'   => __('Featured Video','featured-video-plus').':&nbsp;'.__('Valid URLs', 'featured-video-plus'),
				'content' => $this->help_urls
			));
		}
	}

	/**
	 * Adds help text to contextual help. WordPress 3.3-
	 *
	 * @see http://wordpress.stackexchange.com/a/35164
	 *
	 * @since 1.3
	 */
	public function help_pre_33( $contextual_help, $screen_id, $screen ) {
		if( $screen->id != 'post' )
			return $contextual_help;

		$contextual_help .= '<hr /><h3>'.__('Featured Video','featured-video-plus').':&nbsp;'.__('Local Media', 'featured-video-plus').'</h3>';
		$contextual_help .= $this->help_localmedia;
		$contextual_help .= '<h3>'.__('Featured Video','featured-video-plus').':&nbsp;'.__('Valid URLs', 'featured-video-plus').'</h3>';
		$contextual_help .= $this->help_urls;

		return $contextual_help;
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


	/**
	 *
	 * @see   http://php.net/manual/en/function.parse-url.php
	 * @see   http://php.net/manual/en/function.parse-str.php
	 * @since 2.0.0
	 */
	private function parse_query( $url ) {
		$query = parse_url($url, PHP_URL_QUERY);
		$parameters = array();
		parse_str($query, $parameters);

		return $parameters;
	}


	/**
	 * Utilizes the WordPress oembed class for fetching the oembed info object.
	 *
	 * @see   http://oembed.com/
	 * @since 2.0.0
	 */
	private function oembed_fetch( $url ) {
		require_once( ABSPATH . '/' . WPINC . '/class-oembed.php' );
		$oembed = _wp_oembed_get_object();

		// fetch the oEmbed data with some arbitrary big size to get the biggest
		// thumbnail possible
		$raw = $oembed->fetch(
			$oembed->get_provider($url),
			$url,
			array(
				'width'  => 4096,
				'height' => 4096
			)
		);

		return $raw;
	}
}
