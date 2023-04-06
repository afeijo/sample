(function ($, Drupal) {
  Drupal.behaviors.movie = {
    attach: function (context, settings) {
      var today = new Date();
      // Set the max date to prevent release dates in the future.
      $('[id^=edit-field-release-date]').attr('max', today.toISOString().split('T')[0]);
    }
  };
})(jQuery, Drupal);
