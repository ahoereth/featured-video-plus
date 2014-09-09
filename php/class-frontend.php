<?php
/**
 * Class containing functions required on frontend. Enqueue scripts/styles, replace featured images by featured videos.
 *
 * @since 1.0
 */
class FVP_Frontend extends Featured_Video_Plus {

	/**
	 * Creates a new instace of this class, saves the featured_video_instance.
	 *
	 * @since 1.0
	 *
	 * @param featured_video_plus_instance required, dies without
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue' ) );
		add_filter( 'post_thumbnail_html', array( $this, 'filter_post_thumbnail' ), 99, 5 );

		add_shortcode( 'featured-video-plus', array( $this, 'shortcode' ) );
	}

	/**
	 * Enqueue all scripts and styles needed when viewing the frontend.
	 *
	 * @since 1.0
	 */
	public function enqueue() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		$options = get_option( 'fvp-settings' );


		wp_register_script(
			'jquery.fitvids',
			FVP_URL . "js/jquery.fitvids$min.js",
			array( 'jquery' ),
			'1.1',
			false
		);

		wp_register_script(
			'jquery.domwindow',
			FVP_URL . "js/jquery.domwindow$min.js",
			array( 'jquery' ),
			FVP_VERSION
		);

		// Basic dependencies. Is extended in the following.
		$deps = array( 'jquery' );

		// Is responsive video functionality required? Only when width is set to
		// 'auto' and display mode is not set to overlay.
		if ( $options['sizing']['wmode'] == 'auto' && $options['usage'] != 'overlay' ) {
			$deps[] = 'jquery.fitvids';
		}

		// Is modal functionality required?
		if ( $options['usage'] == 'overlay' ) {
			$deps[] = 'jquery.domwindow';
		}

		// general frontend script
		wp_enqueue_script(
			'fvp-frontend',
			FVP_URL . "js/frontend$min.js",
			$deps,
			FVP_VERSION
		);

		// some context for JS
		wp_localize_script( 'fvp-frontend', 'fvp_frontend', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'featured-video-plus-nonce' ),
			'fitvids'  => ! empty( $options['sizing']['wmode'] ) && $options['sizing']['wmode'] == 'auto',
			'dynamic'  => ! empty( $options['usage'] )           && $options['usage']           == 'dynamic',
			'overlay'  => ! empty( $options['usage'] )           && $options['usage']           == 'overlay',
			'opacity'  => '75',
			'loadingw' => FVP_URL . 'css/loading_w.gif',
			'loadingb' => FVP_URL . 'css/loading_b.gif'
		));

		// general frontend styles
		wp_enqueue_style(
			'fvp-frontend',
			FVP_URL . 'css/frontend.css',
			array(),
			FVP_VERSION
		);
	}

	/**
	 * Display featured videos in place of featured images if a featured video is available and only if so desired by user.
	 *
	 * @see http://wordpress.stackexchange.com/a/41858
	 * @since 1.0
	 *
	 * @param string $html featured image html, ready to echo
	 * @param int $post_id id of target post
	 * @param int $post_thumbnail_id id of featured image
	 * @param string|array $size desired size of featured image / video
	 * @param array $attr
	 */
	public function filter_post_thumbnail($html, $post_id, $post_thumbnail_id, $size, $attr) {
		global $_wp_additional_image_sizes;

		$size = $this->get_size();

		$options = get_option( 'fvp-settings' );

		if ( ( isset($options['issingle']) && $options['issingle'] && ! is_single()) ||
		     ( $options['usage']=='manual' || !has_post_video($post_id) ) )
			return $html;

		elseif ($options['usage']=='replace')
			return get_the_post_video($post_id, $size);

		elseif ($options['usage']=='overlay')
			return '<a href="#fvp_'.$post_id.'" class="fvp_overlay" onclick="return false;">'.$html.'</a><div id="fvp_'.$post_id.'" style="display: none;"></div>';

		else//if ($options['usage']=='dynamic')
			return '<a href="#fvp_'.$post_id.'" id="fvp_'.$post_id.'" class="fvp_dynamic" onclick="fvp_dynamic('.$post_id.');return false;">'.$html.'</a>';

	}

	/**
	 * Shortcode for usage in post or page entries. Echos the post's featured video.
	 *
	 * @since 1.0
	 *
	 * @param array $atts can contain the width and/or height how the featured video should be displayed in px, optional
	 */
	function shortcode($atts){
		$w = isset($atts['width'])  ? $atts['width'] : '';
		$h = isset($atts['height']) ? $atts['height'] : '';

		if(has_post_video())
			return get_the_post_video(null, array($w, $h));
	}
}
