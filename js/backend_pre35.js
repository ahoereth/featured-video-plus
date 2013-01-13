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
});