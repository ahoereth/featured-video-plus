var initFeaturedVideoPlus;

(function($) {
  'use strict';
  /* global fvpdata */


  var $loader = $('<div />').addClass('fvp-loader');
  var playBg = 'url(\'' + fvpdata.playicon + '\')';
  var loadBg = 'url(\'' + fvpdata.loadicon + '\')';
  var bgState;


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
    var $img = $(event.currentTarget).children('img');

    // Is the overlay displayed currently?
    if (0 === $img.siblings('.fvp-loader').length) {
      // Copy classes and css styles onto the play icon overlay.
      $loader.addClass($img.attr('class')).css({
        height: $img.height(),
        width: $img.width(),
        margin: $img.css('margin')
      });

      // Fade out image and insert overlay.
      $img.animate({ opacity: fvpdata.opacity }).before($loader);
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

      triggerPlayLoad();
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

    $('#DOMWindow').css({ backgroundImage: loadBg });

    var $cache = $('#fvp-cache-' + id);

    // Check if the result is already cached
    if (0 === $cache.html().length) {
      $.post(fvpdata.ajaxurl, {
          'action'    : 'fvp_get_embed',
          'fvp_nonce' : fvpdata.nonce,
          'id'        : id
      }, function(response) {
        if (response.success) {
          // cache the result to not reload when opened again
          $cache.html(response.data);

          $('#DOMWindow').html(response.data);
          sizeLocal();
          $(window).trigger('scroll');
        }
      });
    } else {
      // From cache
      $('#DOMWindow').html( $cache.html() );
      $(window).trigger('scroll');
    }
  }


  initFeaturedVideoPlus = function() {
    // remove wrapping anchors
    // doing this twice with a 1 second delay to fix wrapped local video posters
    unwrap();
    setTimeout(unwrap, 1000);

    // initialize fitvids if available
    fitVids();

    sizeLocal();

    // add hover effect and preload icons
    $('.fvp-overlay, .fvp-dynamic').hover(hover, hover);
    triggerPlayLoad();

    // on-demand video insertion click handler
    $('.fvp-dynamic').click(dynamicTrigger);

    // overlay click handler
    $('.fvp-overlay').click(overlayTrigger);
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

    initFeaturedVideoPlus();
  });
})(jQuery);
