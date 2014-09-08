jQuery(document).ready(function($){
    var context = fvp_post;


    /**
     * Set input field values to default on blur if empty and ajax submit if changed
     *
     * @since 1.0
     */
    $('#fvp_video').blur(function() {
        t = $(this); // required to make use of it in ajax callback

        t.val( $.trim( t.val() ) );
        t.trigger('autosize');

        // Did the input change?
        if( t.val() != t.siblings('.fvp_mirror').val() ) {

            // save the previous background
            var bg = t.siblings('.fvp_video_choose').children('.fvp_media_icon').css('backgroundImage');

            // display loading gif in input
            t.siblings('.fvp_video_choose').children('.fvp_media_icon').css('backgroundImage', "url('"+context.loading_gif+"')");

            $.post( ajaxurl,
                {
                    'action'    : 'fvp_save',
                    'id'        : $('#post_ID').val(),
                    'fvp_nonce' : $('#fvp_nonce').val(),
                    'fvp_video' : $('#fvp_video').val()
                },
                function(data) {
                    // remember current video url
                    t.siblings('.fvp_mirror').val( t.val() );

                    // reset loading icon
                    t.siblings('.fvp_video_choose').children('.fvp_media_icon').css('backgroundImage', bg);

                    // Was the video removed?
                    if( data.task == 'remove' ) {
                        $('#fvp_current_video').html('').animate({'height':'0px'});
                    } else {
                        // Display video
                        $('#fvp_current_video').html(data.video).animate({'height':'144px'});

                        // Hide help notice
                        $("#fvp_help_notice").slideUp('fast');

                        // Data is valid: Hide warnings etc
                        if (data.valid) {
                            $("#fvp_set_featimg_link, #fvp_featimg_warning").slideUp().addClass("fvp_hidden");
                            t.css('backgroundColor','#00FF00').animate({'backgroundColor':'white'}, 500, function() { t.css('backgroundColor',''); });

                        // Data is invalid
                        } else {
                            t.addClass('fvp_invalid');
                        }
                    }

                    // update featured image
                    $('#postimagediv .inside').html(data.img);
                }, "json"
            );
        }
    });


    /**
     * Blur input field on page load, autosize them and prevent enter-keypress
     *
     * @see http://www.jacklmoore.com/autosize
     * @since 1.0
     */
    $(".fvp_input").autosize().trigger("blur").keypress(function(event) {
        // enter key
        if (event.keyCode == 13) {
            event.preventDefault();
        }
    });


    /**
     * Select whole input field content on click
     *
     * @since 1.2
     */
    $(".fvp_input").click(function() {
        $(this).select();
    });


    /**
     * Called when a change on the primary video input occurred
     *
     * @since 1.2
     */
    function handleVideoInput( obj ) {
        var value = $.trim(obj.val());
        $("#fvp_help_notice").slideDown('fast');

        // Input field is empty
        if ( value.length === 0 ) {
            $("#fvp_video").removeClass('fvp_invalid');
            $("#fvp_localvideo_format_warning").slideUp('fast');
        }

        // URL is local: Check file extension
        if ( value.match( context.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            var file_extension = /^.*\/(.*)\.(.*)/g;
            var match = file_extension.exec(value);
            if ( match[2] == 'webm' || match[2] == 'mp4' || match[2] == 'ogg' || match[2] == 'ogv' ) {
                $("#fvp_video").removeClass('fvp_invalid');
                $("#fvp_localvideo_format_warning").slideUp('fast');
            } else {
                $("#fvp_video").addClass('fvp_invalid');
                $("#fvp_localvideo_format_warning").slideDown('fast', 'linear');
            }
        } else {
            $("#fvp_video").removeClass('fvp_invalid');
            $("#fvp_localvideo_format_warning").slideUp('fast');
        }
    }


    /**
     * Recognize change on the primary video input
     *
     * @since 1.2
     */
    $("#fvp_video").bind("change paste keyup", function() {
        setTimeout(handleVideoInput($(this)), 200);
    });


    /**
     * "Set featured image" link and featured image requirement warning
     *
     * @since 1.1
     */
    $("#fvp_set_featimg_link").removeClass('fvp_hidden');
    $("#fvp_set_featimg_input").addClass('fvp_hidden');

    $("#fvp_set_featimg_link, #fvp_warning_set_featimg").click(function() {
        $("#fvp_set_featimg").attr('checked', true);
        //$("#fvp_set_featimg").closest("form").submit();
        $.post( ajaxurl,
            {
                'action'    : 'fvp_ajax',
                'id'        : $('#post_ID').val(),
                'fvp_nonce' : $('#fvp_nonce').val(),
                'fvp_video' : $('#fvp_video').val()
            },
            function (data) {
                $('#postimagediv .inside').html(data.img);
                $("#fvp_set_featimg_link, #fvp_featimg_warning").slideUp().addClass("fvp_hidden");
            }, "json"
        );
        return false;
    });

    $("#remove-post-thumbnail").click(function() {
        $("#fvp_set_featimg_link, #fvp_featimg_warning").slideDown().removeClass("fvp_hidden");
    });

    $("#set-post-thumbnail").click(function() {
        $("#fvp_featimg_box_warning").addClass("fvp_hidden");
    });


    /**
     * Toggle for opening the contextual help
     *
     * @since 1.3
     */
    $('#fvp_help_toggle').bind( 'click', function() {
        $('#contextual-help-link').trigger('click');
    });


    /**
     * Making use of the WordPress 3.5 Media Manager
     *
     * @see http://www.blazersix.com/blog/wordpress-image-widget/
     * @see https://github.com/blazersix/simple-image-widget/blob/master/js/simple-image-widget.js
     */
    var $control, $controlTarget, mediaControl;

    mediaControl = {
        // Initializes a new media manager or returns an existing frame.
        // @see wp.media.featuredImage.frame()
        frame: function() {
            if ( this._frame )
                return this._frame;

            this._frame = wp.media({
                title: $control.data('title'),
                library: {
                    type: 'document'
                },
                button: {
                    text: $control.data('button')
                },
                multiple: false
            });

            this._frame.on( 'open', this.updateFrame ).state('library').on( 'select', this.select );

            return this._frame;
        },

        select: function() {
            var selection = this.get('selection'),
                returnProperty = 'url';

            $( $control.data('target') ).val( selection.pluck( returnProperty ) ).trigger('autosize').change().removeClass("defaultTextActive");
            $('#fvp_video').blur();
        },

        updateFrame: function() {
            // Do something when the media frame is opened.
        },

        init: function() {
            $('#wpbody').on('click', '.fvp_video_choose', function(e) {
                e.preventDefault();

                $control = $(this).closest('.fvp_input_wrapper');

                mediaControl.frame().open();
            });
        }
    };

    mediaControl.init();
});
