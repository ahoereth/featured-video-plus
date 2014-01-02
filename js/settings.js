jQuery(document).ready(function($){
    $('.fvp_toggle_input .fvp_toggle').bind('click change', function() {
        var input = $(this).closest('.fvp_toggle_input').children('.fvp_input');
        if ($(this).attr('checked'))
            input.attr('readonly', 'true').addClass('fvp_readonly');
        else
            input.removeAttr('readonly').removeClass('fvp_readonly');
    });

    $('#fvp-settings-youtube-color').click(function() {
        if($('#fvp-settings-youtube-color:checked').length == 1)
            $('#youtube_logoinput_wrapper').fadeOut('slow', function() { $(this).addClass(   'fvp_hidden'); } );
        else
            $('#youtube_logoinput_wrapper').fadeIn( 'slow', function() { $(this).removeClass('fvp_hidden'); } );
    });

    $('#fvp_help_toggle').bind( 'click', function() {
        $('#contextual-help-link').trigger('click');
    });

    if( $('#fvp-settings-width-auto:checked').length == 1 )
        $('#fvp-settings-align-1').closest('tr').hide();

});