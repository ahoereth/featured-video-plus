// <WordPress 3.5
jQuery(document).ready(function($){
	$('.fvp_colorpicker').hide().each(function() {
		$(this).farbtastic($(this).siblings('.fvp_colorpicker_input'));
	});
	$(document).mousedown(function() {
		$('.fvp_colorpicker').each(function() {
			var display = $(this).css('display');
			if ( display == 'block' )
				$(this).fadeOut();
		});
	});
	$('.fvp_colorpicker_input').click(function() {
		$(this).siblings('.fvp_colorpicker').fadeIn();
	});
});