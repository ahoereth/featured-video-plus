// *****************************************************************************
// TABBED OPTIONS
(function($) {
  /* global fvphtml */
  'use strict';

  var clicker = function() {
    var $title = $(this);
    var $body  = $title.siblings('[data-hook=\'' + $title.data('hook') + '\']');

    // current active title is not clickable
    if ($title.hasClass('active') && $body.hasClass('active')) {
      return;
    }

    // no longer active
    $title.siblings(fvphtml.prefix + 'tab-title').removeClass('active');
    $title.siblings(fvphtml.prefix + 'tab-body').slideUp();

    // newly active
    $title.addClass('active');
    $body.addClass('active').slideDown();
  };


  $(document).ready(function() {
    // get tabs
    var $tabs = $(fvphtml.prefix + 'tabs');

    // initialize every instance's functionality
    for ( var i = $tabs.length - 1; i >= 0; i-- ) {
      var $tab = $( $tabs[i] );

      // get titles and bodys
      var $titles = $tab.children(fvphtml.prefix + 'tab-title');
      var $bodys  = $tab.children(fvphtml.prefix + 'tab-body');

      // first title/body pair is active on initiation
      $titles.first().addClass('active');
      $bodys.first().addClass('active');

      // pull titles to top
      $tab.prepend( $titles );

      // hide all but the initially active content
      $bodys.filter(':not(.active)').hide();

      // initialize title click event
      $tab.children(fvphtml.prefix + 'tab-title').click(clicker);
    }
  });
})(jQuery);




// *****************************************************************************
// CONDITIONALS
(function($) {
  /* global fvphtml */
  'use strict';

  var triggers = {};
  var conditionalTriggered = function() {
    var $trigger = $(this);
    var targets = triggers[ $trigger.attr('name') ];
    for (var i = 0; i < targets.length; i++) {
      var $target = $( targets[i] );

      var targetNames = $target.data('names').split('|');
      var targetValues = ("" + $target.data('values')).split('|');

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
  };


  $(document).ready(function() {
    var $conditionals = $(fvphtml.prefix + 'conditional');
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
})(jQuery);




// *****************************************************************************
// COLORPICKERS / IRIS
// See http://automattic.github.io/Iris/
(function($) {
  'use strict';

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
  var colorpickerChange = function(event, ui) {
    var $this = $(this);
    var color = ui && ui.color ? ui.color.toString() : $this.val();
    color = cleanHex(color);

    $this.css({
      backgroundColor: color ? color : '#ffffff',
      color: getContrastColor(color)
    });

    if (! color) { $this.siblings(fvphtml.prefix + 'reset').hide(); }
    else         { $this.siblings(fvphtml.prefix + 'reset').show(); }
  };


  /**
   * Hide all colorpickers upon opening a new one.
   */
  var colorpickerClick = function() {
    var $this = $(this);
    $colorpickers.not( $this ).iris('hide');
    $this.iris('show');
  };


  /**
   * Hide colorpicker reset button on blur.
   */
  var colorpickerBlur = function(event) {
    if (event) { event.preventDefault(); }
    var $this = $(this);
    if ('' === $this.val()) {
      $this.siblings(fvphtml.prefix + 'reset').hide();
    }
  };


  /**
   * Clear colorpicker input and hide colorpickers on reset click.
   */
  var colorpickerResetClick = function(event) {
    if (event) { event.preventDefault(); }
    $colorpickers.iris('hide');
    $(this).siblings(fvphtml.prefix + 'colorpicker')
      .val('')
      .each(colorpickerChange);
  };


  // DOM is ready..
  $(document).ready(function() {
    // Get colorpickers.
    $colorpickers = $(fvphtml.prefix + 'colorpicker');

    // Change handlers.
    $colorpickers.iris({ change: colorpickerChange });
    $colorpickers.bind('input', colorpickerChange); // live change binding

    // Click handler.
    $colorpickers.click(colorpickerClick);

    // Blur handler.
    $colorpickers.blur(colorpickerBlur);

    // Reset click handler.
    $colorpickers.siblings(fvphtml.prefix + 'reset').click(colorpickerResetClick);

    // Initial input coloring.
    $colorpickers.each(colorpickerChange);
  });
})(jQuery);




// *****************************************************************************
// TUTORIAL POINTERS
(function($) {
  /* global fvphtml, ajaxurl */
  'use strict';

  /**
   * Used when dissmissing a pointer to fire an AJAX request to save the
   * closed state to the database.
   */
  var closePointer = function() {
    var identifier = $(this).data('wpPointer').options.pointer_id;
    $.post(ajaxurl, {
      pointer: identifier,
      action: 'dismiss-wp-pointer'
    });
  };


  /**
   * Initializes and opens a pointer given the required pointer data.
   *
   * @param  {object} pointer
   *   {
   *     target: {string} jQuery selector,
   *     identifier: {string},
   *     title: {string},
   *     content: {string}
   *     position: {edge: {string}, align: {string}},
   *   }
   */
  function openPointer(pointer) {
    var title = pointer.title || '';
    var content = pointer.content || '';
    var position = pointer.position || {edge: 'right', align: 'middle'};

    $(pointer.target).pointer({
      pointer_id: pointer.identifier,
      content: '<h3>' + title + '</h3><p>' + content + '</p>',
      position: position,
      close: closePointer
    }).pointer('open');
  }


  $(document).ready(function() {
    var pointers = fvphtml.pointers || [];
    for (var i = 0; i < pointers.length; i++) {
      openPointer(fvphtml.pointers[i]);
    }
  });
})(jQuery);




// *****************************************************************************
// Contextual Help Links
jQuery(document).ready(function($) {
  /* global fvphtml */
  'use strict';

  $(fvphtml.prefix + 'help-link, .help-link').click(function() {
    $('#contextual-help-link').trigger('click');
  });
});
