jQuery(document).ready(function($) {

  var prefix = '.fvphtml';

  /**
   * Filters a given set of jQuery elements by a specific HTML5 data attribute.
   *
   * @param  {jQuery collection} $set  jQuery element set to filter
   * @param  {string}            key   data key to filter by
   * @param  {string}            value desired value
   * @return {jQuery collection} Collection only containing elements with the
   *                             desired key value data pair.
   */
  $.filterByData = function( $set, key, value ) {
    return $set.filter(function( key, value ) {
      return $( this ).data( key ) == value;
    });
  };


  // ****************************************
  // COLORPICKERS / IRIS
  // See http://automattic.github.io/Iris/

  // get colorpickers
  var $colorpickers = $( prefix + '-colorpicker' );

  // initialize functionality
  $colorpickers
    .iris({

      /**
       * Update input background and text color live.
       */
      change: function( event, ui ) {
        var color = ui.color.toString().substr( 1 );

        var r = parseInt( color.substr( 0, 2), 16 ),
            g = parseInt( color.substr( 2, 2), 16 ),
            b = parseInt( color.substr( 4, 2 ), 16 );
        var yiq = ( ( r * 299 ) + ( g * 587 ) + ( b * 114 ) ) / 1000;

        $(this).css({
          backgroundColor: '#' + color,
          color: ( yiq >= 128 ) ? '#000' : '#fff'
        });
      }
    })
    .click(function() {
      $this = $( this );

      // hide all other colorpickers when a single instance is opened
      $colorpickers.not( $this ).iris( 'hide' );
      $this.iris( 'show' );
    });


  // ****************************************
  // TABBED OPTIONS

  // get tabs
  var $tabs = $( prefix + '-tabs');

  // initialize every instance's functionality
  for ( var i = $tabs.length - 1; i >= 0; i-- ) {
    var $tab = $( $tabs[i] );

    // get titles and bodys
    var $titles = $tab.children( prefix + '-tab-title' );
    var $bodys  = $tab.children( prefix + '-tab-body' );

    // first title/body pair is active on initiation
    $titles.first().addClass('active');
    $bodys.first().addClass('active');

    // pull titles to top
    $tab.prepend( $titles );

    // hide all but the initially active content
    $bodys.filter( ':not(.active)' ).hide();

    // initialize title click event
    $tab.children( prefix + '-tab-title' ).click(function() {
      var $title = $( this );
      var $body  = $bodys.filter( "[data-hook='" + $title.data( 'hook' ) + "']" );

      // current active title is not clickable
      if ( $title.hasClass( 'active' ) && $body.hasClass( 'active' ) ) {
        return;
      }

      // no longer active
      $titles.removeClass( 'active' );
      $bodys.removeClass( 'active' ).slideUp();

      // newly active
      $title.addClass( 'active' );
      $body.addClass( 'active' ).slideDown();
    });
  }


  // ****************************************
  // CONDITIONALS
  // TODO: needs rewrite

  $conditionals = $( prefix + '-conditional' );
  $conditional_targets = [];

  for ( var i = $conditionals.length - 1; i >= 0; i-- ) {
    var $conditional = $( $conditionals[i] );
    var name  = $conditional.data( 'name' );

    $conditional_targets.push( $( "[name='" + name + "']" ) );
  }

  for ( var i = $conditional_targets.length - 1; i >= 0; i-- ) {
    var $target = $( $conditional_targets[i] );

    $target.change(function() {
      $this = $( this );

      var $focused = $conditionals.filter( "[data-name='" + $this.attr( 'name' ) + "']");

      for ( var j = $focused.length - 1; j >= 0; j-- ) {
        var $conditional = $( $focused[j] );

        var operator     = true,
            newValue     = $this.attr('type') == 'checkbox' && ! $this.prop('checked') ? null : $this.val(),
            desiredValue = $conditional.data( 'value' );

        if ( typeof desiredValue == 'string' && desiredValue.charAt( 0 ) == '!' ) {
          operator     = false;
          desiredValue = desiredValue.substr( 1 );
        }

        if ( (   operator && newValue != desiredValue ) ||
             ( ! operator && newValue == desiredValue ) ) {
          $conditional.addClass( 'hidden' );
        } else {
          $conditional.removeClass( 'hidden' );
        }
      }
    });
  }

});
