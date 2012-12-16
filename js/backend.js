jQuery(document).ready(function($){

    // remove default value of input field on focus
    // since 1.0
	$("#fvp_video").focus(function(srcc) {
        if ($(this).val() == $(this)[0].title) {
            $(this).removeClass("defaultTextActive");
            $(this).val("");
        }
    });

    $("#fvp_video").blur(function() {
        if ($(this).val() == "") {
            $(this).addClass("defaultTextActive");
            $(this).val($(this)[0].title);
        }
    });

    $("#fvp_video").blur();

    // replace current featured video link & checkbox
    // since 1.1
    $("#fvp_set_featimg_link").show();
    $("#fvp_set_featimg_input").hide();

    $("#fvp_set_featimg_link, #fvp_warning_set_featimg").click(function() {
        $("#fvp_set_featimg").attr('checked', true);
        $("#fvp_set_featimg").closest("form").submit();
        return false;
    });

    $("#remove-post-thumbnail").click(function() {
        $("#fvp_set_featimg_link").html('Set as Featured Image');
        $("#fvp_set_featimg_box").removeClass("fvp_hidden");
        $("#fvp_featimg_box_warning").removeClass("fvp_hidden");
    });

    $("#set-post-thumbnail, .media-button-select").click(function() {
        $("#fvp_set_featimg_link").html('Replace current Featured Image');
        $("#fvp_featimg_box_warning").addClass("fvp_hidden");
    });

});