Drupal.behaviors.myBehavior = {
  attach: function (context, settings) {
    jQuery('.reload-form').click(function(e) {
      window.location.reload();
    });
  }
};