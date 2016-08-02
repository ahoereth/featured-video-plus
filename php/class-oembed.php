<?php
/**
 * Class providing additional functionality to WordPress' native oEmbed
 * functionality.
 *
 * @link http://codex.wordpress.org/oEmbed
 * @link http://oembed.com/ oEmbed Homepage
 * @link https://github.com/WordPress/WordPress/tree/master/wp-includes/class-oembed.php
 *
 * @package Featured Video Plus
 * @subpackage oEmbed
 * @since 2.0.0
 */
class FVP_oEmbed {
	private $oembed;

	public function __construct() {
		// Does not extend oEmbed in order to not initialize it a second time.
		require_once( ABSPATH . '/' . WPINC . '/class-oembed.php' );
		$this->oembed = _wp_oembed_get_object();

		add_filter(
			'oembed_fetch_url', array( $this, 'additional_arguments' ), 10, 3
		);
	}


	/**
	 * Call methods from 'oembed' class if they don't exist here.
	 *
	 * @param  {string} $method
	 * @param  {array}  $args
	 * @return {}               Whatever the other method returns.
	 */
	public function __call( $method, $args ) {
		return call_user_func_array( array( $this->oembed, $method ), $args );
	}


	/**
	 * Utilizes the WordPress oembed class for fetching the oembed info object.
	 *
	 * @see   http://oembed.com/
	 * @since 2.0.0
	 */
	public function request( $url ) {
		// fetch the oEmbed data with some arbitrary big size to get the biggest
		// thumbnail possible.
		$raw = $this->oembed->fetch(
			$this->get_provider( $url ),
			$url,
			array(
				'width'  => 4096,
				'height' => 4096,
			)
		);

		return ! empty( $raw ) ? $raw : false;
	}


	/**
	 * The do-it-all function that takes a URL and attempts to return the HTML.
	 *
	 * @see WP_oEmbed::get_html()
	 *
	 * @param {string} $url The URL to the content that should be embedded.
	 * @param {array}  $args Optional arguments. Usually passed from a shortcode.
	 * @return {false/string} False on failure, other the embed HTML.
	 */
	public function get_html( $url, $args = array(), $provider = null ) {
		if ( $provider ) {
			$args = $this->filter_legal_args( $provider, $args );
		}

		$html = $this->oembed->get_html( $url, $args );

		if ( empty( $provider ) ) {
			return $html;
		}

		// Some providers do not provide it's player API to oEmbed requests,
		// therefore the plugin needs to manually interfere with their iframe's
		// source URL..
		switch ( $provider ) {

			// YouTube.com
			case 'youtube':
				// Add `origin` paramter.
				$args['origin'] = urlencode( get_bloginfo( 'url' ) );

				// The loop parameter does not work with single videos if there is no
				// playlist defined. Workaround: Set playlist to video itself.
				// @see https://developers.google.com/youtube/player_parameters#loop-supported-players
				if ( ! empty( $args['loop'] ) && $args['loop'] ) {
					$args['playlist'] = $this->get_youtube_video_id( $url );
				}

				// Remove fullscreen button manually because the YouTube API
				// does not care about `&fs=0`.
				if ( array_key_exists( 'fs', $args ) && $args['fs'] == 0 ) {
					$html = str_replace( 'allowfullscreen', '', $html );
				}

				// We strip the 'feature=oembed' from the parameters because it breaks
				// some parameters.
				$hook = '?feature=oembed';
				$html = str_replace( $hook, '', $html );
				break;

			// DailyMotion.com
			case 'dailymotion':
				$args = $this->translate_time_arg( $args );
				break;
		}

		if ( ! empty( $args ) ) {
			$pattern = "/src=([\"'])([^\"']*)[\"']/";
			preg_match( $pattern, $html, $match );
			if ( ! empty( $match[1] ) && ! empty( $match[2] ) ) {
				$code = $this->clean_url( $match[2] );
				$replace = sprintf( 'src=$1%s$1', add_query_arg( $args, $code ) );
				$html = preg_replace( $pattern, $replace, $html );
			}
		}

		return $html;
	}


	/**
	 * Cleans up ? and & in urls such that before all & there is a single ?
	 * with none following.
	 *
	 * @see wordpress.org/support/topic/fix-for-wordpress-442-for-youtube-video-error
	 *
	 * @param {string} $urls
	 * @return {string}
	 */
	public function clean_url($url) {
		$url = preg_replace( '/\?/', '&', $url, 1);
		return preg_replace( '/&/',  '?', $url, 1);
	}


	/**
	 * Enable additional parameters for oEmbed requests from inside the plugin.
	 * The plugin only allows a limited set of parameters.
	 *
	 * @see    https://core.trac.wordpress.org/ticket/16996#comment:18
	 * @param  {string} $provider The oEmbed provider (as URL)
	 * @param  {string} $url      The oEmbed request URL
	 * @param  {assoc}  $args     The additional parameters for the provider
	 * @return {string}           $provider with added parameters.
	 */
	public function additional_arguments( $provider, $url, $args ) {
		unset(
			$args['width'],
			$args['height'],
			$args['discover']
		);

		return add_query_arg( $args, $provider );;
	}


	/**
	 * Extracts the arguments (query and fragment) from a given URL and filters
	 * them to only contain arguments legal for the given provider.
	 *
	 * @since  2.0.0
	 *
	 * @param  {string} $url           oEmbed request URL
	 * @param  {string} $provider      Provider name (optional)
	 * @param  {bool}   $filter_legals If the retrieved arguments should be
	 *                                 filtered to only contain legals
	 * @return {assoc}  Associative array containing the arguments as key value
	 *                  pairs
	 */
	public function get_args( $url, $provider = null, $filter_legals = true ) {
		$args = $this->parse_url_args( $url );

		if ( $filter_legals ) {
			$provider = empty( $provider ) ?
				$this->get_provider_name( $url ) :
				$provider;

			$args = $this->filter_legal_args( $provider, $args );
		}

		return $args;
	}


	/**
	 * Extracts the provider name in lowercase from a given URL.
	 *
	 * @since  2.0.0
	 * @param  {string} $url oEmbed request URL
	 * @return {string}      Provider name
	 */
	public function get_provider_name( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );

		$tlds_set = array(
			'com',
			'net',
			'tv',
		);

		$tlds = '(?:' . implode( ')|(?:', $tlds_set ) . ')';
		$pattern = '/(?:www\.)?(.*)\.' . $tlds  . '/';

		preg_match( $pattern , $host, $match );

		if ( ! empty( $match[1] ) ) {
			return strtolower( $match[1] );
		}

		return false;
	}


	/**
	 * Takes a URL and returns the corresponding oEmbed provider's URL, if there
	 * is one.
	 *
	 * Backport from WordPress 4.0.0 to make this method available for earlier
	 * WordPress releases.
	 * @see https://github.com/WordPress/WordPress/blob/ed4aafa6929b36dc1d06708831a7cef258c16b54/wp-includes/class-oembed.php#L188-L226
	 *
	 * @param string        $url  The URL to the content.
	 * @param string|array  $args Optional provider arguments.
	 * @return false|string False on failure, otherwise the oEmbed provider URL.
	 */
	public function get_provider( $url, $args = '' ) {
		// If the WordPress native oembed class has the get_provider function,
		// use that one.
		if ( method_exists( $this->oembed, 'get_provider' ) ) {
			return $this->oembed->get_provider( $url, $args );
		}

		$args = wp_parse_args( $args );

		$provider = false;
		if ( ! isset( $args['discover'] ) ) {
			$args['discover'] = true;
		}

		foreach ( $this->oembed->providers AS $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( ! $regex ) {
				$matchmask = '#' . str_replace(
					'___wildcard___',
					'(.+)',
					preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' )
				) . '#i';
				$matchmask = preg_replace(
					'|^#http\\\://|', '#https?\://', $matchmask
				);
			}

			if ( preg_match( $matchmask, $url ) ) {
				// JSON is easier to deal with than XML
				$provider = str_replace( '{format}', 'json', $providerurl );
				break;
			}
		}

		if ( ! $provider && $args['discover'] ) {
			$provider = $this->oembed->discover( $url );
		}

		return $provider;
	}


	/**
	 * Get video id from url.
	 *
	 * @param  {string} $url
	 * @return {string/bool} Video ID or false on error.
	 */
	public function get_video_id( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$provider = $this->get_provider_name( $url );
		if ( empty( $provider ) ) {
			return false;
		}

		switch ( $provider ) {
			case 'dailymotion':
				return strtok( basename( $url ), '_' );
				break;
		}

		return false;
	}


	/**
	 * Get video thumbnail url by provider and video id.
	 *
	 * @param  {string} $provider
	 * @param  {string} $id
	 * @return {string/bool} Video URL or false on error.
	 */
	public function get_thumbnail_url( $provider, $id ) {
		static $thumbnail_apis = array(
			'dailymotion' =>
				'https://api.dailymotion.com/video/%s?fields=thumbnail_url,poster_url',
		);

		if ( empty( $provider ) || empty( $id ) ) {
			return false;
		}

		$result = @file_get_contents( sprintf( $thumbnail_apis[$provider], $id ) );
		if ( ! empty( $result ) ) {
			switch ( $provider ) {
				case 'dailymotion':
					$data = json_decode( $result, true );
					return ! empty( $data['thumbnail_url'] ) ?
						$data['thumbnail_url'] : $data['poster_url'];
					break;
			}
		}

		return false;
	}


	/**
	 * Only keeps key value pairs of the source $array if their keys are listed
	 * in the $filter array.
	 *
	 * @since  2.0.0
	 *
	 * @param  {assoc} $array  Associative array to filter
	 * @param  {array} $filter Enumerated array containing the legal keys to keep
	 * @return {assoc} Filtered array
	 */
	private function filter_legal_args( $provider, $args ) {
		$legals = array(
			'default' => array(
				'width',
				'height',
			),

			// YouTube.com
			// https://developers.google.com/youtube/player_parameters
			'youtube' => array(
				'autohide',
				'autoplay',
				'cc_load_policy',
				'color',
				'controls',
				'disablekb',
				'enablejsapi',
				'end',
				'fs',
				'hl',
				'iv_load_policy',
				'list',
				'listType',
				'loop',
				'modestbranding',
				'origin',
				'playerapiid',
				'playlist',
				'playsinline',
				'rel',
				'showinfo',
				'start',
				'theme',
			),

			// Vimeo.com
			// http://developer.vimeo.com/apis/oembed
			'vimeo' => array(
				'byline',
				'title',
				'portrait',
				'color',
				'autoplay',
				'autopause',
				'loop',
				'api',
				'player_id',
			),

			// DailyMotion.com
			// http://www.dailymotion.com/doc/api/player.html
			'dailymotion' => array(
				'wmode',
				'autoplay',
				'api',
				'background',
				'chromeless',
				'controls',
				'foreground',
				'highlight',
				'html',
				'id',
				'info',
				'logo',
				'network',
				'quality',
				'related',
				'startscreen',
				'start',
				't',
				'syndication',
			),

			// SoundCloud.com
			// https://developers.soundcloud.com/docs/oembed
			'soundcloud' => array(
				'auto_play',
				'auto_play' => 'autoplay', // map autoplay to auto_play
				'color',
				'show_comments',
			),
		);

		$result = array();

		if ( ! empty( $legals[ $provider ] ) ) {
			$combinedlegals = array_merge( $legals['default'], $legals[ $provider ] );
			foreach ( $combinedlegals AS $key => $val ) {
				if ( array_key_exists( $val, $args ) && ! is_null( $args[ $val ] ) ) {
					$key = is_numeric( $key ) ? $val : $key;
					$result[ $key ] = urlencode( $args[ $val ] );
				}
			}
		}

		return $result;
	}


	/**
	 * Function used for retrieving query (?..&..) and fragment (#..) arguments
	 * of a given URL.
	 *
	 * @see    http://php.net/manual/en/function.parse-url.php
	 * @see    http://php.net/manual/en/function.parse-str.php
	 * @since  2.0.0
	 *
	 * @param  {string} $url the URL to parse for arguments
	 * @return array containing query and fragment arguments
	 */
	private function parse_url_args( $url ) {
		// parse query
		$query = parse_url( $url, PHP_URL_QUERY );
		$query_args = array();
		parse_str( $query, $query_args );

		// parse fragment
		$fragment = parse_url( $url, PHP_URL_FRAGMENT );
		$fragment_args = array();
		parse_str( $fragment, $fragment_args );

		// merge query and fragment args
		$args = array_merge(
			$query_args,
			$fragment_args
		);

		return $args;
	}


	/**
	 * Calculates the amount of seconds depicted by a string structured like one
	 * of the following possibilities:
	 *   ##m##s
	 *   ##m
	 *   ##s
	 *   ##
	 *
	 * @since  2.0.0
	 *
	 * @param  {string} $t
	 * @return {int}    seconds
	 */
	private function handle_m_s_string( $t ) {
		$seconds = 0;

		preg_match( '/(\d+)m/', $t, $m );
		if ( ! empty( $m[1] ) ) {
			$seconds += $m[1] * 60;
		}

		preg_match( '/(\d+)s/', $t, $s );
		if ( ! empty( $s[1] ) ) {
			$seconds += $s[1];
		}

		if ( empty( $m[1] ) && empty( $s[1] ) ) {
			$seconds += intval( $t );
		}

		return $seconds;
	}


	/**
	 * Translates a source time parameter (mostly given as 't' in the
	 * fragment (#..) of an URL in seconds to a destination parameter.
	 *
	 * Note: The source parameter overwrites the destination parameter!
	 *
	 * @since  2.0.0
	 *
	 * @param  {array}  $parameters Array of parameters, containing $src
	 * @param  {string} $src        Key of the source parameter
	 * @param  {string} $dst        Key of the destination parameter
	 * @return {array}  Updated $parameters array
	 */
	private function translate_time_arg( $args, $src = 't', $dst = 'start' ) {
		if ( ! empty( $args[ $src ] ) ) {
			$t = $this->handle_m_s_string( $args[ $src ] );

			unset( $args[ $src ] );

			if ( $t ) {
				$args[ $dst ] = $t;
			}
		}

		return $args;
	}


	/**
	 * Extract the YouTube Video ID from an URL.
	 *
	 * @see http://stackoverflow.com/a/6382259/1447384
	 *
	 * @param  {string} $url
	 * @return {string} YouTube ID
	 */
	private function get_youtube_video_id( $url ) {
		$pattern =
		 	'/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)'.
			'|youtu\.be\/)([^"&?\/ ]{11})/i';

		if ( preg_match( $pattern, $url, $match ) ) {
			return $match[1];
		}

		return false;
	}
}
