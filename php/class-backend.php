<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing functions required WordPress administration panels. Metabox on post/page edit views and options section under settings->media.
 *
 * @since 1.0
 *
 * @param featured_video_plus instance
 */
class FVP_Backend extends Featured_Video_Plus {
	private $help_localmedia;
	private $help_urls;


	/**
	 * Creates a new instance of this class, saves the featured_video_instance and default value for the meta box input.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init',            array( $this, 'upgrade' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_menu',            array( $this, 'metabox_register' ) );
		add_action( 'save_post',             array( $this, 'metabox_save' ) );
		add_action( 'admin_init',            array( $this, 'help' ) );
		add_action( 'load-post.php',         array( $this, 'tabs' ), 20 );

		add_filter( 'plugin_action_links',   array( $this, 'plugin_action_link' ), 10, 2);

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_fvp_save',             array( $this, 'metabox_save_ajax' ) );
			add_action( 'wp_ajax_fvp_get_embed',        array( $this, 'ajax_get_embed' ) );
			add_action( 'wp_ajax_nopriv_fvp_get_embed', array( $this, 'ajax_get_embed' ) );
		}
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
	public function enqueue( $hook ) {
		if( $hook != 'post.php' && $hook != 'post-new.php' )
			return;

		$min = SCRIPT_DEBUG ? '' : '.min';

		// jQuery script for automatically resizing <textarea>s
		wp_register_script(
			'jquery.autosize',
			FVP_URL . "js/jquery.autosize$min.js",
			array( 'jquery' ),
			FVP_VERSION,
			false
		);

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
			'wp_upload_dir' => $upload_dir['baseurl'],
			'loading_gif'   => get_admin_url( null, 'images/loading.gif' )
		));

		// general backend style
		wp_enqueue_style(
			'fvp-backend',
			FVP_URL . "styles/backend.css",
			array(),
			FVP_VERSION,
			'all'
		);

		// HTML5 video handling
		wp_enqueue_script( 'wp-mediaelement' );
		wp_enqueue_style( 'wp-mediaelement' );
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
		$class = $has_post_image || ! $has_post_video || ( isset( $options['mode'] ) && $options['mode'] == 'manual' ) ? ' hidden' : '';
		$content .= "<div id='fvp_featimg_warning' class='fvp_notice{$class}'><p class='description'>";
		$content .= '<span style="font-weight: bold;">'.__('Featured Image').':</span>&nbsp;'.__('For automatically displaying the Featured Video a Featured Image is required.', 'featured-video-plus');
		$content .= "</p></div>\n";

		// set as featured image
		$class = isset($meta['provider']) && $meta['provider'] == 'local' || ! $has_post_video || ($has_post_image && $featimg_is_fvp) ? ' class="fvp_hidden"' : '';
		$content .= sprintf('<p id="fvp_set_featimg_box"'.$class.'>'."\n\t".'<span id="fvp_set_featimg_input">'."\n\t\t".'<input id="fvp_set_featimg" name="fvp_set_featimg" type="checkbox" value="set_featimg" />'."\n\t\t".'<label for="fvp_set_featimg">&nbsp;%s</label>'."\n\t".'</span>'."\n\t".'<a class="fvp_hidden" id="fvp_set_featimg_link" href="#">%s</a>'."\n".'</p>'."\n", __('Set as Featured Image', 'featured-video-plus'), __('Set as Featured Image', 'featured-video-plus') );

		// current theme does not support Featured Images warning
		if( !current_theme_supports('post-thumbnails') && $options['mode'] != 'manual' ) {
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
	public function metabox_save( $post_id ){
		if (( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		    ( defined( 'DOING_AJAX' )     && DOING_AJAX )     ||
		    ( ! current_user_can( 'edit_post', $post_id ) )   ||
		    ( false !== wp_is_post_revision( $post_id ) )
		   ) return;

		$post = array(
			'id'              => $post_id,
			'fvp_nonce'       => ! empty( $_POST['fvp_nonce'] )       ? $_POST['fvp_nonce']       : '',
			'fvp_set_featimg' => ! empty( $_POST['fvp_set_featimg'] ) ? $_POST['fvp_set_featimg'] : '',
			'fvp_video'       => ! empty( $_POST['fvp_video'] )       ? $_POST['fvp_video']       : '',
			'fvp_sec'         => ! empty( $_POST['fvp_sec'] )         ? $_POST['fvp_sec']         : ''
		);

		$this->save( $post );

		return;
	}


	/**
	 * Forwards ajax save requests to the $this->save function and generates a response.
	 *
	 * @since 1.5
	 */
	public function metabox_save_ajax() {
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

		if ( has_post_video( $post['id'] ) ){
			$video = get_the_post_video( $post['id'], array( 256, 144 ) );
			$response = json_encode(array(
				'type'     => 'update',
				'valid'    => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'video'    => $video,
				'img'      => $img,
				'provider' => isset( $meta['provider'] ) ? $meta['provider'] : null
			));
		} else {
			$response = json_encode(array(
				'task'  => 'remove',
				'valid' => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'img'   => $img
			));
		}

		exit( $response );
	}


	/**
	 * Used for processing a save request.
	 *
	 * @see   http://codex.wordpress.org/Function_Reference/update_post_meta
	 * @since 1.5
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
				'valid' => 1 // can be overwritten by $data
			),
			$data
		);

		update_post_meta( $post['id'], '_fvp_video', $meta );
		return $meta;
	}


	/**
	 * Returns an array containing video information like id provider imgurl etc.
	 *
	 * @see   http://oembed.com/
	 * @since 1.5
	 *
	 * @param  {string} $url The video URL
	 * @return {assoc}  Associative array containing the video information data
	 */
	function get_video_data( $url ) {
		$data = array();

		$local = wp_upload_dir();
		$islocal = strpos( $url, $local['baseurl'] );

		// handle local videos
		if( $islocal !== false ) {
			$provider = 'local';

		// handle external videos
		} else {
			$raw = $this->oembed->request( $url );

			// If no provider is returned the URL is invalid
			if ( empty( $raw ) || empty( $raw->provider_name ) ) {
				return array( 'valid' => false );
			}

			$provider = strtolower( $raw->provider_name );

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

		$data['parameters'] = $this->oembed->get_args( $url, $provider );

		// provider specific handling
		switch ( $provider ) {

			// local video
			case 'local':
				$ext_legal = array( 'mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv' );
				$ext = pathinfo( $url, PATHINFO_EXTENSION );

				// check if extension is legal
				if ( empty( $ext ) || ! in_array( $ext, $ext_legal ) ) {
					return array( 'valid' => false );
				}

				$data = array(
					'provider' => 'local',
					'id'  => $this->get_post_by_url( $url ),
					'url' => $url
				);
				break;
		}

		return ! empty( $data ) ? $data : false;
	}


	/**
	 * Pulls the new featured image picture to local and sets it as featured image.
	 * Since 1.0, got it own function in 1.5
	 *
	 * @since 1.5
	 *
	 * @param  {int}   $post_id
	 * @param  {assoc} $data    Video information data containing img_url
	 * @return {int}   ID of the inserted attachment
	 */
	function set_featured_video_image( $post_id, $data ) {
		// Is this screen capture already existing in our media library?
		$img = $this->get_post_by_custom_meta('_fvp_image_url', $data['img_url']);

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
			update_post_meta( $img, '_fvp_image_url', $data['img_url'] );
		}

		if ( ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $img );
		}

		return $img;
	}


	/**
	 * Removes the old featured image.
	 *
	 * @since 1.4
	 *
	 * @param {int} $post_id
	 * @param {int} FVP video meta data containing 'img' with the FVP image
	 *              attachment ID
	 */
	function delete_featured_video_image( $post_id, $meta ) {
		if ( empty( $meta['img'] ) )
			return false;

		// Unset featured image if it is from this video
		delete_post_meta( $post_id, '_thumbnail_id', $meta['img'] );

		// Check if other posts use the image, if not we can delete it completely
		$other = $this->get_post_by_custom_meta( '_thumbnail_id', $meta['img'] );
		if ( empty( $other ) ) {
			wp_delete_attachment( $meta['img'] );
			delete_post_meta( $meta['img'], '_fvp_image_url', $meta['img_url'] );

			// pre 2.0.0
			delete_post_meta( $meta['img'], '_fvp_image', $meta['provider'] . '?' . $meta['id'] );
		}
	}


	/**
	 *
	 * Located in backend class because all AJAX requests are handled on the
	 * admin side of WordPress - WordPress only distinguishes between
	 * priv and nopriv requests. This function is only called by the frontend
	 * JavaScript.
	 *
	 * @since 1.7
	 */
	public function ajax_get_embed(){
		header( "Content-Type: application/json" );

		// bad request
		if ( ! wp_verify_nonce( $_POST['nonce'], 'featured-video-plus-nonce' ) ) {
			$response =  json_encode(array(
				'success' => false,
				'html'    => 'invalid nonce',
			));

		// return featured video as requested
		} elseif ( has_post_video( $_POST['id'] ) ) {
			$meta  = get_post_meta( $_POST['id'], '_fvp_video', true );
			$video = get_the_post_video( $_POST['id'] );

			$response =  json_encode(array(
				'success' => 'true',
				'html'    => $video,
				'id'      => $meta['id'],
			));

		// no video, return featured image
		} else{
			$image = get_the_post_thumbnail($_POST['id']);

			$response = json_encode(array(
				'success' => 'false',
				'html'    => $image
			));
		}

		exit( $response );
	}


	/*
	 * Initializes the help texts.
	 *
	 * @since 1.3
	 */
	public function help() {
		$mediahref = '<a href="#" class="insert-media" title="Add Media">';
		$general   = sprintf( __('To use local videos, copy the <code>Link To Media File</code> from your %sMedia Library%s and paste it into the text field.', 'featured-video-plus'), $mediahref, '</a>' );

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
	 * Initiates the upgrade (plugin installation or update) logic.
	 *
	 * @since 2.0.0
	 */
	public function upgrade() {
		$version = get_option( 'fvp-version' );
		$options = $options_org = get_option( 'fvp-settings' );

		// determine current version
		if ( empty( $version ) ) {
			if ( ! isset( $options['overwrite'] ) && ! isset( $options['mode'] ) ) {
				$version = '0';
			} else {
				$version = ! empty( $options['version'] ) ? $options['version'] : '1.1';
			}
		}

		// either execute install or upgrade logic
		if ( $version == '0' ) {
			include_once( FVP_DIR . 'php/install.php' );
		} elseif ( $version != FVP_VERSION ) {
			include_once( FVP_DIR . 'php/upgrade.php' );
		}
	}


	/**
	 * Gets post id by it's url / guid.
	 *
	 * @see http://codex.wordpress.org/Class_Reference/wpdb
	 * @since 1.0
	 *
	 * @param  {string} $url which url to look for
	 * @return {int}    retrieved post ID
	 */
	function get_post_by_url( $url ) {
		global $wpdb;

		$id = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE guid=%s;",
			$url
		));

		return $id;
	}
}
