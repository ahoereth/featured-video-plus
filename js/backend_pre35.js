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
		$('#fvp-settings-dailymotion-foreground-colorpicker').each(function() {
			var display = $(this).css('display');
			if ( display == 'block' )
				$(this).fadeOut();
		});
		$('#fvp-settings-dailymotion-background-colorpicker').each(function() {
			var display = $(this).css('display');
			if ( display == 'block' )
				$(this).fadeOut();
		});
		$('#fvp-settings-dailymotion-highlight-colorpicker').each(function() {
			var display = $(this).css('display');
			if ( display == 'block' )
				$(this).fadeOut();
		});
	});


	$('#fvp-settings-dailymotion-foreground-colorpicker').hide();
	$('#fvp-settings-dailymotion-foreground-colorpicker').farbtastic('#fvp-settings-dailymotion-foreground');
	$('#fvp-settings-dailymotion-foreground').click(function() {
		$('#fvp-settings-dailymotion-foreground-colorpicker').fadeIn();
	});

	$('#fvp-settings-dailymotion-background-colorpicker').hide();
	$('#fvp-settings-dailymotion-background-colorpicker').farbtastic('#fvp-settings-dailymotion-background');
	$('#fvp-settings-dailymotion-background').click(function() {
		$('#fvp-settings-dailymotion-background-colorpicker').fadeIn();
	});


	$('#fvp-settings-dailymotion-highlight-colorpicker').hide();
	$('#fvp-settings-dailymotion-highlight-colorpicker').farbtastic('#fvp-settings-dailymotion-highlight');
	$('#fvp-settings-dailymotion-highlight').click(function() {
		$('#fvp-settings-dailymotion-highlight-colorpicker').fadeIn();
	});

});