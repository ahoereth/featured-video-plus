jQuery(document).ready(function($){

    /*
     * remove default value on focus
     * since 1.0
     */
	$(".fvp_input").focus(function() {
        if ($(this).val() == $(this)[0].title) {
            $(this).removeClass("defaultTextActive");
            $(this).val("");
        }
    });

    /*
     * add default value on blur if empty
     * since 1.0
     */
    $(".fvp_input").blur(function() {
        if ( ($(this).val().length === 0) || ($(this).val() == $(this)[0].title) ) {
            $(this).addClass("defaultTextActive");
            $(this).val($(this)[0].title);
        }
    });

    /**
     * blur both input fields on page load
     * since 1.0
     */
    $(".fvp_input").blur();

    /**
     * select whole input field content on click
     * since 1.2
     */
    $(".fvp_input").click(function() {
        $(this).select();
    });

    /**
     * hide secondary input initially
     * since 1.2
     */
    var value = $("#fvp_video").val();
    if ( value.length === 0 || value == fvp_backend_data.default_value )
        $("#fvp_sec").val( fvp_backend_data.default_value_sec ).hide();

    /**
     * hide or show second input box when content is added or deleted
     * since 1.2
     */
    $("#fvp_video").change(function() {
        var value = $(this).val();

        if ( value.length === 0 || value == fvp_backend_data.default_value ) {
            $("#fvp_sec").hide('fast');
            $("#fvp_localvideos_notice").show('fast');
        }

        if ( value.match( fvp_backend_data.wp_upload_dir.replace(/\//g, "\\\/") ) ) {
            $("#fvp_sec").show('fast');
            $("#fvp_localvideos_notice").hide('fast');
        }
    });

    /**
     * set featured image link and featured image requirement warning
     * since 1.1
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