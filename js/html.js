(function($) {
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
  $.filterByData = function($set, key, value) {
    return $set.filter(function(key, value) {
      return $(this).data(key) === value;
    });
  };


  // ****************************************
  // TABBED OPTIONS

  $(document).ready(function() {
    // get tabs
    var $tabs = $(prefix + '-tabs');

    // initialize every instance's functionality
    for ( var i = $tabs.length - 1; i >= 0; i-- ) {
      var $tab = $( $tabs[i] );

      // get titles and bodys
      var $titles = $tab.children(prefix + '-tab-title');
      var $bodys  = $tab.children(prefix + '-tab-body');

      // first title/body pair is active on initiation
      $titles.first().addClass('active');
      $bodys.first().addClass('active');

      // pull titles to top
      $tab.prepend( $titles );

      // hide all but the initially active content
      $bodys.filter(':not(.active)').hide();

      // initialize title click event
      $tab.children(prefix + '-tab-title').click(function() {
        var $title = $(this);
        var $body  = $bodys.filter('[data-hook=\'' + $title.data('hook') + '\']');

        // current active title is not clickable
        if ($title.hasClass('active') && $body.hasClass('active')) {
          return;
        }

        // no longer active
        $titles.removeClass('active');
        $bodys.removeClass('active').slideUp();

        // newly active
        $title.addClass('active');
        $body.addClass('active').slideDown();
      });
    }
  });


  // ****************************************
  // CONDITIONALS
  // TODO: needs rewrite

  var triggers = {};
  function conditionalTriggered() {
    var $trigger = $(this);
    var targets = triggers[ $trigger.attr('name') ];
    for (var i = 0; i < targets.length; i++) {
      var $target = $( targets[i] );

      var targetNames = $target.data('names').split('|');
      var targetValues = $target.data('values').split('|');

      var index = $.inArray($trigger.attr('name'), targetNames);
      if (-1 === index) {
        continue;
      }

      var operator = true;
      var desiredValue = targetValues[ index ];
      var newValue = $trigger.attr('type') === 'checkbox' &&
                     ! $trigger.prop('checked') ? null : $trigger.val();

      if (
        typeof desiredValue === 'string' &&
        desiredValue.charAt(0) === '!'
      ) {
        operator = false;
        desiredValue = desiredValue.substr(1);
      }

      if ((  operator && newValue !== desiredValue) ||
          (! operator && newValue === desiredValue)) {
        $target.addClass('hidden');
      } else {
        $target.removeClass('hidden');
      }
    }
  }

  $(document).ready(function() {
    var $conditionals = $( prefix + '-conditional' );
    for (var i = 0; i < $conditionals.length; i++) {
      var $target = $( $conditionals[i] );
      var names = $target.data('names').split('|');
      for (var j = 0; j < names.length; j++) {
        var name = names[j];
        if (! triggers.hasOwnProperty(name)) {
          triggers[ name ] = [];
        }
        triggers[ name ].push($target);
      }
    }

    for (var trigger in triggers) {
      var $trigger = $('[name=\'' + trigger + '\']');
      $trigger.change(conditionalTriggered);
    }
  });


  // ***************************************************************************
  // COLORPICKERS / IRIS
  // See http://automattic.github.io/Iris/
  (function() {
    var $colorpickers;


    /**
     * Returns either white (#ffffff) or black (#000000) depending on the
     * contrast to the given background color.
     *
     * @param  {string} color
     * @return {string}
     */
    function getContrastColor(color) {
      color = ! color ? '#fffff' : color;
      color = ('#' === color.charAt(0)) ? color.substr(1) : color;

      var r = parseInt(color.substr(0, 2), 16),
          g = parseInt(color.substr(2, 2), 16),
          b = parseInt(color.substr(4, 2), 16);

      var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;

      return ( yiq >= 128 ) ? '#000' : '#fff';
    }


    /**
     * Converts a 3 digit hex to a 6 digit hex including #.
     * @param  {string} hex
     * @return {string}
     */
    function cleanHex(hex) {
      if (hex.length === 3 && '#' !== hex.charAt(0)) {
        hex = '#' + hex;
      }

      if (hex.length === 4 && '#' === hex.charAt(0)) {
        hex = '#' + hex.charAt(1) + hex.charAt(1) +
                    hex.charAt(2) + hex.charAt(2) +
                    hex.charAt(3) + hex.charAt(3);
      }

      return 7 === hex.length ? hex : false;
    }


    /**
     * Adjust colorpicker input background and foreground color depending
     * on its value.
     */
    function colorpickerChange(event, ui) {
      var $this = $(this);
      var color = ui && ui.color ? ui.color.toString() : $this.val();
      color = cleanHex(color);

      $this.css({
        backgroundColor: color ? color : '#ffffff',
        color: getContrastColor(color)
      });

      if (! color) { $this.siblings(prefix + '-reset').hide(); }
      else         { $this.siblings(prefix + '-reset').show(); }
    }


    /**
     * Hide all colorpickers upon opening a new one.
     */
    function colorpickerClick() {
      var $this = $(this);
      $colorpickers.not( $this ).iris('hide');
      $this.iris('show');
    }


    /**
     * Hide colorpicker reset button on blur.
     */
    function colorpickerBlur(event) {
      if (event) { event.preventDefault(); }
      var $this = $(this);
      if ('' === $this.val()) {
        $this.siblings(prefix + '-reset').hide();
      }
    }


    /**
     * Clear colorpicker input and hide colorpickers on reset click.
     */
    function colorpickerResetClick(event) {
      if (event) { event.preventDefault(); }
      $colorpickers.iris('hide');
      $(this).siblings(prefix + '-colorpicker')
        .val('')
        .each(colorpickerChange);
    }

    // DOM is ready..
    $(document).ready(function() {
      // Get colorpickers.
      $colorpickers = $(prefix + '-colorpicker');

      // Change handlers.
      $colorpickers.iris({ change: colorpickerChange });
      $colorpickers.bind('input', colorpickerChange); // live change binding

      // Click handler.
      $colorpickers.click(colorpickerClick);

      // Blur handler.
      $colorpickers.blur(colorpickerBlur);

      // Reset click handler.
      $colorpickers.siblings(prefix + '-reset').click(colorpickerResetClick);

      // Initial input coloring.
      $colorpickers.each(colorpickerChange);
    });
  })(); // End colorpickers.

})(jQuery);
