jQuery(document).ready(function ($) {
  // Story voting.
  $(document).on('click', '.story-vote-up, .story-vote-down', function (event) {
    event.preventDefault();

    var elem = $(this);
    var url  = elem.attr('href');

    $.get(url, function (response) {
      if (response.status) {
        var counter = $('.story-' + response.storyId + ' .story-votes-counter');

        counter.html(response.rating);
        elem.removeClass('muted');

        $.bootstrapGrowl(response.message, { type: 'success' });
      } else {
        $.bootstrapGrowl('<b>' + response.message + '</b><br />' + response.error, { type: 'error' });
      }
    }, 'json');
  });

  // Ajax pagination.
  $(document).on('click', '.feed-stories .pagination a', function (event) {
    event.preventDefault();

    var elem       = $(this);
    var url        = elem.attr('href');
    var parent     = elem.parents('div.feed:first');
    var container  = parent.find('div.feed-stories');
    var loader     = parent.find('div.progress');
    var pagination = parent.find('div.pagination');

    if ('undefined' == typeof url) {
      return false;
    }

    pagination.hide();
    loader.show();

    //$('body').append('<div class="progress progress-striped active" style="display: none; position: fixed; top: 0; right: 0; z-index: 9999;"><div class="bar" style="width: 100%;"></div></div>');

    $.get(url, { format: 'raw' }, function (response) {
      container.replaceWith(response);
    });
  });

  // Table feeds.
  $(document).on('click', 'a.feed-table', function (event) {
    event.preventDefault();

    $(this).parent().find('.well').toggle();
  });

  // Modal feeds.
  $(document).on('click', 'a.feed-modal', function (event) {
    event.preventDefault();

    $(this).parent().find('.modal').modal({ backdrop: true, keyboard: true, show: true, remote: ''});
  });

  // Feed bookmarking.
  $('.story-bookmark').click(function (event) {
    event.preventDefault();

    var elem = $(this);
    var url  = elem.attr('href');

    $.get(url, function (response) {
      if (response.status) {
        $('.feed-title-bookmarked', '.feed-' + response.feedId).toggle();
        $('.story-bookmark', '.feed-' + response.feedId).attr('href', response.url);
        $('.story-bookmark-icon', '.feed-' + response.feedId).toggle();

        $.bootstrapGrowl(response.message, { type: 'success' });
      } else {
        $.bootstrapGrowl('<b>' + response.message + '</b><br />' + response.error, { type: 'error' });
      }
    }, 'json');
  });
});
