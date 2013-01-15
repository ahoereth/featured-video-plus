jQuery(document).ready(function($){

    /**
     * remove default value on focus
     * @since 1.0
     */
    $(".fvp_input").focus(function() {
        if ($(this).val() == $(this)[0].title) {
            $(this).removeClass("defaultTextActive");
            $(this).val("");
        }
    });

    /**
     * add default value on blur if empty
     * @since 1.0
     */
    $(".fvp_input").blur(function() {
        if ( ($(this).val().length === 0) || ($(this).val() == $(this)[0].title) ) {
            $(this).addClass("defaultTextActive");
            $(this).val($(this)[0].title);
        }
    });

    /**
     * blur both input fields on page load, autosize them and prevent enter
     * @see http://www.jacklmoore.com/autosize
     * @since 1.0
     */
    $(".fvp_input").autosize().blur().keypress(function(event) {
        if (event.keyCode == 13) { // enter
            event.preventDefault();
        }
    });

    /**
     * select whole input field content on click
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
    if ( value.length === 0 || value == fvp_backend_data.default_value || !value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) )
        $("#fvp_sec").val( fvp_backend_data.default_value_sec ).hide();

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

        if ( value.length === 0 || value == fvp_backend_data.default_value ) {
            $("#fvp_video").css('backgroundColor', 'white');
            $("#fvp_sec").val( fvp_backend_data.default_value_sec ).blur().hide('fast');
            $("#fvp_localvideo_notice").show('fast');
            $("#fvp_localvideo_format_warning").hide('fast');
        }

        if ( value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            var file_extension = /^.*\/(.*)\.(.*)$/g;
            var match = file_extension.exec(value);
            if ( match[2] == 'webm' || match[2] == 'mp4' || match[2] == 'ogg' || match[2] == 'ogv' ) {
                $("#fvp_sec").show('fast');
                $("#fvp_video").css('backgroundColor', 'white');
                $("#fvp_localvideo_format_warning").hide('fast');
            } else {
                $("#fvp_sec").val( fvp_backend_data.default_value_sec ).blur().hide('fast');
                $("#fvp_video").css('backgroundColor', 'lightYellow');
                $("#fvp_localvideo_format_warning").show('fast');
            }
            distinctContent();
        } else {
            $("#fvp_sec").hide('fast');
            $("#fvp_video").css('backgroundColor', 'white');
            $("#fvp_localvideo_format_warning").hide('fast');
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
            $("#fvp_sec").css('backgroundColor', 'white');
            $("#fvp_localvideo_notice").hide('show');
        }

        if ( value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            var file_extension = /^.*\/(.*)\.(.*)$/g;
            var match = file_extension.exec(value);
            if ( match[2] == 'webm' || match[2] == 'mp4' || match[2] == 'ogg' || match[2] == 'ogv' ) {
                distinctContent();
                $("#fvp_sec").css('backgroundColor', 'white');
                $("#fvp_localvideo_format_warning").hide('fast');
                $("#fvp_localvideo_notice").hide('fast');
            } else {
                distinctContent();
                $("#fvp_sec").css('backgroundColor', 'lightYellow');
                $("#fvp_localvideo_format_warning").show('fast');
            }
        } else if (value.length !== 0) {
            $("#fvp_sec").css('backgroundColor', 'lightYellow');
            $("#fvp_localvideo_notdistinct_warning").show('fast');
        }

    }

    /**
     * Compares the two input boxes if they contain the same URL
     * @since 1.2
     */
    function distinctContent() {
        if ( $('#fvp_video').val() == $('#fvp_sec').val() ) {
            $("#fvp_sec").css('backgroundColor', 'lightYellow');
            $("#fvp_localvideo_notdistinct_warning").show('fast');
        } else {
            $("#fvp_localvideo_notdistinct_warning").hide('fast');
            $("#fvp_sec").css('backgroundColor', 'white');
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

});