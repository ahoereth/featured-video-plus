jQuery(document).ready(function($){

  var $hiders = $('.hidden-when');

  for (var i = $hiders.length - 1; i >= 0; i--) {
    var $hider = $($hiders[i]);

    var who  = $hider.data('who');
        when = $hider.data('when');

    $('input[name="'+who+'"').change(function() {
      if ( $(this).val() == when && $(this).prop('checked') ) {
        $hider.addClass('hidden');
      } else {
        $hider.removeClass('hidden');
      }
    });
  };

  $('input[name="fvp-settings[usage]"]').change(function() {
    $this = $(this);

    if ( $this.val() ==  'manual' && $this.prop('checked') ) {
      $('.hidden-when-manual').addClass('hidden');
      $('.shown-when-manual').removeClass('hidden');
    } else {
      $('.hidden-when-manual').removeClass('hidden');
      $('.shown-when-manual').addClass('hidden');
    }
  });

  $('#fvp_help_toggle').bind( 'click', function() {
    $('#contextual-help-link').trigger('click');
  });

});
