(function($) {
  'use strict';
  /* global fvpPost, ajaxurl */

  var context = fvpPost;
  var $input;
  var $media;
  var currentUrl;
  var mediaicon;
  var loadingicon = 'url(' + context.loading_gif + ')';


  /**
   * Set the featured video with 'setfeatimg' parameter in order to force set
   * the featured image to the video thumbnail if possible.
   *
   * @param {event} event jQuery click event.
   */
  function setFeatimg(event) {
    submitVideo(event, true);
  }


  /**
   *
   * When the featured image is removed it might take some time for the HTTP
   * request to return before we can enable the 'quick set featimg' link. It
   * is not too bad if the link is not available (won't be displayed), but its
   * nice to have.
   */
  function removeFeatimg(event) {
    event.preventDefault();

    $media.css({ backgroundImage: loadingicon }); // Show loading gif.
    $.post(ajaxurl, {
      'action'    : 'fvp_remove_img',
      'id'        : $('#post_ID').val(),
      'fvp_nonce' : $('#fvp_nonce').val()
    }, function(response) {
      if (response.success) {
        $('#postimagediv .inside').html(response.data);
        $media.css({ backgroundImage: mediaicon }); // Hide loading gif.
      }
    }, 'json' );
  }


  /**
   * Submit video to server via ajax.
   *
   * @param {bool} setFeatimg
   */
  function submitVideo(event, setFeatimg) {
    event.preventDefault();
    setFeatimg = setFeatimg || false;
    $input.val($.trim($input.val())).trigger('autosize'); // Remove whitespace.

    // Don't do anything if value didn't change and we are not force-setting
    // the featured image.
    if (currentUrl === $input.val() && ! setFeatimg) { return; }

    $media.css({ backgroundImage: loadingicon }); // Show loading gif.
    currentUrl = $input.val(); // Remember new url.

    var data = {
      'action'         : 'fvp_save',
      'id'             : $('#post_ID').val(),
      'fvp_nonce'      : $('#fvp_nonce').val(),
      'fvp_video'      : $input.val(),
      'fvp_set_featimg': setFeatimg
    };

    $.post(ajaxurl, data, function(response) {
      if (! response.success) {
        return false;
      }

      var data = response.data;
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
    }, 'json' );
  }


  $(document).ready(function() {
    $input = $('.fvp-video');
    $media = $input.siblings('.fvp-video-choose').children('.fvp-media-icon');
    currentUrl  = $input.val();
    mediaicon = $media.css('backgroundImage');

    // Automatically submit the video URL using AJAX when the input is blurred.
    // Update video and featured image with the returned data.
    $input.blur(submitVideo);

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

    // Click handlers for quickly setting a featured image from the video and
    // removing the existing featured image the FVP way. Additionally hiding
    // the WordPress remove featured image link.
    $('#postimagediv')
      .on('click', '.fvp-set-image', setFeatimg)
      .on('click', '.fvp-remove-image', removeFeatimg);

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
