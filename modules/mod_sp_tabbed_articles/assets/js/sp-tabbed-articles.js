/*------------------------------------------------------------------------
# mod_sp_tabbed_articles - Tabbed articles module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2015 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

jQuery(function($) {
	$('.sp-vertical-tabs').each(function(){
		var $tab = $(this),
		$handlers = $tab.find('.sp-tab-btns').children(),
		$tabs = $tab.find('.sp-tab-content').children();

		$handlers.each(function(index) {
			$(this).on('mouseenter', function(event){
				event.preventDefault();
				$handlers.removeClass('active');
				$tabs.removeClass('active');
				$(this).addClass('active');
				$($tabs[index]).addClass('active');
			});
		});
	});
});