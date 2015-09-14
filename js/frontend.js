var initFeaturedVideoPlus;

(function($) {
  'use strict';
  /* global fvpdata */

  var videoCache = {};
  var selectorCache;
  var initTimeout = 0;


  /**
   * Remove the link wrapping featured images on index pages and the
   * possibile repetition of .post-thumbnail-classes.
   */
  function unwrap() {
    // Remove links around videos.
    $('.has-post-video a>.featured-video-plus,' +
      '.has-post-video a>.fvp-dynamic,' +
      '.has-post-video a>.fvp-overlay,' +
      '.has-post-video a>.wp-video,' +
      '.has-post-video a>.wp-video-shortcode'
    ).unwrap();

    // Remove wrapped .post-thumbnail-classes
    $('.has-post-video .post-thumbnail>.post-thumbnail')
      .removeClass('post-thumbnail');

    // There might still be some empty .post-thumbnail links to be removed.
    $('a.post-thumbnail:empty').not('.fvp-dynamic, .fvp-overlay').remove();
  }


  /**
   * Autosize videos using fitvids for responsive videos
   */
  function fitVids() {
    if (fvpdata.fitvids) {
      $('.featured-video-plus.fvp-responsive').fitVids({
        customSelector: ['iframe', 'object', 'embed']
      });
    }
  }


  /**
   * WordPress forces a maximum size of the global $contentwidth onto local
   * videos - overwrite it.
   */
  function sizeLocal() {
    if (fvpdata.width && ! fvpdata.fitvids) {
      $('.fvp-local .wp-video').css({width: fvpdata.width, height: 'auto'});
      var video = $('.fvp-local .wp-video .wp-video-shortcode');
      video.attr({
        width: fvpdata.width,
        height: (fvpdata.width / video.attr('width') ) * video.attr('heigth')
      });
    }
  }


  /**
   * Get the actionicon element from the provided container.
   */
  function getActioniconElem(elem) {
    var $elem = $(elem);
    var $icon = $elem.children('.fvp-actionicon');
    $icon.css({
      height: $elem.height(),
      width : $elem.width(),
      margin: $elem.css('margin')
    });

    return $icon;
  }


  /**
   * Handle mouseover and mouseout events.
   */
  function hoverAction(event) {
    var $img = $(event.currentTarget).children('img');
    var $icon = getActioniconElem(event.currentTarget);

    $icon.toggleClass('play');
    if ($icon.hasClass('play')) {
      $img.animate({ opacity: fvpdata.opacity });
    } else {
      $img.animate({ opacity: 1 });
    }
  }


  /**
   * Replace a featured image with its featured video on-click.
   */
  function dynamicTrigger(event) {
    event.preventDefault();
    var $self = $(event.currentTarget);
    var id = parseInt($self.attr('data-id'), 10);

    var $icon = getActioniconElem(event.currentTarget);
    $icon.addClass('load ' + fvpdata.color);

    $.post(fvpdata.ajaxurl, {
      'action'    : 'fvp_get_embed',
      'fvp_nonce' : fvpdata.nonce,
      'id'        : id
    }, function(response){
      if (response.success) {
        var $parent = $self.parent();
        $self.replaceWith(response.data);

        // Initialize mediaelement.js, autosize and unwrap the new videos.
        $parent.find('.wp-audio-shortcode, .wp-video-shortcode')
          .mediaelementplayer();
        fitVids();
        unwrap();
      }

      $icon.removeClass('load ' + fvpdata.color);
    });
  }


  /**
   * Show the overlay on-click.
   */
  function overlayTrigger(event) {
    event.preventDefault();
    var $self = $(event.currentTarget);
    var id = parseInt($self.attr('data-id'), 10);

    $self.openDOMWindow({
      eventType     : null,
      windowPadding : 0,
      borderSize    : 0,
      windowBGColor : 'transparent',
      overlayOpacity: fvpdata.opacity * 100,
      width : '100%',
      height: '100%'
    });

    // Check if the result is already cached
    if (! videoCache[id]) {
      $.post(fvpdata.ajaxurl, {
        'action'    : 'fvp_get_embed',
        'fvp_nonce' : fvpdata.nonce,
        'id'        : id
      }, function(response) {
        if (response.success) {
          // cache the result to not reload when opened again
          videoCache[id] = response.data;

          $('#DOMWindow').html(response.data);
          sizeLocal();
          $(window).trigger('scroll');
        }
      });
    } else {
      // From cache
      $('#DOMWindow').html( videoCache[id] );
      sizeLocal();
      $(window).trigger('scroll');
    }
  }


  /**
   * Initialize the plugins JS functionality.
   */
  function init() {
    var newSet = $('.featured-video-plus, .fvp-overlay, .fvp-dynamic');
    if (newSet.is(selectorCache)) { return false; }
    selectorCache = newSet;

    // remove wrapping anchors
    // doing this twice with a 1 second delay to fix wrapped local video posters
    unwrap();
    setTimeout(unwrap, 1000);

    // initialize fitvids if available
    fitVids();

    sizeLocal();

    // add hover effect and preload icons
    $('.fvp-overlay, .fvp-dynamic')
      .off('mouseenter').on('mouseenter', hoverAction)
      .off('mouseleave').on('mouseleave', hoverAction);

    // on-demand video insertion click handler
    $('.fvp-dynamic').off('click').on('click', dynamicTrigger);

    // overlay click handler
    $('.fvp-overlay').off('click').on('click', overlayTrigger);
  }


  /**
   * Debounced version of the init function.
   */
  initFeaturedVideoPlus = function() {
    if (0 === initTimeout) {
      init();
      initTimeout = setTimeout(function() {}, 100);
    } else {
      clearTimeout(initTimeout);
      initTimeout = setTimeout(init, 100);
    }
  };


  // Initialization after DOM is completly loaded.
  $(document).ready(function() {
    // Wordaround for chrome bug
    // See https://code.google.com/p/chromium/issues/detail?id=395791
    if (!! window.chrome) {
      $('.featured-video-plus iframe').each(function() {
        this.src = this.src;
      });
    }

    // preload images
    [fvpdata.playicon, fvpdata.loadicon].forEach(function(val) {
      $('body').append($('<img/>', {src: val, alt: 'preload image'}).hide());
    });
  });
})(jQuery);
