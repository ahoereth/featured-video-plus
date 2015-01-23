<?php
/**
 * Class providing additional functionality to WordPress' native oEmbed
 * functionality.
 *
 * @link http://codex.wordpress.org/oEmbed oEmbed Codex Article
 * @link http://oembed.com/ oEmbed Homepage
 *
 * @package Featured Video Plus
 * @subpackage oEmbed
 * @since 2.0.0
 */
class FVP_oEmbed {
	private $super;

	public $time;

	public function __construct() {
		// Does not extend oEmbed in order to not initialize it a second time.
		require_once( ABSPATH . '/' . WPINC . '/class-oembed.php' );
		$this->super = _wp_oembed_get_object();

		$this->time = time();

		add_filter('oembed_fetch_url', array($this, 'additional_arguments'), 10, 3);
	}


	/**
	 * Call methods from 'super' class if they don't exist here.
	 *
	 * @param  {string} $method
	 * @param  {array}  $args
	 * @return {}               Whatever the other method returns.
	 */
	public function __call( $method, $args ) {
		return call_user_func_array( array( $this->super, $method ), $args );
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
		$raw = $this->super->fetch(
			$this->super->get_provider( $url ),
			$url,
			array(
				'width'  => 4096,
				'height' => 4096
			)
		);

		return ! empty( $raw ) ? $raw : false;
	}


	public function get_html( $url, $args = array(), $provider = null ) {
		$html = $this->super->get_html( $url, $args );

		if ( empty( $provider ) ) {
			return $html;
		}

		$args = $this->filter_legal_args( $provider, $args );

		// Some providers do not provide it's player API to oEmbed requests,
		// therefore the plugin needs to manually interfere with their iframe's
		// source URL..
		switch ( $provider ) {

			// YouTube.com
			case 'youtube':
				$hook = '?feature=oembed';
				$parameters = add_query_arg( $args, $hook );
				$html = str_replace( $hook, $parameters, $html );
				break;

			// DailyMotion.com
			case 'dailymotion':
				$args = $this->translate_time_arg( $args );

				$parameters = add_query_arg( $args, '' );

				$pattern     = "/src=([\"'])([^\"']*)[\"']/";
				$replacement = 'src=$1$2' . $parameters . '$1';
				$html = preg_replace($pattern, $replacement, $html);
				break;
		}

		return $html;
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
		// Only oEmbed requests from inside the plugin are allowed to have
		// additional parameters!
		if ( ! empty( $args['fvp'] ) && $args['fvp'] == $this->time ) {
			// Unset plugin internal and default arguments.
			unset(
				$args['fvp'],
				$args['width'],
				$args['height'],
				$args['discover']
			);

			$provider = add_query_arg( $args, $provider );
		}

		return $provider;
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
		$pattern = "/(?:www\.)?(.*)\.".$tlds."/";

		preg_match( $pattern , $host, $match );

		if ( ! empty( $match[1] ) ) {
			return strtolower( $match[1] );
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
			),
		);

		$result = array();

		if ( ! empty( $legals[ $provider ] ) ) {
			foreach ( $legals[ $provider ] as $key ) {
				if ( ! empty( $args[ $key ] ) ) {
					$result[ $key ] = $args[ $key ];
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
		$query = parse_url($url, PHP_URL_QUERY);
		$query_args = array();
		parse_str($query, $query_args);

		// parse fragment
		$fragment = parse_url($url, PHP_URL_FRAGMENT);
		$fragment_args = array();
		parse_str($fragment, $fragment_args);

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
	 * 	##m##s
	 * 	##m
	 * 	##s
	 * 	##
	 *
	 * @since  2.0.0
	 *
	 * @param  {string} $t
	 * @return {int}    seconds
	 */
	private function handle_m_s_string( $t ) {
		$seconds = 0;

		preg_match('/(\d+)m/', $t, $m);
		if ( ! empty( $m[1] ) ) {
			$seconds += $m[1]*60;
		}

		preg_match('/(\d+)s/', $t, $s);
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
}
