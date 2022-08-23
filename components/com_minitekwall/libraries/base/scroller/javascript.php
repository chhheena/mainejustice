<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallLibBaseScrollerJavascript
{
	public function loadScrollerJavascript($scroller_params, $widgetID, $totalCount)
	{
		$document = JFactory::getDocument();
		$contain_type = $scroller_params['scr_contain_type'];

		$javascript = "jQuery(function(){";

			// Not contained scroller
			if ($contain_type == 'not_contained')
			{
				$javascript .= $this->initializeNotContained($scroller_params, $widgetID);

				// Media scroller
				if ($scroller_params['scr_type'] == 'media_scroller')
				{
					$javascript .= $this->initializeMediaScroller($widgetID);
				}

				// Reveal scroller
				if ($scroller_params['scr_type'] == 'reveal_scroller')
				{
					$javascript .= $this->initializeRevealScroller($widgetID);
				}
				else // Center selected
				{
					$javascript .= $this->centerSelectedItem($widgetID);
				}
			}

			// Contained scroller
			else if ($contain_type == 'contained')
			{
				$javascript .= $this->initializeContained($scroller_params, $widgetID);
			}

			// Hover box
			if ($scroller_params['scr_hb'])
			{
				$javascript .= $this->initializeHoverBox($scroller_params['scr_hb_effect'], $widgetID);
			}

			// Arrows
			if ($scroller_params['scr_arrows'])
			{
				$javascript .= $this->initializeFixArrows($scroller_params, $widgetID);
			}

		$javascript .= "});";

		//$document->addScriptDeclaration($javascript);
		$document->addCustomTag('<script type="text/javascript">'.$javascript.'</script>');
	}

	public function initializeNotContained($scroller_params, $widgetID)
	{
		$contain = $scroller_params['scr_contain_type'];
		if ($contain) {
			$contain = 'true';
		} else {
			$contain = 'false';
		}

		$cellalign = $scroller_params['scr_cell_align'];

		$rtl = $scroller_params['scr_rtl'];
		if ($rtl) {
			$rtl = 'true';
		} else {
			$rtl = 'false';
		}

		$drag = $scroller_params['scr_drag'];
		if ($drag) {
			$drag = 'true';
		} else {
			$drag = 'false';
		}

		$freescroll = $scroller_params['scr_free_scroll'];
		if ($freescroll) {
			$freescroll = 'true';
		} else {
			$freescroll = 'false';
		}

		$rewind_nav = $scroller_params['scr_rewind'];
		if ($rewind_nav) {
			$rewind_nav = 'true';
		} else {
			$rewind_nav = 'false';
		}

		$autoplay_speed = (int)$scroller_params['scr_autoplay_speed'];
		if ($scroller_params['scr_autoplay']) {
			$autoplay = $autoplay_speed;
		} else {
			$autoplay = 'false';
		}

		$nav_arrow = $scroller_params['scr_arrows'];
		if ($nav_arrow) {
			$nav_arrow = 'true';
		} else {
			$nav_arrow = 'false';
		}

		$pagination = $scroller_params['scr_bullets'];
		if ($pagination) {
			$pagination = 'true';
		} else {
			$pagination = 'false';
		}

		$javascript = "

			// Show hidden scroller
			jQuery('#mnwall_scr_".$widgetID."').show();

			// Initialize scroller
			var _carousel = jQuery('#mnwall_scr_".$widgetID."').flickity({

				setGallerySize: true,
				resize: true,
				percentPosition: false,
				contain: ".$contain.",
				cellAlign: '".$cellalign."',
				rightToLeft: ".$rtl.",
				draggable: ".$drag.",
				freeScroll: ".$freescroll.",
				wrapAround: ".$rewind_nav.",
				autoPlay: ".$autoplay.",
				prevNextButtons: ".$nav_arrow.",
				pageDots: ".$pagination.",
				imagesLoaded: true,
				cellSelector: '.mnwall-scr-item',
				initialIndex: 0,
				accessibility: true

			});

			_carousel.flickity('reposition');
			_carousel.flickity('resize');

		";

		return $javascript;
	}

	public function centerSelectedItem($widgetID)
	{
		$javascript = "

			_carousel.on( 'staticClick', function( event, pointer, cellElement, cellIndex ) {
				if ( typeof cellIndex == 'number' ) {
				  	_carousel.flickity( 'select', cellIndex );
				}
			});

		";

		return $javascript;
	}

	public function initializeMediaScroller($widgetID)
	{
		$javascript = "

			jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-media-db').insertAfter('#mnwall_scr_".$widgetID." .flickity-viewport');

			jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-detail-box').each(function(index){
				jQuery(this).attr('data-selectedindex', index);
			});

			var flkty = _carousel.data('flickity');
			_carousel.on( 'cellSelect', function() {
				jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-detail-box').hide();
				jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-detail-box[data-selectedindex=\"'+ flkty.selectedIndex +'\"]').show();
			})

		";

		return $javascript;
	}

	public function initializeRevealScroller($widgetID)
	{
		$javascript = "

			_carousel.on( 'staticClick', function( event, pointer, cellElement, cellIndex )
			{
				if ( !cellElement )
				{
					return;
				}
				jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-item').removeClass('is-expanded');
				jQuery( cellElement ).addClass('is-expanded');
				_carousel.flickity('reposition');
				_carousel.flickity('resize');

				if ( typeof cellIndex == 'number' ) {
				  	_carousel.flickity( 'select', cellIndex );
				}
			});

			jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-item').on( 'click', '.mnw-close-reveal', function(event)
			{
				jQuery('#mnwall_scr_".$widgetID." .mnwall-scr-item').removeClass('is-expanded');
				_carousel.flickity('reposition');
			});

		";

		return $javascript;
	}

	public function initializeHoverBox($hoverBoxEffect, $widgetID)
	{
		$javascript = "

			// Hover effects
			var hoverBoxEffect = '".$hoverBoxEffect."';

			// Hover box trigger
			var triggerHoverBox = function triggerHoverBox() {

				// Hover effects
				jQuery('#mnwall_scr_".$widgetID."').find('.mnwall-scr-item')
				.mouseenter(function(e) {

					if (hoverBoxEffect == 'no') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('hoverShow');
					}
					if (hoverBoxEffect == '1') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('hoverFadeIn');
					}
					if (hoverBoxEffect == '2') {
						jQuery(this).stop().addClass('perspective');
						jQuery(this).find('.mnwall-scr-item-outer-cont').stop().addClass('flip flipY hoverFlipY');
					}
					if (hoverBoxEffect == '3') {
						jQuery(this).stop().addClass('perspective');
						jQuery(this).find('.mnwall-scr-item-outer-cont').stop().addClass('flip flipX hoverFlipX');
					}
					if (hoverBoxEffect == '4') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('animated slideInRight');
					}
					if (hoverBoxEffect == '5') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('animated slideInLeft');
					}
					if (hoverBoxEffect == '6') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('animated slideInTop');
					}
					if (hoverBoxEffect == '7') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('animated slideInBottom');
					}
					if (hoverBoxEffect == '8') {
						jQuery(this).find('.mnwall-hover-box').stop().addClass('animated mnwzoomIn');
					}

				}).mouseleave(function (e) {

					if (hoverBoxEffect == 'no') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('hoverShow');
					}
					if (hoverBoxEffect == '1') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('hoverFadeIn');
					}
					if (hoverBoxEffect == '2') {
						jQuery(this).find('.mnwall-scr-item-outer-cont').stop().removeClass('hoverFlipY');
					}
					if (hoverBoxEffect == '3') {
						jQuery(this).find('.mnwall-scr-item-outer-cont').stop().removeClass('hoverFlipX');
					}
					if (hoverBoxEffect == '4') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('slideInRight');
					}
					if (hoverBoxEffect == '5') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('slideInLeft');
					}
					if (hoverBoxEffect == '6') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('slideInTop');
					}
					if (hoverBoxEffect == '7') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('slideInBottom');
					}
					if (hoverBoxEffect == '8') {
						jQuery(this).find('.mnwall-hover-box').stop().removeClass('mnwzoomIn');
					}

				});

			};

			triggerHoverBox();

		";

		return $javascript;
	}

	public function initializeFixArrows($scroller_params, $widgetID)
	{
		$javascript = "

			jQuery('#mnwall_scr_".$widgetID." .flickity-prev-next-button.previous').html('<i class=\"fa fa-".$scroller_params['scr_arrows_list']."-left\"></i>');
			jQuery('#mnwall_scr_".$widgetID." .flickity-prev-next-button.next').html('<i class=\"fa fa-".$scroller_params['scr_arrows_list']."-right\"></i>');

		";

		return $javascript;
	}
}
