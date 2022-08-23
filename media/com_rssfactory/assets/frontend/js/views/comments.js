jQuery(document).ready(function ($) {
  // Comment delete.
  $('.comment-delete').click(function (event) {
    event.preventDefault();

    var elem = $(this);
    var url  = elem.attr('href');

    $.get(url, function (response) {
      if (response.status) {
        elem.parents('div.well:first').fadeOut();
      }

      $.FactoryGrowlNotification(response);
    }, 'json');
  });

  // Comment edit.
  $('.comment-edit').click(function (event) {
    event.preventDefault();

    var elem    = $(this);
    var html    = elem.parents('div.rssfactory-comments:first').attr('data-prototype-update');
    var comment = elem.parents('div.comment:first');
    var text    = comment.find('p:first').html().replace(/(<([^>]+)>)/ig, '');

    comment.hide().parent().append(html).find('textarea:first').val(text);
  });

  // Comment edit cancel.
  $(document).on('click', '.comment-cancel', function (event) {
    event.preventDefault();

    var elem    = $(this);
    var comment = elem.parents('div.well:first').find('.comment');

    comment.show();
    elem.parent().remove();
  });

  // Comment update.
  $(document).on('click', '.comment-update', function (event) {
    event.preventDefault();

    var elem = $(this);
    var text = elem.parents('div:first').find('textarea:first').val();
    var id   = elem.parents('div.well:first').attr('id').replace('comment-', '');
    var comment = elem.parents('div.well:first').find('.comment:first');

    $.post('index.php?option=com_rssfactory&format=raw&task=comment.update', { comment_id: id, text: text}, function (response) {
      $.FactoryGrowlNotification(response);

      if (response.status) {
        elem.parents('div:first').remove();
        comment.show().find('p:first').html(response.text);
      }

    }, 'json');
  });
});
