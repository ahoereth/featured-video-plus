// <WordPress 3.5
jQuery(document).ready(function($){
	$('#fvp-settings-vimeo-colorpicker').hide();
	$('#fvp-settings-vimeo-colorpicker').farbtastic('#fvp-settings-vimeo-color');

	$('#fvp-settings-vimeo-color').click(function() {
		$('#fvp-settings-vimeo-colorpicker').fadeIn();
	});

	$(document).mousedown(function() {
		$('#fvp-settings-vimeo-colorpicker').each(function() {
			var display = $(this).css('display');
			if ( display == 'block' )
				$(this).fadeOut();
		});
	});

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
});