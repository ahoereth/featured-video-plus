<?php

// dependencies
require_once( FVP_DIR . 'php/class-html.php' );

/**
 * Class for handling help tabs.
 */
class FVP_Help {

	public function __construct() {
		add_action( 'load-options-media.php', array( $this, 'functions' ) );
		add_action( 'load-options-media.php', array( $this, 'shortcode' ) );
		add_action( 'load-post.php',          array( $this, 'post' ), 20 );
	}


	/**
	 * FVP post edit screen help tab.
	 */
	public function post() {
		$screen = get_current_screen();
		$title = esc_html__( 'Featured Video Plus', 'featured-video-plus' );
		$content = array();

		// Tab Headline
		$content[] = FVP_HTML::html( 'h3', $title );

		// oEmbed
		$content[] = FVP_HTML::html( 'p', sprintf(
			esc_html__(
				'Take a video url from one of the %ssupported oembed providers%s and paste it into the Featured Video input field.',
				'featured-video-plus'
			),
			'<a href="https://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank" rel="noopener noreferrer">',
			'</a>'
		) );

		// Local
		$content[] = FVP_HTML::html( 'p', sprintf(
			esc_html__(
				'Alternatively you can select one of the videos from your media library using the small media icon to the right in the URL input field. The plugin makes use of %sWordPress\' native HTML5 video functionality%s - no gurantee for compatibility with all formats.',
				'featured-video-plus'
			),
			'<a href="http://mediaelementjs.com/#browsers">',
			'</a>'
		) );

		// Uploading
		$content[] = FVP_HTML::html( 'h4',
			array( 'style' => 'margin-bottom: 0;' ),
			esc_html__( 'Fixing upload errors', 'featured-video-plus' ) . ':'
		);

		$content[] = FVP_HTML::html( 'p',
			array( 'style' => 'margin-bottom: 0;' ),
			sprintf(
				esc_html__(
					'Read %sthis%s on how to increase the maximum file upload size.',
					'featured-video-plus'
				),
				'<a href="https://goo.gl/yxov27" target="_blank" rel="noopener noreferrer">',
				'</a>'
			)
		);

		// Register tab.
		$screen->add_help_tab( array(
			'id'      => 'featured-video-plus',
			'title'   => $title,
			'content' => implode( '', $content ),
		) );
	}


	/**
	 * FVP PHP-Functions help tab.
	 */
	public function functions() {
		$screen = get_current_screen();

		$title = 'Featured Video Plus: '. esc_html__(
			'PHP-Functions',
			'featured-video-plus'
		);

		$content = array();

		// Tab Headline
		$content[] = FVP_HTML::html( 'h3', $title );

		// PHP functions
		$content[] = FVP_HTML::unordered_list( array(
			'<code>the_post_video( $size )</code>',
			'<code>has_post_video( $post_id )</code>',
			'<code>get_the_post_video( $post_id, $size )</code>',
			'<code>get_the_post_video_url( $post_id )</code>',
			'<code>get_the_post_video_image_url( $post_id )</code>',
			'<code>get_the_post_video_image( $post_id )</code>',
		) );

		// PHP function explanations
		$content[] = FVP_HTML::html( 'p', sprintf(
			esc_html_x(
				'All parameters are optional. If %1$s the current post\'s id will be used. %2$s is either a string %2$s or a 2-item array representing width and height in pixels, e.g. array(32,32).',
				'%1$s is a boolean condition, \"post_id == null\", %2$s is a PHP variable, %2$s is a list of strings in paranthesis.',
				'featured-video-plus'
			),
			'<code>post_id == null</code>',
			'<code>$size</code>',
			'(<code>thumbnail</code>, <code>medium</code>, <code>large</code> ' . esc_html__( 'or', 'featured-video-plus' ) . ' <code>full</code>)'
		) );

		$content[] = FVP_HTML::html( 'p', sprintf(
			esc_html__(
				'The functions are implemented corresponding to the original %sfunctions%s: They are intended to be used and to act the same way. Take a look into the WordPress Codex for further guidance:',
				'featured-video-plus'
			),
			'<a href="https://codex.wordpress.org/Post_Thumbnails#Function_Reference" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Featured Image' ) . '&nbsp;',
			'</a>'
		) );

		// WordPress Featured Image functions
		$content[] = FVP_HTML::unordered_list( array(
			'<code><a href="https://developer.wordpress.org/reference/functions/the_post_thumbnail/" target="_blank" rel="noopener noreferrer">get_the_post_thumbnail</a></code>',
			'<code><a href="https://developer.wordpress.org/reference/functions/wp_get_attachment_image/" target="_blank" rel="noopener noreferrer">wp_get_attachment_image</a></code>',
		) );

		// Register tab.
		$screen->add_help_tab( array(
			'id'      => 'fvp_help_functions',
			'title'   => $title,
			'content' => implode( '', $content )
		) );
	}


	/**
	 * FVP Shortcode help tab.
	 */
	public function shortcode() {
		$screen = get_current_screen();
		$title = esc_html__(
			'Featured Video Plus: Shortcode',
			'featured-video-plus'
		);

		$content = array();

		// Tab Headline
		$content[] = FVP_HTML::html( 'h3', $title );

		$content[] = FVP_HTML::unordered_list( array(
			'<code>[featured-video-plus]</code><br />' .
				'<span>' .
					esc_html__( 'Displays the video in its default size.', 'featured-video-plus' ) .
				'</span>',
			'<code>[featured-video-plus width=560]</code><br />' .
				'<span>' .
					esc_html__( 'Displays the video with a width of 300 pixel. Height will be fitted such that the aspect ratio is preserved.', 'featured-video-plus' ) .
				'</span>',
			'<code>[featured-video-plus width=560 height=315]</code><br />' .
				'<span>' .
					esc_html__( 'Displays the video with a fixed width and height.', 'featured-video-plus' ) .
				'</span>',
		) );

		// Register tab.
		$screen->add_help_tab( array(
			'id'      => 'fvp_help_shortcode',
			'title'   => $title,
			'content' => implode( '', $content ),
		) );
	}

}
