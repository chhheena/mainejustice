jQuery(document).ready(function ($) {
  $(window).resize(function() {
    var iframe = $('iframe:first');
    var navbar = $('.navbar');
    var height = $(document).height() - navbar.height();

    iframe.css('height', height + 'px');
    $('body').css('overflow', 'hidden');
  }).resize();
});
