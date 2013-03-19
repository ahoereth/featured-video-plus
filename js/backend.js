jQuery(document).ready(function($){

    /**
     * Set input field values to default on blur if empty and ajax submit if changed
     * @since 1.0
     */
    $('#fvp_video,#fvp_sec').blur(function() {
        var t = $(this); // required to make use of it in ajax callback

        t.val( $.trim( t.val()) );

        value = t.val();
        if((value.length === 0)                          ||
           (value == fvp_backend_data.default_value    ) ||
           (value == fvp_backend_data.default_value_sec) ){
            t.addClass("defaultTextActive");
            if( t.is('#fvp_video') )
                t.val( fvp_backend_data.default_value     );
            else
                t.val( fvp_backend_data.default_value_sec );
        }
        t.trigger('autosize');

        if( t.val() != t.siblings('.fvp_mirror').val() ) {
            bg = t.siblings('.fvp_video_choose').children('.fvp_media_icon').css('background-image');
            t.siblings('.fvp_video_choose').children('.fvp_media_icon').css({'background-image': "url('"+fvp_backend_data.loading_gif+"')"});
            $.post( ajaxurl,
                {
                    'action'    : 'fvp_ajax',
                    'id'        : $('#post_ID').val(),
                    'fvp_nonce' : $('#fvp_nonce').val(),
                    'fvp_video' : $('#fvp_video').val(),
                    'fvp_sec'   : $('#fvp_sec').val()
                },
                function(data) {
                    t.siblings('.fvp_mirror').val( t.val() );
                    t.siblings('.fvp_video_choose').children('.fvp_media_icon').css({'background-image': bg});
                    if(data.valid) {
                        $('#fvp_current_video').html(data.video);
                        $("#fvp_help_notice").hide('fast');
                    } else
                        t.addClass('fvp_invalid');
                }, "json"
            );
        }
    });

    /**
     * Remove default values of input fields on focus
     * @since 1.0
     */
    $(".fvp_input").focus(function() {
        value = $(this).val();
        if((value == fvp_backend_data.default_value    ) ||
           (value == fvp_backend_data.default_value_sec) ){
            $(this).removeClass("defaultTextActive");
            $(this).val("");
        }
    });

    /**
     * Blur both input fields on page load, autosize them and prevent enter-keypress
     * @see http://www.jacklmoore.com/autosize
     * @since 1.0
     */
    $(".fvp_input").autosize().trigger("blur").keypress(function(event) { //{append: "\n"}
        if (event.keyCode == 13) { // enter
            event.preventDefault();
        }
    });

    /**
     * Select whole input field content on click
     * @since 1.2
     */
    $(".fvp_input").click(function() {
        $(this).select();
    });

    /**
     * hide secondary input initially
     * @since 1.2
     */
    var value = $("#fvp_video").val();
    if ( value.length === 0 || value == fvp_backend_data.default_value || !value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
        $("#fvp_sec").val( fvp_backend_data.default_value_sec );
        $("#fvp_sec_wrapper").hide();
    }

    /**
     * recognize change on the primary video input
     * @since 1.2
     */
    $("#fvp_video").bind("change paste keyup", function() {
        setTimeout(handleVideoInput($(this)), 200);
    });

    /**
     * Called when a change on the primary video input occurred
     * @since 1.2
     */
    function handleVideoInput( obj ) {
        var value = $.trim(obj.val());
        var sec   = $.trim($('#fvp_sec').val());
        $("#fvp_help_notice").show('fast');

        if ( value.length === 0 || value == fvp_backend_data.default_value ) {
            $("#fvp_video").removeClass('fvp_invalid'); //css('backgroundColor', 'white');
            $("#fvp_sec").val( fvp_backend_data.default_value_sec ).blur();
            $("#fvp_sec_wrapper").hide('fast', 'linear');
            $("#fvp_localvideo_format_warning").hide('fast', 'linear');
        }

        if ( value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            var file_extension = /^.*\/(.*)\.(.*)$/g;
            var match = file_extension.exec(value);
            if ( match[2] == 'webm' || match[2] == 'mp4' || match[2] == 'ogg' || match[2] == 'ogv' ) {
                $("#fvp_sec_wrapper").show('fast', 'linear');
                $("#fvp_video").removeClass('fvp_invalid'); //.css('backgroundColor', 'white');
                $("#fvp_localvideo_format_warning").hide('fast', 'linear');
            } else {
                $("#fvp_sec").val( fvp_backend_data.default_value_sec ).blur();
                $("#fvp_sec_wrapper").hide('fast', 'linear');
                $("#fvp_video").addClass('fvp_invalid'); //css('backgroundColor', 'lightYellow');
                $("#fvp_localvideo_format_warning").show('fast', 'linear');
            }
            distinctContent();
        } else {
            $("#fvp_sec_wrapper").hide('fast', 'linear');
            $("#fvp_video").removeClass('fvp_invalid'); //.css('backgroundColor', 'white');
            $("#fvp_localvideo_format_warning").hide('fast', 'linear');
        }
    }

    /**
     * recognize change on the secondary video input
     * @since 1.2
     */
    $("#fvp_sec").bind("change paste keyup", function() {
        setTimeout(handleSecInput($(this)), 200);
    });

    /**
     * Called when a change on the primary video input occurred
     * @since 1.2
     */
    function handleSecInput( obj ) {
        var value = $.trim(obj.val());
        var prim  = $.trim($('#fvp_video').val());

        if ( value.length === 0 || value == fvp_backend_data.default_value ) {
            $("#fvp_localvideo_format_warning").hide('fast');
            $("#fvp_sec").removeClass('fvp_invalid'); //.css('backgroundColor', 'white');
        }

        if ( value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            var file_extension = /^.*\/(.*)\.(.*)$/g;
            var match = file_extension.exec(value);
            if ( match[2] == 'webm' || match[2] == 'mp4' || match[2] == 'ogg' || match[2] == 'ogv' ) {
                $("#fvp_sec").removeClass('fvp_invalid'); //.css('backgroundColor', 'white');
                $("#fvp_localvideo_format_warning").hide('fast');
                distinctContent();
            } else {
                $("#fvp_sec").addClass('fvp_invalid'); //.css('backgroundColor', 'lightYellow');
                $("#fvp_localvideo_format_warning").show('fast');
            }
        } else if (value.length !== 0) {
            $("#fvp_sec").addClass('fvp_invalid'); //.css('backgroundColor', 'lightYellow');
            $("#fvp_localvideo_notdistinct_warning").show('fast');
        }

    }

    /**
     * Compares the two input boxes if they contain the same URL
     * @since 1.2
     */
    function distinctContent() {
        if ( $.trim( $('#fvp_video').val() ) == $.trim( $('#fvp_sec').val() ) ) {
            $("#fvp_sec").addClass('fvp_invalid'); //.css('backgroundColor', 'lightYellow');
            $("#fvp_localvideo_notdistinct_warning").show('fast');
        } else {
            $("#fvp_localvideo_notdistinct_warning").hide('fast');
            $("#fvp_sec").removeClass('fvp_invalid'); //.css('backgroundColor', 'white');
        }
    }

    /**
     * set featured image link and featured image requirement warning
     * @since 1.1
     */
    $("#fvp_set_featimg_link").show();
    $("#fvp_set_featimg_input").hide();

    $("#fvp_set_featimg_link, #fvp_warning_set_featimg").click(function() {
        $("#fvp_set_featimg").attr('checked', true);
        $("#fvp_set_featimg").closest("form").submit();
        return false;
    });

    $("#remove-post-thumbnail").click(function() {
        //$("#fvp_set_featimg_box").removeClass("fvp_hidden");
        $("#fvp_featimg_box_warning").removeClass("fvp_hidden");
    });

    $("#set-post-thumbnail").click(function() {
        $("#fvp_featimg_box_warning").addClass("fvp_hidden");
    });

    /**
     * Toggle for opening the contextual help
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
                    type: 'video'
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

    // Media Manager was implemented in WordPress 3.5
    if(fvp_backend_data.wp_35 == 1)
        mediaControl.init();


    /**
     * Button in the top right of the Featured Video box. Planned for a feature release.
     *
     * @since 1.#
     */
    //$('#featured_video_plus-box .handlediv').after('<div class="box_topright"><a href="#" id="fvp_remove" title="Remove Featured Video"><br /></div></div>');

});