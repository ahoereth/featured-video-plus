// >=WordPress 3.5
jQuery(document).ready(function($){
	$('#fvp-settings-vimeo-color').wpColorPicker({
		defaultColor: '#00adef',
		hide: true,
		palettes: new Array('#00adef', '#ff9933', '#c9ff23', '#ff0179', '#ffffff')
	});

	$('#fvp-settings-dailymotion-foreground').wpColorPicker({
		defaultColor: '#f7fffd',
		hide: true,
		palettes: new Array('#E02C72', '#92ADE0', '#E8D9AC', '#C2E165', '#FF0099', '#CFCFCF')
	});

	$('#fvp-settings-dailymotion-highlight').wpColorPicker({
		defaultColor: '#ffc300',
		hide: true,
		palettes: new Array('#BF4B78', '#A2ACBF', '#FFF6D9', '#809443', '#C9A1FF', '#834596')
	});

	$('#fvp-settings-dailymotion-background').wpColorPicker({
		defaultColor: '#171d1b',
		hide: true,
		palettes: new Array('#260F18', '#202226', '#493D27', '#232912', '#052880', '#000000')
	});
});