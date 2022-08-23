<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallLibBaseScrollerResponsive
{
	public function loadResponsiveScroller($scroller_params, $widgetID)
	{
		$document = JFactory::getDocument();
		$mnwall = 'mnwall_scr_'.$widgetID;

		// Not contained scroller
		if ($scroller_params['scr_contain_type'] == 'not_contained')
		{
			// Item width
			$item_width = '
				#'.$mnwall.' .mnwall-scr-item {
					width: '.number_format((float)$scroller_params['scr_highlight_width'], 4, '.', '').'%;
				}
			';
			$document->addStyleDeclaration( $item_width );

			// Reveal scroller
			if ($scroller_params['scr_type'] == 'reveal_scroller')
			{
				$reveal_width = '
					@media only screen and (max-width:'.(int)$scroller_params['scr_full_width_limit'].'px)
					{
						#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-cover {
							height: auto !important;
						}
						#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-item.is-expanded .mnwall-scr-img-div {
							width: 100%;
							margin: 0;
						}
						#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-item .mnwall-scr-detail-box {
							position: absolute;
							z-index: 3;
							top: 0;
							left: 0;
							height: 100%;
							overflow: hidden;
						}
						#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-item.is-expanded .mnwall-scr-detail-box {
							width: 100%;
						}
						#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-photo-link img {
							max-height: inherit;
							width: 100%;
							height: auto;
							position: static;
						}
					}
					#'.$mnwall.'.mnwall_reveal_scroller .mnwall-scr-item.is-expanded {
						width: 100%;
					}
				';
				$document->addStyleDeclaration( $reveal_width );
			}

			// Full width limit
			$full_width = '@media only screen and (max-width:'.(int)$scroller_params['scr_full_width_limit'].'px)
			{
				#'.$mnwall.' .mnwall-scr-item {
					width: 100%;
				}
			}
			';
			$document->addStyleDeclaration( $full_width );
		}

		// Bullets color
		$arrows_color = $scroller_params['scr_arrows_color'];
		$arrows_color_css = '
			#'.$mnwall.' .flickity-prev-next-button {
				color: '.$arrows_color.';
			}
		';
		$document->addStyleDeclaration( $arrows_color_css );

		// Bullets color
		$bullets_color = $scroller_params['scr_bullets_color'];
		$bullets_color_css = '
			#'.$mnwall.' .flickity-page-dots .dot {
				background: '.$bullets_color.';
			}
		';
		$document->addStyleDeclaration( $bullets_color_css );
	}
}
