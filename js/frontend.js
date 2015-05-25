(function($) {
  'use strict';
  /* global fvpdata */


  var $loader = $('<div />').addClass('fvp-loader');
  var playBg = 'url(\'' + fvpdata.playicon + '\')';
  var loadBg = 'url(\'' + fvpdata.loadicon + '\')';
  var bgState;


  /**
   * Remove the link wrapping featured images on index pages
   */
  function unwrap() {
    $('.has-post-video a.post-thumbnail>.featured-video-plus,' +
      '.has-post-video a.post-thumbnail>.mejs-video,' +
      '.has-post-video a.post-thumbnail>.wp-video'
    ).unwrap();
  }


  /**
   * Autosize videos using fitvids for responsive videos
   */
  function fitVids() {
    if (fvpdata.fitvids) {
      $('.featured-video-plus.responsive').fitVids({
        customSelector: ['iframe', 'object', 'embed']
      });
    }
  }


  /**
   * Trigger the play / load icon (and preload them).
   */
  function triggerPlayLoad() {
    // preload images
    if (bgState === undefined) {
      [fvpdata.playicon, fvpdata.loadicon].forEach(function(val) {
        $('body').append( $('<img/>', { src: val }).hide() );
      });
    }

    // trigger image
    bgState = bgState === playBg ? loadBg : playBg;
    $loader.css({ backgroundImage: bgState });
  }


  /**
   * Handle mouseover and mouseout events.
   */
  function hover(event) {
    var $elem = $(event.currentTarget);
    var $img = $elem.children('img');

    if (0 === $elem.find('.fvp-loader').length) {
      $img.animate({ opacity: fvpdata.opacity });
      $elem
        .css({ position: 'relative' })
        .prepend(
          $loader
            .css({
              height    :  $img.height(),
              width     :  $img.width(),
              marginTop : -$img.height()/2,
              marginLeft: -$img.width()/2
            })
        );
    } else if (bgState !== loadBg) {
      $img.animate({ opacity: 1 });
      $loader.remove();
    }
  }


  /**
   * Replace a featured image with its featured video on-click.
   */
  function dynamicTrigger(event) {
    event.preventDefault();
    var $self = $(event.currentTarget);
    var id = parseInt($self.attr('data-id'), 10);

    triggerPlayLoad();

    $.post(fvpdata.ajaxurl, {
      'action': 'fvp_get_embed',
      'nonce' : fvpdata.nonce,
      'id'    : id
    }, function(data){
      if (data.success) {
        $self.replaceWith(data.html);
        unwrap();
        fitVids();
      }

      triggerPlayLoad();
    });
  }


  /**
   * Show the overlay on-click.
   */
  function overlayTrigger(event) {
    var $self = $(event.currentTarget);
    var id = parseInt($self.attr('data-id'), 10);

    $self.openDOMWindow({
      eventType     : null,
      windowPadding : 0,
      borderSize    : 0,
      windowBGColor : 'transparent',
      overlayOpacity: fvpdata.opacity * 100
    });

    $('#DOMWindow').css({ backgroundImage: loadBg });

    // Check if the result is already cached
    if (0 === $('#fvp-' + id).html().length) {
      $.post(fvpdata.ajaxurl, {
          'action': 'fvp_get_embed',
          'nonce' : fvpdata.nonce,
          'id'    : id
      }, function(data) {
        if (data.success) {
          // cache the result to not reload when opened again
          $('#fvp-' + id).html(data.html);

          $('#DOMWindow').html(data.html);
          $(window).trigger('scroll');
        }
      });
    } else {
      // From cache
      $('#DOMWindow').html( $('#fvp-' + id).html() );
      $(window).trigger('scroll');
    }
  }


  // Initialization after DOM is completly loaded.
  $(document).ready(function() {
    // remove wrapping anchors
    // doing this twice with a 1 second delay to fix wrapped local video posters
    unwrap();
    setTimeout(unwrap, 1000);

    // initialize fitvids if available
    fitVids();

    // add hover effect and preload icons
    $('.fvp-overlay, .fvp-dynamic').hover(hover, hover);
    triggerPlayLoad();

    // on-demand video insertion click handler
    $('.fvp-dynamic').click(dynamicTrigger);

    // overlay click handler
    $('.fvp-overlay').click(overlayTrigger);
  });
})(jQuery);
