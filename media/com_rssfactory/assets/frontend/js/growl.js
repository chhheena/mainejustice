/* https://github.com/ifightcrime/bootstrap-growl */

(function($) {
  $.bootstrapGrowl = function(message, options) {

    var options = $.extend({}, $.bootstrapGrowl.default_options, options);

    var $alert = $('<div>');

    $alert.attr('class', 'bootstrap-growl alert');

    if (options.type) {
        $alert.addClass('alert-' + options.type);
    }

    if (options.allow_dismiss) {
      $alert.append('<a class="close" data-dismiss="alert" href="#">&times;</a>');
    }

    $alert.append(message);

    // Prevent BC breaks
    if (options.top_offset) {
        options.offset = {from: 'top', amount: options.top_offset};
    }

    // calculate any 'stack-up'
    var offsetAmount = options.offset.amount;
    $('.bootstrap-growl').each(function() {
      offsetAmount = Math.max(offsetAmount, parseInt($(this).css(options.offset.from)) + $(this).outerHeight() + options.stackup_spacing);
    });

    css = {
      'position': (options.ele == 'body' ? 'fixed' : 'absolute'),
      'margin': 0,
      'z-index': '9999',
      'display': 'none'
    };
    css[options.offset.from] = offsetAmount + 'px';
    $alert.css(css);

    if (options.width !== 'auto') {
      $alert.css('width', options.width + 'px');
    }

    // have to append before we can use outerWidth()
    $(options.ele).append($alert);

    switch(options.align) {
      case 'center':
        $alert.css({
          'left': '50%',
          'margin-left': '-' + ($alert.outerWidth() / 2) + 'px'
        });
        break;
      case 'left':
        $alert.css('left', '20px');
        break;
      default:
        $alert.css('right', '20px');
    }

    $alert.fadeIn();
    // Only remove after delay if delay is more than 0
    if(options.delay > 0){
      $alert.delay(options.delay).fadeOut(function() {
        $(this).remove();
      });
    }

  };

  $.bootstrapGrowl.default_options = {
    ele: 'body',
    type: null,
    offset: {from: 'top', amount: 20},
    align: 'left', // (left, right, or center)
    width: 250,
    delay: 4000,
    allow_dismiss: true,
    stackup_spacing: 10
  };

  $.FactoryGrowlNotification = function (response) {
    if (response.status) {
      $.bootstrapGrowl('<b>' + response.message + '</b>', { type: 'success' });
    }
    else {
      $.bootstrapGrowl('<b>' + response.message + '</b><br />' + response.error, { type: 'error' });
    }
  }
})(jQuery);
