jQuery(document).ready(function($){

    $('.fvp_toggle_input .fvp_toggle').bind('click', function () {
        var input = $(this).closest('.fvp_toggle_input').children('.fvp_input');

        if ($(this).attr('checked')) {
            input.attr('readonly', 'true').addClass('fvp_readonly');
        } else {
            input.removeAttr('readonly').removeClass('fvp_readonly');
        }
    });

	$('#fvp_help_toggle').bind( 'click', function() {
		$('#contextual-help-link').trigger('click');
	});
});