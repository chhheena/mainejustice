jQuery(document).ready(function ($) {
  $(document).on('click', '.factory-ajax-link' , function (event) {
    event.preventDefault();

    var elem   = $(this);
    var value  = elem.val();
    var url    = elem.attr('data-url');
    var update = elem.attr('data-update');

    elem.trigger('getRequestData');

    var data = elem.attr('data-request');

    elem.val('Processing...');

    $.get(url, data, function (response) {
      var updateZone = $('#' + update);
      if (response.status) {
        updateZone.html(response.message);
      } else {
        updateZone.html(response.message + '<br />' + response.error);
      }

      elem.val(value);
    }, 'json');
  });
});
