<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

class MinitekWallLibBaseMasonryPagination
{
	var $utilities = null;

	function __construct()
	{
		$this->utilities = new MinitekWallLibUtilities;

		return;
	}

	public function getPaginationCss($masonry_params, $widgetID)
	{
		if ($masonry_params['mas_pagination'] == '1' || $masonry_params['mas_pagination'] == '4')
		{
			$this->getAppendCss($masonry_params, $widgetID);
		}
		if ($masonry_params['mas_pagination'] == '2')
		{
			$this->getArrowsCss($masonry_params, $widgetID);
		}
		if ($masonry_params['mas_pagination'] == '3')
		{
			$this->getPagesCss($masonry_params, $widgetID);
		}
	}

	public function getAppendCss($masonry_params, $widgetID)
	{
		$document = JFactory::getDocument();
		$mnwall = 'mnwall_container_'.$widgetID;

		$bg = $masonry_params['mas_pagination_bg'];
		$color = $masonry_params['mas_pagination_color'];
		$border_radius = (int)$masonry_params['mas_pagination_border_radius'];

		$css = '
		#'.$mnwall.' a.more-results,
		#'.$mnwall.' a.mnwall-reset-btn {
			background-color: '.$bg.';
			border-color: '.$bg.';
			border-radius: '.$border_radius.'px;
			color: '.$color.';
		}
		#'.$mnwall.' a.more-results.disabled {
			background-color: '.$bg.' !important;
			border-color: '.$bg.' !important;
			border-radius: '.$border_radius.'px !important;
			color: '.$color.' !important;
		}
		#'.$mnwall.' a.more-results.mnwall-loading {
			border-color: '.$bg.';
		}
		';

		$document->addStyleDeclaration( $css );
	}

	public function getArrowsCss($masonry_params, $widgetID)
	{
		$document = JFactory::getDocument();
		$mnwall = 'mnwall_container_'.$widgetID;

		$bg = $masonry_params['mas_pagination_bg'];
		$color = $masonry_params['mas_pagination_color'];
		$border_radius = (int)$masonry_params['mas_pagination_border_radius'];

		$css = '
		#'.$mnwall.' a.mnwall_arrow,
		#'.$mnwall.' a.mnwall-reset-btn {
			background-color: '.$bg.';
			border-color: '.$bg.';
			border-radius: '.$border_radius.'px;
			color: '.$color.';
		}
		#'.$mnwall.' a.mnwall_arrow.disabled {
			background-color: '.$bg.' !important;
			border-color: '.$bg.' !important;
			border-radius: '.$border_radius.'px !important;
			color: '.$color.' !important;
		}
		#'.$mnwall.' a.mnwall_arrow.mnwall-loading {
			border-color: '.$bg.';
		}
		';

		$document->addStyleDeclaration( $css );
	}

	public function getPagesCss($masonry_params, $widgetID)
	{
		$document = JFactory::getDocument();
		$mnwall = 'mnwall_container_'.$widgetID;

		$bg = $masonry_params['mas_pagination_bg'];
		$color = $masonry_params['mas_pagination_color'];
		$border_radius = (int)$masonry_params['mas_pagination_border_radius'];

		$css = '
		#'.$mnwall.' a.mnwall_page {
			border-radius: '.$border_radius.'px;
		}
		#'.$mnwall.' a.mnwall_page.mnw_active,
		#'.$mnwall.' a.mnwall-reset-btn {
			background-color: '.$bg.';
			border-color: '.$bg.';
			color: '.$color.';
		}
		';

		$document->addStyleDeclaration( $css );
	}
}
