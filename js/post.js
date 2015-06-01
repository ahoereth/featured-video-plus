(function($) {
  'use strict';
  /* global fvp_post, ajaxurl */

  var context = fvp_post;
  var $input;
  var $media;
  var mediaicon;


  /**
   * Set the featured video with 'setfeatimg' parameter in order to force set
   * the featured image to the video thumbnail if possible.
   *
   * @param {event} event jQuery click event.
   */
  function setFeatimg(event) {
    event.preventDefault();
    submitVideo(true);
  }


  /**
   *
   * When the featured image is removed it might take some time for the HTTP
   * request to return before we can enable the 'quick set featimg' link. It
   * is not too bad if the link is not available (won't be displayed), but its
   * nice to have.
   */
  function removeFeatimg() {
    setTimeout(refreshHandlers, 2000); // Arbritrarily wait 2 seconds.
  }


  /**
   * Submit video to server via ajax.
   *
   * @param {bool} setFeatimg
   */
  function submitVideo(setFeatimg) {
    setFeatimg = setFeatimg || false;

    $.post(ajaxurl, {
      'action'         : 'fvp_save',
      'id'             : $('#post_ID').val(),
      'fvp_nonce'      : $('#fvp_nonce').val(),
      'fvp_video'      : $input.val(),
      'fvp_set_featimg': setFeatimg
    }, function(data) {
      var $container = $('.fvp-current-video');

      // reset loading icon
      $media.css({ backgroundImage: mediaicon });

      // removed video
      if('remove' === data.task) {
        $container
          .css({height: $container.height() })
          .html('')
          .animate({height: 0});

      // new video data
      } else {
        $container
          .css({height: 'auto'})
          .html(data.video);
      }

      // update featured image
      $('#postimagediv .inside').html(data.img);
      refreshHandlers();
    }, 'json' );
  }


  /**
   * Sets the set and remove featured image handlers.
   * @return {[type]} [description]
   */
  function refreshHandlers() {
    // Button for quickly setting a featured image if none is set.
    $('.fvp-set-featimg').show().click(setFeatimg);

    // Show setFeatimg link after removing a featured image.
    $('#remove-post-thumbnail').click(removeFeatimg);
  }


  $(document).ready(function() {
    // elements
    $input = $('.fvp-video');
    $media = $input.siblings('.fvp-video-choose').children('.fvp-media-icon');
    mediaicon = $media.css( 'backgroundImage' );

    var loadingicon = 'url(\'' + context.loading_gif + '\')';
    var currentUrl  = $input.val();

    // Automatically submit the video URL using AJAX when the input is blurred.
    // Update video and featured image with the returned data.
    $input.blur(function() {
      $input.val( $.trim( $input.val() ) );

      // don't do anything if input didn't change
      if (currentUrl === $input.val()) {
        return;
      }

      // remember new url
      currentUrl = $input.val();

      // autosize input field
      $input.trigger('autosize');

      // display loading gif in input
      $media.css({ backgroundImage: loadingicon });

      submitVideo();
    });

    // Initialize autosizing the url input field, disable enter key and
    // auto select content on click.
    // @see http://www.jacklmoore.com/autosize
    $input
      .autosize()
      .trigger('blur')
      .keypress(function(event) {
        if (13 === event.keyCode) { // enter key
          event.preventDefault();
          $(this).trigger('blur');
        }
      })
      .click(function() {
        $(this).select();
      });


    // Initialize set & remove featured image handlers.
    refreshHandlers();


    // WordPress 3.5 Media Manager
    // @see http://www.blazersix.com/blog/wordpress-image-widget/
    // @see https://github.com/blazersix/simple-image-widget/blob/master/js/simple-image-widget.js
    var $control;
    var mediaControl = {
      // Initializes a new media manager or returns an existing frame.
      // @see wp.media.featuredImage.frame()
      frame: function() {
        if (this._frame) {
          return this._frame;
        }

        this._frame = wp.media({
          title: $control.data('title'),
          library: {
            type: 'video'
          },
          button: {
            text: $control.data('button')
          },
          multiple: false
        });

        this
          ._frame.on('open', this.updateFrame)
          .state('library').on('select', this.select);

        return this._frame;
      },

      select: function() {
        var selection = this.get('selection'),
          returnProperty = 'url';

        var target = $control.data('target');
        $(target)
          .val( selection.pluck( returnProperty ) )
          .change()
          .trigger('blur');
      },

      updateFrame: function() {
        // Do something when the media frame is opened.
      },

      init: function() {
        $('#wpbody').on('click', '.fvp-video-choose', function(e) {
          e.preventDefault();

          $control = $(this).closest('.fvp-input-wrapper');

          mediaControl.frame().open();
        });
      }
    };

    mediaControl.init();
  });
})(jQuery);
