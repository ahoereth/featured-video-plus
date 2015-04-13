(function($) {
  'use strict';
  /* global fvpdata */


  var $loader = $('<div />').addClass('fvp_loader');
  var playBg = 'url(\'' + fvpdata.playicon + '\')';
  var loadBg = 'url(\'' + fvpdata.loadicon + '\')';
  var bgState;


  /**
   * Remove the link wrapping featured images on index pages
   */
  function unwrap() {
    $('.has-post-video').find(
      '.featured_video_plus, a.fvp_overlay, a.fvp_dynamic'
    ).each(function() {
      var $self = $(this);
      if ( $self.parent().is('a') ) {
        $self.unwrap();
      }

      // Should already be done by unwrap.
      $self.siblings('a.post-thumbnail').remove();
    });
  }


  /**
   * Autosize videos using fitvids for responsive videos
   */
  function fitVids() {
    if (fvpdata.fitvids) {
      $('.featured_video_plus.responsive').fitVids({
        customSelector: ['iframe', 'object', 'embed', 'video']
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

    if (0 === $elem.find('.fvp_loader').length) {
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
    if (0 === $('#fvp_' + id).html().length) {
      $.post(fvpdata.ajaxurl, {
          'action': 'fvp_get_embed',
          'nonce' : fvpdata.nonce,
          'id'    : id
      }, function(data) {
        if (data.success) {
          // cache the result to not reload when opened again
          $('#fvp_' + id).html(data.html);

          $('#DOMWindow').html(data.html);
          $(window).trigger('scroll');
        }
      });
    } else {
      // From cache
      $('#DOMWindow').html( $('#fvp_' + id).html() );
      $(window).trigger('scroll');
    }
  }


  // Initialization after DOM is completly loaded.
  $(document).ready(function() {
    // remove wrapping anchors
    unwrap();

    // initialize fitvids if available
    fitVids();

    // add hover effect and preload icons
    $('.fvp_overlay, .fvp_dynamic').hover(hover, hover);
    triggerPlayLoad();

    // on-demand video insertion click handler
    $('.fvp_dynamic').click(dynamicTrigger);

    // overlay click handler
    $('.fvp_overlay').click(overlayTrigger);
  });
})(jQuery);
