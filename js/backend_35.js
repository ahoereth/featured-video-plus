// >=WordPress 3.5
jQuery(document).ready(function($){
	var myOptions = {
		// you can declare a default color here,
		// or in the data-default-color attribute on the input
		defaultColor: '#00adef',
		// a callback to fire whenever the color changes to a valid color
		change: function(event, ui){},
		// a callback to fire when the input is emptied or an invalid color
		clear: function() {},
		// hide the color picker controls on load
		hide: true,
		// show a group of common colors beneath the square
		// or, supply an array of colors to customize further
		palettes: new Array('#00adef', '#ff9933', '#c9ff23', '#ff0179', '#ffffff')
	};
	
	$('#fvp-settings-vimeo-color').wpColorPicker(myOptions);
});