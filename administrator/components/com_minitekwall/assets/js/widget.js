(function( $ ) {
	'use strict';

	$(function() {

		newWidgetName();
		checkGridRadio();
		checkScrollerRadio();

	});

	function newWidgetName()
	{
		if ($('#jform_name').val() == '')
		{
			$('#jform_name').val('widget '+$.now());
		}

		$('#jform_name').focus(function() {
			if ($('#jform_name').val() == '')
			{
				$('#jform_name').val('widget '+$.now());
			}
		});

		$('#jform_name').blur(function() {
			if ($('#jform_name').val() == '')
			{
				$('#jform_name').val('widget '+$.now());
			}
		});
	}

	function checkGridRadio()
	{
		$('.grid-radio-input:checked').parents('.grid-radio').addClass('active');

		$('.grid-radio-input').change(function() {
			$(this).parents('.controls').find('.grid-radio').removeClass('active');
			var checked = $(this).attr('checked', true);

			if (checked) {
				$(this).parents('.grid-radio').addClass('active');
			}
		});
	}

	function checkScrollerRadio()
	{
		$('.scroller-radio-input:checked').parents('.scroller-radio').addClass('active');

		$('.scroller-radio-input').change(function() {
			$(this).parents('.controls').find('.scroller-radio').removeClass('active');
	  	var checked = $(this).attr('checked', true);

	  	if (checked) {
				$(this).parents('.scroller-radio').addClass('active');
	  	}
		});
	}

})( jQuery );
