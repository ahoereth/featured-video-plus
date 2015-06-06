<?php

class FVP_HTML {

	private static $name = 'fvphtml';


	public static $screens;


	public static function add_screens( $screens = array() ) {
		if ( empty( self::$screens ) && ! empty( $screens ) ) {
			add_action( 'admin_enqueue_scripts', array( get_class(), 'enqueue' ) );
		}

		self::$screens = array_merge( (array) self::$screens, (array) $screens );
	}


	public static function enqueue( $hook ) {
		// only enqueue scripts/styles on the specified screens - if specified
		if ( empty( self::$screens ) || ! in_array( $hook, self::$screens ) ) {
			return;
		}

		// development or production?
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'fvphtml',
			FVP_URL . 'styles/html.css',
			array(
				'wp-pointer',
			),
			FVP_VERSION
		);

		// - colorpicker
		// - tabbed options
		// - conditional showing/hiding options
		wp_enqueue_script(
			'fvphtml',
			FVP_URL . "js/html$min.js",
			array(
				'jquery',
				'iris',
				'wp-pointer',
			),
			FVP_VERSION
		);


		wp_localize_script(
			'fvphtml', // hook
			'fvphtml', // variable name
			array(
				'prefix' => '.fvphtml-',
				'pointers' => self::get_pointers( $hook ),
			)
		);
	}

	/**
	 * Generate an HTML tag. Atributes are escaped. Content is NOT escaped.
	 *
	 * @see    https://github.com/scribu/wp-scb-framework/blob/r60/Util.php#L228
	 *
	 * @param  {string} $tag        tag name
	 * @param  {array}  $attributes attributes
	 * @param  {string} $content    tag content
	 * @return {string} composed HTML
	 */
	public static function html( $tag ) {
		static $SELF_CLOSING_TAGS = array(
			'area',
			'base',
			'basefont',
			'br',
			'hr',
			'input',
			'img',
			'link',
			'meta',
		);

		$args = func_get_args();

		$tag = array_shift( $args );

		if ( isset( $args[0] ) && is_array( $args[0] ) ) {
			$closing = $tag;
			$attributes = array_shift( $args );
			foreach ( $attributes as $key => $value ) {
				if ( false === $value ) {
					continue;
				}

				if ( true === $value ) {
					$value = $key;
				}

				$tag .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
		} else {
			list( $closing ) = explode( ' ', $tag, 2 );
		}

		if ( in_array( $closing, $SELF_CLOSING_TAGS ) ) {
			return "<{$tag} />";
		}

		$content = implode( '', $args );

		return "<{$tag}>{$content}</{$closing}>";
	}


	/**
	 * input
	 *
	 * @param  {string} $name
	 * @param  {string} $type
	 * @param  {array}  $attributes
	 * @return {string}
	 */
	public static function input( $name, $type = 'text', $attributes = array() ) {
		$legal_types = array(
			'text',
			'password',
			'checkbox',
			'radio',
			'color',
			'data',
			'datetime',
			'datetime-local',
			'email',
			'month',
			'number',
			'range',
			'search',
			'tel',
			'time',
			'url',
			'week',
		);

		if ( ! in_array( $type, $legal_types ) ) {
			return '';
		}

		if ( is_string( $attributes ) ) {
			$attributes = array(
				'value' => $attributes,
			);
		}

		$input = self::html(
			'input',
			array_merge( (array) $attributes, array(
				'name' => $name,
				'type' => $type,
			))
		);

		return $input;
	}


	/**
	 * label + input
	 *
	 * @param  {string} $label
	 * @param  {string} $name
	 * @param  {array}  $attributes (optional)
	 * @param  {string} $type       (optional)
	 * @return {string}
	 */
	public static function labeled_input( $label, $name, $attributes = array(), $type = 'text' ) {
		$input = self::input(
			$name,
			$type,
			$attributes
		);

		$span = self::html(
			'span',
			array( 'class' => self::$name . '-innerlabel' ),
			$label
		);

		$label = self::html(
			'label',
			array( 'class' => self::$name . '-label' ),
			$span . $input
		);

		return $label;
	}


	/**
	 * checkbox/radio
	 *
	 * @param  {string}          $name    Input name
	 * @param  {string}          $label   Label for the input field, optional
	 * @param  {string}          $type    Input type (checkbox/radio)
	 * @param  {string/bool/int} $checked
	 * @return {string}
	 */
	public static function tickable( $name, $label = null, $type = 'checkbox', $value = '1', $checked = null ) {
		$legal_types = array( 'checkbox', 'radio' );

		if ( ! in_array( $type, $legal_types ) ) {
			return '';
		}

		$html = self::input(
			$name,
			$type,
			array(
				// there should be an option to force check the tickable using a boolean
				'checked' => $checked == $value,
				'value'   => $value,
			)
		);

		if ( ! empty( $label ) ) {
			$html = self::html(
				'label',
				array( 'class' => self::$name . '-label' ),
				$html . ' ' . $label
			);
		}

		return $html;
	}


	/**
	 * checkbox
	 *
	 * @param  {string}          $name
	 * @param  {string}          $label
	 * @param  {boolean}         $value
	 * @param  {string/bool/int} $checked
	 * @param  {boolean}         $br
	 * @return {string}
	 */
	public static function checkbox( $name, $label = null, $value = '1', $checked = null ) {
		$html = self::tickable(
			$name,
			$label,
			'checkbox',
			$value,
			$checked
		);

		return $html;
	}


	/**
	 * Multiple checkboxes wrapped in a div.
	 *
	 * @param  {string} $wrapper
	 * @param  {assoc}  $options Associative array containing all checkboxes to
	 *                           print in the following manner:
	 *                           name => [ label => 'LABEL', value => 'VALUE' ]
	 *                           Alternatively only name => label pairs, value
	 *                           default is true
	 * @param  {assoc}  $checked Associative array with the option names as keys
	 *                           and their checked conditions as value
	 * @return {string}
	 */
	public static function checkboxes( $wrapper, $options, $checked ) {
		$checkboxes = '';

		foreach ( $options as $name => $data ) {
			$label = is_array( $data ) ? $data['label'] : $data;
			$value = is_array( $data ) ? $data['value'] : '1';

			$checkboxes .= self::checkbox(
				"{$wrapper}[{$name}]",
				$label,
				$value,
				isset( $checked[ $name ] ) ? $checked[ $name ] : null
			);
		}

		return $checkboxes;
	}


	/**
	 * radio
	 *
	 * @param  {string}          $name
	 * @param  {string}          $label
	 * @param  {boolean}         $value
	 * @param  {string/bool/int} $checked
	 * @return {string}
	 */
	public static function radio( $name, $label = null, $value = '1', $checked = null ) {
		$radio = self::tickable(
			$name,
			$label,
			'radio',
			$value,
			$checked
		);

		return $radio;
	}


	/**
	 * Multiple radios wrapped in a div.
	 *
	 * @param  {string} $name
	 * @param  {assoc}           $options Associative array containing value =>
	 *                                    label pairs for all the individual radios.
	 * @param  {string/bool/int} $checked
	 * @return {string}
	 */
	public static function radios( $name, $options, $checked = null ) {
		$radios = '';

		foreach ( $options as $value => $label ) {
			$radios .= self::radio( $name, $label, $value, $checked );
		}

		$wrapped = self::html(
			'div',
			array(
				'class' => implode( ' ', array(
					self::$name . '-radios',
				)),
				'data-name' => $name,
			),
			$radios
		);

		return $wrapped;
	}


	/**
	 *
	 * @see    http://automattic.github.io/Iris/
	 * @since  1.0.0
	 *
	 * @param  {string} $title   input label content
	 * @param  {string} $name    input name
	 * @param  {string} $default default HEX color
	 * @return {string}          Rendered HTML
	 */
	public static function colorpicker( $title, $name, $default = null ) {
		$title = self::html(
			'span',
			array( 'class' => self::$name . '-innerlabel' ),
			$title
		);

		$input = self::input(
			$name,
			'text',
			array(
				'value' => $default,
				'data-default' => $default,
				'class' => self::$name . '-colorpicker',
			)
		);

		$reset = self::html(
			'span',
			array( 'class' => self::$name . '-reset' ),
			'&times;'
		);

		$html = self::html(
			'label',
			array( 'class' => self::$name . '-label' ),
			$title . $input . $reset
		);

		return $html;
	}


	public static function tabbed( $tabs ) {
		// render each tab
		$rendered = array();
		foreach ( $tabs as $title => $content ) {
			$hook = sanitize_file_name( $title );

			// use array unshift to have the titles at the front in the html source
			$rendered[] = self::html(
				'span',
				array(
					'class'     => 'fvphtml-tab-title',
					'data-hook' => $hook,
				),
				$title
			);

			$rendered[] = self::html(
				'div',
				array(
					'class'     => 'fvphtml-tab-body',
					'data-hook' => $hook,
				),
				implode( '', (array) $content )
			);
		}

		// wrap all tabs in a parent container
		$html = self::html(
			'div',
			array( 'class' => 'fvphtml-tabs' ),
			implode( '', $rendered )
		);

		return $html;
	}


	public static function description( $content, $additional_classes = array() ) {
		$html = self::html(
			'p',
			array(
				'class' => implode( ' ', array_merge(
					array( 'description' ),
					(array) $additional_classes
				) )
			),
			$content
		);

		return $html;
	}


	public static function unordered_list( $items, $classes = array() ) {
		$html = '';
		foreach ( $items as $item ) {
			$html .= self::html(
				'li',
				$item
			);
		}

		$html = self::html(
			'ul',
			array( 'class' => implode( ' ', (array) $classes ) ),
			$html
		);

		return $html;
	}


	public static function conditional( $object, $args ) {
		$hidden = ! empty( $args['hidden'] ) ? $args['hidden'] : null;
		unset( $args['hidden'] );

		$name = implode( '|', array_keys( $args ) );
		$value = implode( '|', array_values( $args ) );

		$html = self::html(
			'div',
			array(
				'class' => implode( ' ', array(
					self::$name . '-conditional',
					$hidden ? 'hidden' : '',
				)),
				'data-names'  => $name,
				'data-values' => $value,
			),
			$object
		);

		return $html;
	}


	/**
	 * Generates the list of pointers using the 'fvphtml_pointers' filter.
	 *
	 * @param  {string} $hook Current page hook for prefiltering pointers using
	 *                        the filter.
	 * @return {array}  Array of pointers ready to throw into wp_localize_script.
	 */
	public static function get_pointers( $hook = null ) {
		/**
		 * 'fvphtml_pointers' filter
		 *
		 * Expects:
		 * array(
		 *   'identifier' {string} => assoc(
		 *     'target' => {string} (jQuery selector) [required],
		 *     'title' => {string} [optional, default: ''],
		 *     'content' => {string} [optional, default: ''],
		 *     'position' => array(
		 *       'edge' => {string} [optional, default: 'right'],
		 *       'align' => {string} [optional, default: 'middle'],
		 *     )
		 *   )
		 * )
		 */
		$unfiltered_pointers = apply_filters( 'fvphtml_pointers', array(), $hook );

		// Get already dismissed pointers from database.
		$dismissed_pointers = explode(',', (string) get_user_meta(
			get_current_user_id(),
			'dismissed_wp_pointers',
			true
		));

		// Filter out already dismissed pointers.
		$filtered_pointers = array_diff(
			array_keys( $unfiltered_pointers ),
			$dismissed_pointers
		);

		// Create nice array of not yet dismissed pointers.
		$pointers = array();
		foreach ( $filtered_pointers AS $pointer ) {
			$unfiltered_pointers[ $pointer ][ 'identifier' ] = $pointer;
			$pointers[] = $unfiltered_pointers[ $pointer ];
		}

		return $pointers;
	}


	/**
	 * Translates a given array into a ready-to-use HTML class-attribute or its
	 * value.
	 *
	 * @param  {assoc/array} $assoc     If assoc: classname/condition pairs.
	 *                                  If array: classnames.
	 * @param  {boolean} $attribute     If the classnames should be wrapped in a
	 *                                  'class' attribute.
	 * @param  {boolean} $leadingspace  If the result string should start with
	 *                                  a space.
	 * @param  {boolean} $trailingspace If the result string should end with a
	 *                                  space.
	 * @return {string}
	 */
	public static function class_names(
		$assoc,
		$attribute = false,
		$leadingspace = false,
		$trailingspace = false
	) {
		// Attribute opening and leading space.
		$string  = $leadingspace ? ' ' : '';
		$string .= $attribute ? 'class="' : '';

		// Class list.
		$classes = array();
		foreach ( $assoc AS $key => $val ) {
			if ( $val ) {
				$classes[] = $key;
			}
		}
		$string .= implode( ' ', $classes );

		// Closing the attribute and trailing space.
		$string .= $attribute ? '"' : '';
		$string .= $trailingspace ? ' ' : '';

		return $string;
	}


	/**
	 * Translates a given array into a ready-to-use HTML style-attribute.
	 *
	 * @param  {assoc}   $assoc         Associative array of CSS property/value
	 *                                  pairs or associative array of CSS
	 *                                  properties WITH values as key and
	 *                                  boolean conditions as value.
	 * @param  {boolean} $attribute     If the resulting style string should be
	 *                                  wrapped in a 'style' attribute.
	 * @param  {boolean} $leadingspace  If the resulting string should start with
	 *                                  a space.
	 * @param  {boolean} $trailingspace If the resulting string should end with a
	 *                                  space.
	 * @return {string}
	 */
	public static function inline_styles(
		$assoc,
		$attribute = false,
		$leadingspace = false,
		$trailingspace = false
	) {
		// Attribute opening and leading space.
		$string  = $leadingspace ? ' ' : '';
		$string .= $attribute ? 'style="' : '';

		// Style body.
		foreach ( $assoc AS $key => $val ) {
			if ( is_bool( $val ) && true === $val ) {
				// $key is a property: value pair and $val a boolean condition
				$string .= esc_attr( $key ) . '; ';
			} else {
				// $key is a property and $val a value
				$string .= sprintf( '%s: %s; ', esc_attr( $key ), esc_attr( $val ) );
			}
		}

		// Closing the attribute and trailing space.
		$string .= $attribute ? '"' : '';
		$string .= $trailingspace ? ' ' : '';

		return $string;
	}


}
