<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing plugin specific WordPress administration panels
 * functionality.
 *
 * Specifically a metabox on post/page edit views and a new options section
 * under settings->media. Additionally the ajax request handlers and
 * help tabs.
 *
 * @since 1.0.0
 */
class FVP_Backend extends Featured_Video_Plus {

	/**
	 * Register actions and filters.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init',            array( $this, 'upgrade' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_menu',            array( $this, 'metabox_register' ) );
		add_action( 'save_post',             array( $this, 'metabox_save' ) );
		add_action( 'load-post.php',         array( $this, 'tabs' ), 20 );

		add_filter( 'plugin_action_links',
		            array( $this, 'plugin_action_link' ),
		            10, 2 );
		add_filter( 'admin_post_thumbnail_html',
		            array( $this, 'featured_image_notice' ),
		            10, 2 );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_fvp_save', array( $this, 'metabox_save_ajax' ) );
			add_action( 'wp_ajax_fvp_get_embed', array( $this, 'ajax_get_embed' ) );
			add_action( 'wp_ajax_nopriv_fvp_get_embed',
			            array( $this, 'ajax_get_embed' ) );
		}
	}


	/**
	 * Enqueue all scripts and styles needed when viewing the backend.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} $hook Current view hook.
	 */
	public function enqueue( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		$min = SCRIPT_DEBUG ? '' : '.min';

		// jQuery script for automatically resizing <textarea>s.
		wp_register_script(
			'jquery.autosize',
			FVP_URL . "js/jquery.autosize$min.js",
			array( 'jquery' ),
			FVP_VERSION,
			false
		);

		// Script handling featured video form interactions with ajax requests etc.
		wp_enqueue_script(
			'fvp-post',
			FVP_URL . "js/post$min.js",
			array(
				'jquery',
				'jquery.autosize',
			),
			FVP_VERSION
		);

		// Some variables required in JS context.
		$upload_dir = wp_upload_dir();
		wp_localize_script( 'fvp-post', 'fvp_post', array(
			'wp_upload_dir' => $upload_dir['baseurl'],
			'loading_gif'   => get_admin_url( null, 'images/loading.gif' )
		));

		// General backend style.
		wp_enqueue_style(
			'fvp-backend',
			FVP_URL . 'styles/backend.css',
			array(),
			FVP_VERSION,
			'all'
		);
	}


	/**
	 * Register the metabox on post/page edit views.
	 *
	 * @since 1.0.0
	 */
	public function metabox_register() {
		$post_types = get_post_types( array( 'public' => true ) );

		// Cycle through all post types.
		foreach ( $post_types AS $post_type ) {
			// Ignore attachment post type.
			if ( 'attachment' === $post_type ) {
				continue;
			}

			// Register metabox.
			add_meta_box(
				'featured_video_plus-box',
				__( 'Featured Video', 'featured-video-plus' ),
				array( $this, 'metabox_content' ),
				$post_type,
				'side',
				'core'
			);
		}
	}


	/**
	 * Callback function of the metabox; generates the HTML content.
	 *
	 * @since 1.0.0
	 */
	public function metabox_content() {
		wp_nonce_field( FVP_NAME, 'fvp_nonce' );

		// Get current post's id.
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $GLOBALS['post']->ID;

		$options = get_option( 'fvp-settings' );
		$meta = get_post_meta( $post_id, '_fvp_video', true );
		$has_post_video = has_post_video( $post_id );

		$content = '';

		// Current featured video.
		$content .= sprintf(
			'<div id="fvp_current_video"%s>%s</div>',
			$this->inline_styles( array(
				'height: 0px' => (bool) $has_post_video,
			), true, true ),
			get_the_post_video( $post_id, array( 256, 144 ) )
		);

		// Input box containing the featured video URL input.
		$full = $has_post_video ? get_the_post_video_url( $post_id ) : '';

		// Media gallery wrapper.
		$content .= sprintf(
			'<div class="fvp_input_wrapper" data-target="#fvp_video" data-title="%1$s" data-button="%1$s">',
			esc_attr__( 'Set Featured Video', 'featured-video-plus' )
		);

		// Video input.
		$content .= sprintf(
			'<textarea class="fvp_input" id="fvp_video" name="fvp_video" type="text" placeholder="%s">%s</textarea>',
			esc_attr__( 'Video URL', 'featured-video-plus' ),
			$full
		);

		// Media gallery button.
		$content .= sprintf(
			'<a href="#" class="fvp_video_choose">' .
				'<span class="fvp_media_icon"%s></span>' .
			'</a>',
			$this->inline_styles(array(
				'background-image' => sprintf(
					'url(%s/wp-admin/images/media-button.png)',
					get_bloginfo( 'wpurl' )
				)
			), true, true)
		);

		// Close media gallery wrapper.
		$content .= '</div>';

		// 'Current theme does not support Featured Images' warning.
		if (
			! current_theme_supports( 'post-thumbnails' ) &&
			'manual' !== $options['mode']
		) {
			$content .= '<p class="fvp_warning description">';
			$content .= sprintf(
				'<span style="font-weight: bold;">%s</span>&nbsp;',
				esc_html__(
					'The current theme does not support Featured Images',
					'featured-video-plus'
				)
			);

			$content .= sprintf(
				esc_html__(
					'To display Featured Videos you need to use the <code>Shortcode</code> or <code>PHP functions</code>. To hide this notice deactivate &quot;<em>Replace Featured Images</em>&quot; in the %sMedia Settings%s.',
					'featured-video-plus'
				),
				'<a href="' . esc_attr( get_admin_url( null, '/options-media.php' ) ) . '">',
				'</a>'
			);
				'</p>';
		}

		echo "\n\n\n<!-- Featured Video Plus Metabox -->\n";
		echo $content;
		echo "<!-- Featured Video Plus Metabox End-->\n\n\n";
	}


	/**
	 * Saves metabox changes - NON AJAX.
	 *
	 * @since 1.0.0
	 * @uses $this->save()
	 *
	 * @param {int} $post_id
	 */
	public function metabox_save( $post_id ){
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		     ( defined( 'DOING_AJAX' )     && DOING_AJAX )     ||
		     ( ! current_user_can( 'edit_post', $post_id ) )   ||
		     ( false !== wp_is_post_revision( $post_id ) )
		) {
			return;
		}

		$post = array(
			'id'        => $post_id,
			'fvp_nonce' => ! empty( $_POST['fvp_nonce'] ) ? $_POST['fvp_nonce'] : '',
			'fvp_video' => ! empty( $_POST['fvp_video'] ) ? $_POST['fvp_video'] : ''
		);

		$this->save( $post );

		return;
	}


	/**
	 * Saves metabox changes - AJAX.
	 *
	 * @since 1.5.0
	 * @uses $this->save()
	 */
	public function metabox_save_ajax() {
		$post = array(
			'id' => $_POST['id'],
			'fvp_nonce' => ! empty( $_POST['fvp_nonce'] ) ? $_POST['fvp_nonce'] : '',
			'fvp_video' => ! empty( $_POST['fvp_video'] ) ? $_POST['fvp_video'] : '',
			'fvp_set_featimg' =>
				! empty( $_POST['fvp_set_featimg'] ) ? $_POST['fvp_set_featimg'] : '',
		);

		// this also verifies the nonce
		$meta = $this->save( $post );

		$img = _wp_post_thumbnail_html(
			get_post_thumbnail_id( $post['id'] ),
			$post['id']
		);

		if ( has_post_video( $post['id'] ) ) {
			$video = get_the_post_video( $post['id'], array( 256, 144 ) );
			$response = json_encode( array(
				'type'     => 'update',
				'valid'    => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'video'    => $video,
				'img'      => $img,
				'provider' => isset( $meta['provider'] ) ? $meta['provider'] : null
			) );
		} else {
			$response = json_encode( array(
				'task'  => 'remove',
				'valid' => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'img'   => $img,
			) );
		}

		exit( $response );
	}


	/**
	 * Used for processing a save request.
	 *
	 * @since 1.5.0
	 *
	 * @param  {assoc} $post
	 * @return {assoc/bool} video meta data on success, false on failure
	 */
	private function save( $post ) {
		if (
			! isset( $post['fvp_nonce'] ) ||
			! wp_verify_nonce( $post['fvp_nonce'], FVP_NAME )
		) {
			return false;
		}

		// get fvp_video post meta data
		$meta = get_post_meta( $post['id'], '_fvp_video', true );

		// parse video url
		$url = ! empty( $post['fvp_video'] ) ? trim( $post['fvp_video'] ) : '';

		// has featured image AND url did not change or is and was empty
		if (
			! $post['fvp_set_featimg'] && (
				( ! empty( $meta['full'] ) && $url == $meta['full'] ) ||
				(   empty( $meta['full'] ) && empty( $url ) )
			)
		) {
			return false;
		}

		// there was a video and we want to delete it
		if ( ! empty( $meta['full'] ) && empty( $url ) ) {
			delete_post_meta( $post['id'], '_fvp_video' );
			$this->delete_featured_image( $post['id'], $meta );
			return false;
		}

		$data = $this->get_video_data( $url );

		// Do we have a screen capture to pull?
		if ( empty( $data['img_url'] ) ) {
			$data['img_url'] = FVP_URL . 'img/playicon.png';
			$data['filename'] = 'Featured Video Plus Placeholder';
		}

		$img = $this->set_featured_image( $post['id'], $data );

		$meta = array_merge(
			array(
				'full'     => $url,
				'img'      => ! empty( $img ) ? $img : null,
				'valid'    => 1, // can be overwritten by $data
				'provider' => 'raw', // "
			),
			$data
		);

		update_post_meta( $post['id'], '_fvp_video', $meta );
		return $meta;
	}


	/**
	 * Returns an array containing video information like id provider imgurl etc.
	 *
	 * @since 1.5.0
	 *
	 * @param  {string} $url The video URL
	 * @return {assoc}  Associative array containing the video information data
	 */
	private function get_video_data( $url ) {
		$data = array();

		$local = wp_upload_dir();
		$islocal = strpos( $url, $local['baseurl'] );

		// handle local videos
		if ( false !== $islocal ) {
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
				'title'       => ! empty( $raw->title )         ? $raw->title : null,
				'author'      => ! empty( $raw->author_name )   ? $raw->author_name : null,
				'description' => ! empty( $raw->description )   ? $raw->description : null,
				'img_url'     => ! empty( $raw->thumbnail_url ) ? $raw->thumbnail_url : null,
				'filename'    => ! empty( $raw->title ) ? sanitize_file_name( $raw->title ) : null,
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
					'url' => $url,
				);
				break;
		}

		return ! empty( $data ) ? $data : false;
	}


	/**
	 * Sets a remote image as featured image for the given post.
	 *
	 * @since 1.5.0
	 *
	 * @param  {int}   $post_id
	 * @param  {assoc} $data    Video information data containing img_url
	 * @return {int}   ID of the inserted attachment
	 */
	private function set_featured_image( $post_id, $data ) {
		// Is this screen capture already existing in our media library?
		$img = $this->get_post_by_custom_meta( '_fvp_image_url', $data['img_url'] );

		if ( empty( $img ) ) {
			$file = array(
			  'name' => basename( $data['img_url'] ),
			);

			// Get external image
			$file['tmp_name'] = download_url( $data['img_url'] );
			if ( is_wp_error( $file['tmp_name'] ) ) {
				return false;
			}

			// Insert into media library
			$url_type = image_type_to_extension(
				exif_imagetype( $file['tmp_name'] ),
				false
			);
			$file['name'] = basename( $data['img_url'] . '.' . $url_type );
			$img = media_handle_sideload( $file, $post_id );

			// save picture source url in post meta
			update_post_meta( $img, '_fvp_image_url', $data['img_url'] );
		}

		// set featured image
		if ( ! has_post_thumbnail( $post_id ) ) {
			set_post_thumbnail( $post_id, $img );
		}

		return $img;
	}


	/**
	 * Removes the old featured image.
	 *
	 * @since 1.4.0
	 *
	 * @param {int}   $post_id
	 * @param {assoc} $meta    FVP video meta data containing 'img' with
	 *                         the FVP image attachment ID.
	 */
	private function delete_featured_image( $post_id, $meta ) {
		if ( empty( $meta['img'] ) ) {
			return false;
		}

		// Unset featured image if it is from this video
		delete_post_meta( $post_id, '_thumbnail_id', $meta['img'] );

		// Check if other posts use the image, if not we can delete it completely
		$other = $this->get_post_by_custom_meta( '_thumbnail_id', $meta['img'] );
		if ( empty( $other ) && ! empty( $meta['img_url'] ) ) {
			wp_delete_attachment( $meta['img'] );
			delete_post_meta( $meta['img'], '_fvp_image_url', $meta['img_url'] );

			// pre 2.0.0
			delete_post_meta(
				$meta['img'],
				'_fvp_image',
				$meta['provider'] . '?' . $meta['id']
			);
		}
	}


	/**
	 * Return video embed code.
	 *
	 * Located in backend class because all AJAX requests are handled on the
	 * admin side of WordPress - WordPress only distinguishes between
	 * priv and nopriv requests. This function is only called by the frontend
	 * JavaScript.
	 *
	 * @since 1.7.0
	 */
	public function ajax_get_embed() {
		header( 'Content-Type: application/json' );

		// bad request
		if ( ! wp_verify_nonce( $_POST['nonce'], 'featured-video-plus-nonce' ) ) {
			$response = json_encode( array(
				'success' => false,
				'html'    => 'invalid nonce',
			) );

			// return featured video as requested
		} elseif ( has_post_video( $_POST['id'] ) ) {
			$meta  = get_post_meta( $_POST['id'], '_fvp_video', true );
			$video = get_the_post_video( $_POST['id'] );

			$response = json_encode( array(
				'success' => 'true',
				'html'    => $video,
				'id'      => $meta['id'],
			) );

			// no video, return featured image
		} else {
			$image = get_the_post_thumbnail( $_POST['id'] );

			$response = json_encode(array(
				'success' => 'false',
				'html'    => $image,
			));
		}

		exit( $response );
	}


	/**
	 * Adds help tabs to contextual help. WordPress 3.3+
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_help_tab
	 *
	 * @since 1.3.0
	 */
	public function tabs() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->id || get_bloginfo( 'version' ) < 3.3 ) {
			return;
		}

		// Tab Headline
		$help = sprintf(
			'<h3>%s</h3>',
			esc_html__( 'Featured Video Plus', 'featured-video-plus' )
		);

		// oEmbed
		$help .= '<p>' . sprintf(
			esc_html__( 'Take a video url from one of the %ssupported oembed providers%s and paste it into the Featured Video input field.', 'featured-video-plus' ),
			'<a href="https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F">',
			'</a>'
		) . '</p>';

		// Local
		$help .= '<p>' . sprintf(
			esc_html__( 'Alternatively you can select one of the videos from your media library using the small media icon to the right in the URL input vield. The plugin makes use of %sWordPress\' native functionality%s - no gurantee for compatibility with all formats.', 'featured-video-plus' ),
			'<a href="http://mediaelementjs.com/">',
			'</a>'
		) . '</p>';

		// Converting
		$help .= sprintf(
			'<h4 style="margin-bottom: 0;">%s</h4>',
			esc_html__( 'Converting your videos', 'featured-video-plus' )
		);

		$help .= '<p style="margin-top: 0;">' . sprintf(
			esc_html__( 'Take a look at the %sMiro Video Converter%s. It is open source, lightweight and compatible with Windows, Mac and Linux.', 'featured-video-plus' ),
			'<a href="http://www.mirovideoconverter.com/" target="_blank">',
			'</a>'
		) . '</p>';

		// Uploading
		$help .= '<h4 style="margin-bottom: 0;">' . esc_html__( 'Fixing upload errors', 'featured-video-plus' ) . ':</h4>';
		$help .= '<p style="margin-top: 0;">' . sprintf(
			esc_html__( 'Read %sthis%s on how to increase the maximum file upload size.', 'featured-video-plus' ),
			'<a href="http://goo.gl/yxov27" target="_blank">',
			'</a>'
		) . '</p>';

		// REGISTER HELP TAB
		$screen->add_help_tab( array(
			'id'      => 'featured_video_plus',
			'title'   => __( 'Featured Video Plus', 'featured-video-plus' ),
			'content' => $help,
		) );
	}


	/**
	 * Adds a media settings link to the plugin info
	 *
	 * @since 1.2
	 */
	function plugin_action_link( $links, $file ) {
		if ( $file == FVP_NAME . '/' . FVP_NAME . '.php' ) {
			$settings_link = sprintf(
				'<a href="%s/wp-admin/options-media.php">Media Settings</a>',
				esc_attr( get_bloginfo( 'wpurl' ) )
			);
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	/**
	 * Add a notice about the requirement of a featured image to the
	 * featured image meta box.
	 *
	 * @param  {string} $content
	 * @param  {int}    $post_id
	 * @return {string}
	 */
	function featured_image_notice( $content, $post_id ) {
		if ( has_post_thumbnail( $post_id ) || ! has_post_video( $post_id ) ) {
			return $content;
		}

		$notice  = '<span class="fvp-notice">';
		$notice .= __(
			'Featured Videos require a Featured Image for automatic replacement.',
			'featured-video-plus'
		);
		$notice .= '&nbsp;<a href="#" class="fvp-set_featimg hidden">' . __(
			'Auto set',
			'featured-video-plus'
		) . '</a>';
		$notice .= '</span>';

		return $notice . $content;
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
		if ( '0' === $version ) {
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

		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE guid=%s;",
			$url
		) );

		return $id;
	}
}
