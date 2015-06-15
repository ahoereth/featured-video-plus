<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing plugin specific WordPress administration panels
 * functionality.
 *
 * Specifically the metabox on post/page edit views.
 *
 * @since 1.0.0
 */
class FVP_Backend extends Featured_Video_Plus {

	/**
	 * Register actions and filters.
	 */
	public function __construct() {
		parent::__construct();
		FVP_HTML::add_screens( array( 'post.php', 'post-new.php' ) );

		add_action( 'admin_init',            array( $this, 'upgrade' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_menu',            array( $this, 'metabox_register' ) );
		add_action( 'save_post',             array( $this, 'metabox_save' ) );

		add_filter( 'fvphtml_pointers', array( $this, 'pointers' ), 10, 2 );
		add_filter( 'plugin_action_links',
		            array( $this, 'plugin_action_link' ),
		            10, 2 );
		add_filter( 'admin_post_thumbnail_html',
		            array( $this, 'featured_image_box' ),
		            10, 2 );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_fvp_save', array( $this, 'metabox_save_ajax' ) );
			add_action( 'wp_ajax_fvp_remove_img', array( $this, 'ajax_remove_img' ) );
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

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

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
				'wp-mediaelement',
			),
			FVP_VERSION
		);

		// Some variables required in JS context.
		$upload_dir = wp_upload_dir();
		wp_localize_script( 'fvp-post', 'fvpPost', array(
			'wp_upload_dir' => $upload_dir['baseurl'],
			'loading_gif'   => get_admin_url( null, 'images/loading.gif' ),
			'debug'         => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG
		));

		// General backend style.
		wp_enqueue_style(
			'fvp-backend',
			FVP_URL . 'styles/backend.css',
			array(
				'wp-mediaelement',
			),
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
				'featured-video-plus-box',
				__( 'Featured Video', 'featured-video-plus' ),
				array( $this, 'metabox_content' ),
				$post_type,
				'side',
				'high'
			);
		}
	}


	/**
	 * Callback function of the metabox; generates the HTML content.
	 *
	 * @since 1.0.0
	 */
	public function metabox_content() {
		wp_nonce_field( FVP_NAME . FVP_VERSION, 'fvp_nonce' );

		// Get current post's id.
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $GLOBALS['post']->ID;

		$options = get_option( 'fvp-settings' );
		$meta = get_post_meta( $post_id, '_fvp_video', true );
		$has_post_video = has_post_video( $post_id );

		$content = '';

		// Current featured video.
		$content .= sprintf(
			'<div class="fvp-current-video"%s>%s</div>',
			FVP_HTML::inline_styles( array(
				'height: 0px' => ! $has_post_video,
			), true, true ),
			get_the_post_video( $post_id, array( 256, 144 ) )
		);

		// Input box containing the featured video URL input.
		$full = $has_post_video ? get_the_post_video_url( $post_id ) : '';

		// Media gallery wrapper.
		$content .= sprintf(
			'<div class="fvp-input-wrapper" data-target=".fvp-video" data-title="%1$s" data-button="%1$s">',
			esc_attr__( 'Set Featured Video', 'featured-video-plus' )
		);

		// Video input.
		$content .= sprintf(
			'<textarea class="fvp-video" name="fvp_video" type="text" placeholder="%s">%s</textarea>',
			esc_attr__( 'Video URL', 'featured-video-plus' ),
			$full
		);

		// Media gallery button.
		$content .= sprintf(
			'<a href="#" class="fvp-video-choose">' .
				'<span class="fvp-media-icon"%s></span>' .
			'</a>',
			FVP_HTML::inline_styles( array(
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
			$content .= '<p class="fvp-warning description">';
			$content .= sprintf(
				'<span style="font-weight: bold;">%s</span>&nbsp;',
				esc_html__(
					'The current theme does not support Featured Images',
					'featured-video-plus'
				)
			);

			$content .= sprintf(
				esc_html__(
					'To display Featured Videos you need to use the %1$sShortcode%2$s or %1$sPHP functions%2$s. To hide this notice deactivate %3$sReplace Featured Images%4$s in the %5$sMedia Settings%6$s.',
					'featured-video-plus'
				),
				'<code>', '</code>',
				'&quot;<em>', '</em>&quot;',
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
		if ( ! self::has_valid_nonce( $_POST ) ) {
			return false;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		     ( defined( 'DOING_AJAX' )     && DOING_AJAX )     ||
		     ( ! current_user_can( 'edit_post', $post_id ) )   ||
		     ( false !== wp_is_post_revision( $post_id ) )
		) {
			return;
		}

		$post = array(
			'id'        => $post_id,
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
		if ( ! self::has_valid_nonce( $_POST ) || empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		$post = array(
			'id' => (int) $_POST['id'],
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
			$response = array(
				'type'     => 'update',
				'valid'    => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'video'    => $video,
				'img'      => $img,
				'provider' => isset( $meta['provider'] ) ? $meta['provider'] : null
			);
		} else {
			$response = array(
				'task'  => 'remove',
				'valid' => isset( $meta['valid'] ) ? $meta['valid'] : null,
				'img'   => $img,
			);
		}

		wp_send_json_success( $response );
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
		// get fvp_video post meta data
		$meta = get_post_meta( $post['id'], '_fvp_video', true );

		// parse video url
		$url = ! empty( $post['fvp_video'] ) ? trim( $post['fvp_video'] ) : '';

		// Was this a force-auto-set featimg action?
		$setimg = ! empty ( $post['fvp_set_featimg'] ) && $post['fvp_set_featimg'];

		// Don't do anything if we are not setting the featured image AND the
		// URL is empty AND did not change.
		if ( ! $setimg && (
			( ! empty( $meta['full'] ) && $url == $meta['full'] ) ||
			(   empty( $meta['full'] ) && empty( $url ) )
		) ) {
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
			$data['img_url'] = FVP_URL . 'img/placeholder.png';
			$data['filename'] = 'Featured Video Plus Placeholder';
		}

		// Should we set the featured image?
		if ( $setimg || (
			! has_post_thumbnail( $post['id'] ) &&
			( empty( $meta['noimg'] ) || $meta['noimg'] )
		) ) {
			$img = $this->set_featured_image( $post['id'], $data );
			$data['noimg'] = false;
		}

		// Create the final _fvp_video meta data.
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
					'id'  => self::get_post_by_url( $url ),
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
		$img = self::get_post_by_custom_meta( '_fvp_image_url', $data['img_url'] );

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
		$other = self::get_post_by_custom_meta( '_thumbnail_id', $meta['img'] );
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
		if ( ! self::has_valid_nonce( $_POST ) || empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		// Parse post id.
		$id = (int) $_POST['id'];

		if ( has_post_video( $id ) ) {
			// Return featured video html as requested.
			$video = get_the_post_video( $id );
			wp_send_json_success( $video );
		} else {
			// Post has no video, return featured image html.
			$image = get_the_post_thumbnail( $id );
			wp_send_json_success( $image );
		}
	}


	/**
	 * Some people might not want to have a featured image because of whatever
	 * reason. We notify them about the probable incompatibility and offer the
	 * 'auto set' link to set the featured image using the plugin (video
	 * thumbnail or placeholder) but do not want to auto set it on every post
	 * save automatically if they explicitly removed it before. This function
	 * therefor is triggered by an AJAX request when removing a featured image
	 * which was previously set by the plugin.
	 */
	public function ajax_remove_img() {
		if ( ! self::has_valid_nonce( $_POST ) || empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		// Retrieve post id and check user capabilities.
		$id = (int) $_POST['id'];
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error();
		}

		// Retrieve featured video metadata.
		$meta = get_post_meta( $id, '_fvp_video', true );

		// Delete the image from database if feasible. This also again tries to
		// remove the link of the featured image to the post although it will
		// probably already be unlinked by WordPress internal functionality.
		$this->delete_featured_image( $id, $meta );

		// Remember that we do not want to set a featured image automatically for
		// this post.
		$meta['noimg'] = true;

		// Remove now unnecessary image information from the video meta.
		$meta['img'] = null;

		// Save meta.
		update_post_meta( $id, '_fvp_video', $meta );

		// Respond to the client.
		$html = _wp_post_thumbnail_html( get_post_thumbnail_id( $id ), $id );
		wp_send_json_success( $html );
	}


	/**
	 * Add a pointer to the Featured Video Plus box on the post edit screen for
	 * initial explanation.
	 *
	 * @param  {array}  $pointers
	 * @param  {string} $hook
	 * @return {array}
	 */
	public function pointers( $pointers, $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return $pointers;
		}

		$pointers['fvp-post-box'] = array(
			'target' => '#featured-video-plus-box',
			'title' => esc_html__( 'Featured Videos', 'featured-video-plus' ),
			'content' => sprintf(
				esc_html__(
					'Simply paste a URL into this input to add a bit extra life to your posts. %sTry an example%s.',
					'featured-video-plus'
				),
				'<a href="#" onclick="jQuery(\'.fvp-video\').val(\'http://youtu.be/CfNHleTEpTI\').trigger(\'blur\'); return false;">',
				'</a>'
			) . '</p><p>' . sprintf(
				esc_html__(
					'To adjust how featured videos are displayed on the frontend checkout the %smedia settings%s.',
					'featured-video-plus'
				),
				sprintf(
					'<a href="%s/wp-admin/options-media.php#fvp-section">',
					esc_attr( get_bloginfo( 'wpurl' ) )
				),
				'</a>'
			),
			'position' => array(
				'align' => 'middle',
				'edge' => 'right'
			)
		);

		return $pointers;
	}


	/**
	 * Adds a media settings link to the plugin info
	 *
	 * @since 1.2
	 */
	public function plugin_action_link( $links, $file ) {
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
	public function featured_image_box( $content, $post_id ) {
		if ( ! has_post_video( $post_id ) ) {
			// Has no featured video so the plugin does not interfere.
			return $content;
		}

		if ( has_post_thumbnail( $post_id ) ) {
			// Has featured video and featured image.
			return $content . sprintf(
				'<p class="hidden"><a href="#" class="fvp-remove-image">%s</a></p>',
				esc_html__( 'Remove featured image' )
			);
		}

		// Has featured video but not featured image.
		return sprintf(
			'<p class="fvp-notice">%s <a href="#" class="fvp-set-image hide-if-no-js">%s</a></p>',
			esc_html__(
				'Featured Videos require a Featured Image for automatic replacement.',
				'featured-video-plus'
			),
			esc_html__( 'Auto set', 'featured-video-plus' )
		) . $content;
	}


	/**
	 * Initiates the upgrade (plugin installation or update) logic.
	 *
	 * @since 2.0.0
	 */
	public function upgrade() {
		$version = get_option( 'fvp-version' );
		$options = $options_org = get_option( 'fvp-settings' );

		// either execute install or upgrade logic
		if ( empty( $version ) || empty( $options ) ) {
			include_once( FVP_DIR . 'php/inc-install.php' );
		} elseif ( $version !== FVP_VERSION ) {
			include_once( FVP_DIR . 'php/inc-upgrade.php' );
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
	protected static function get_post_by_url( $url ) {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE guid=%s;",
			$url
		) );

		return $id;
	}


	/**
	 * Check the given assoc array for a 'fvp_nonce' field and check if this
	 * field contains a valid nonce.
	 *
	 * @param  {assoc}  $post_data
	 * @return boolean
	 */
	private static function has_valid_nonce( $post_data ) {
		if (
			! isset( $post_data['fvp_nonce'] ) ||
			! wp_verify_nonce( $post_data['fvp_nonce'], FVP_NAME . FVP_VERSION )
		) {
			return false;
		}

		return true;
	}


}
