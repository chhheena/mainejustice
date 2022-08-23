<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class MinitekWallViewMasonry extends JViewLegacy
{
	public function display($tpl = null)
	{
 		$document = JFactory::getDocument();
		$this->model = $this->getModel();
		$masonry_options = $this->model->masonry_options;
		$this->assignRef('masonry_options', $masonry_options);
		$utilities = $this->model->utilities;
		$this->assignRef('utilities', $utilities);
		$params = $utilities->getParams('com_minitekwall');
		$this->assignRef('params', $params);
		$jinput = JFactory::getApplication()->input;
		$widgetID = $jinput->get('widget_id', '', 'INT');
		$page = $jinput->get('page', '1', 'INT');

		// Get masonry parameters
		$masonry_params = $utilities->getMasonryParams($widgetID);
		$this->assignRef('masonry_params', $masonry_params);

		if (!isset($masonry_params['mas_grid']))
		{
			JError::raiseError(404, JText::_('COM_MINITEKWALL_WIDGET_NOT_FOUND'));
			return false;
		}
		// If component was upgraded from free version, make sure the widget is saved again.
		else if (!isset($masonry_params['mas_page_limit']))
		{
			JError::raiseError(404, JText::_('COM_MINITEKWALL_WIDGET_MUST_BE_SAVED'));
			return false;
		}

		// Get Grid
		$gridType = $masonry_params['mas_grid'];
		$suffix = '';
		if (array_key_exists('mas_suffix', $masonry_params))
		{
			$suffix = $masonry_params['mas_suffix'];
		}
		$masCols = $masonry_params['mas_cols'];
		$masColsper = 100 / $masCols;
		$gutter = $masonry_params['mas_gutter'];
		$mas_border_radius = (int)$masonry_params['mas_border_radius'];
		$mas_border = (int)$masonry_params['mas_border'];
		$mas_border_color = $masonry_params['mas_border_color'];

		// Layout/Grid class
		if ($gridType == '99v')
		{
			$mnwall_layout = 'list';
			$mnwall_grid = '';
		}
		else if ($gridType == '98o')
		{
			$mnwall_layout = 'columns';
			$mnwall_grid = '';
		}
		else
		{
			$mnwall_layout = 'masonry';
			$mnwall_grid = 'mnwall-grid'.$gridType;
		}

		// Columns
		$cols = '';
		$masColsper = number_format((float)$masColsper, 4, '.', '');
		$cols = 'width: '.$masColsper.'%;';

		// Images
		$mas_images = $masonry_params['mas_images'];
		$this->mas_image_link = true;
		if (array_key_exists('mas_image_link', $masonry_params))
		{
			$this->mas_image_link = $masonry_params['mas_image_link'];
		}
		$mas_crop_images = $masonry_params['mas_crop_images'];
		$mas_image_width = $masonry_params['mas_image_width'];
		$mas_image_height = $masonry_params['mas_image_height'];
		$full_width_image = $masonry_params['mas_full_width_image'];

		// Get Total count - Arrows pagination
		$startLimit = $masonry_params['mas_starting_limit'];
		$pageLimit = $masonry_params['mas_page_limit'];

		if ($page === '1')
		{
			// Get pagination
			$pagination = $masonry_params['mas_pagination'];
			if ($pagination)
			{
				$masonry_pagination = $this->model->masonry_pagination;
				$masonry_pagination->getPaginationCss($masonry_params, $widgetID);
			}
			$this->mas_pag_reset_filters = false;
			if (array_key_exists('mas_pag_reset_filters', $masonry_params) && $masonry_params['mas_pag_reset_filters'])
			{
				$this->mas_pag_reset_filters = true;
			}

			$globalLimit = $masonry_params['mas_global_limit'];
			if ($startLimit > $globalLimit)
			{
				$startLimit = $globalLimit;
			}
			$totalCount = $this->model->getAllResultsCount($widgetID);
			$remainingCount = $totalCount - $startLimit;
			$showRemaining = $masonry_params['mas_pag_remaining'];
			if ($totalCount < $startLimit)
			{
				$startLimit = $totalCount;
			}
			$lastPage = ceil(($totalCount - $startLimit) / $pageLimit);

			// Pages pagination
			$totalPages = ceil(($totalCount - $startLimit) / $pageLimit) + 1;
			$this->assignRef('totalPages', $totalPages);

			// Get arrows
			$arrows = $masonry_params['mas_arrows'];

			// Load masonry css
			$document->addStyleSheet(JURI::base(true).'/components/com_minitekwall/assets/css/masonry.css?v=3.9.2.1');

			// Add scripts
			$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/imagesloaded.pkgd.min.js" type="text/javascript"></script>');
			$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/isotope.pkgd.min.js" type="text/javascript"></script>');
			$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/packery-mode.pkgd.min.js" type="text/javascript"></script>');
			if ($pagination == '4')
			{
				$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/jquery.visible.min.js" type="text/javascript"></script>');
				$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/jquery.inview.min.js" type="text/javascript"></script>');
			}
			$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/js/spin.min.js" type="text/javascript"></script>');

			// Get lightbox
			$lightbox = false;
			if ($masonry_params['mas_hb'] && isset($masonry_params['mas_hb_lightbox']) && $masonry_params['mas_hb_lightbox'] && $params->get('load_lightbox', true))
			{
				$lightbox = true;
				$document->addStyleSheet(JURI::base(true).'/components/com_minitekwall/assets/lightbox/lightbox.min.css');
				$document->addCustomTag('<script src="'.JURI::base(true).'/components/com_minitekwall/assets/lightbox/lightbox.min.js" type="text/javascript"></script>');
			}

			// Add javascript.php
			$masonry_javascript = $this->model->masonry_javascript;
			$masonry_javascript->loadMasonryJavascript($masonry_params, $widgetID, $totalCount);
		}

		// Detail box
		$detailBoxTitleLimit = $masonry_params['mas_db_title_limit'];
		$this->detailBoxTitleLink = true;
		if (array_key_exists('mas_db_title_link', $masonry_params))
		{
			$this->detailBoxTitleLink = $masonry_params['mas_db_title_link'];
		}
		$detailBoxIntrotextLimit = $masonry_params['mas_db_introtext_limit'];
		$detailBoxStripTags = $masonry_params['mas_db_strip_tags'];
		$detailBoxDateFormat = $masonry_params['mas_db_date_format'];

		// Big
		$detailBoxBig = $masonry_params['mas_db_big'];
		$detailBoxPositionBig = $masonry_params['mas_db_position_big'];
		$detailBoxBackgroundBig = $masonry_params['mas_db_bg_big'];
		$detailBoxBackgroundOpacityBig = $masonry_params['mas_db_bg_opacity_big'];
		$detailBoxTextColorBig = $masonry_params['mas_db_color_big'];
		$detailBoxTitleBig = $masonry_params['mas_db_title_big'];
		$detailBoxIntrotextBig = $masonry_params['mas_db_introtext_big'];
		$detailBoxDateBig = $masonry_params['mas_db_date_big'];
		$detailBoxCategoryBig = $masonry_params['mas_db_category_big'];
		$detailBoxLocationBig = $masonry_params['mas_db_location_big'];
		$detailBoxTypeBig = $masonry_params['mas_db_content_type_big'];
		$detailBoxAuthorBig = $masonry_params['mas_db_author_big'];
		$detailBoxTagsBig = isset($masonry_params['mas_db_tags_big']) ? $masonry_params['mas_db_tags_big'] : false;
		$detailBoxPriceBig = $masonry_params['mas_db_price_big'];
		$detailBoxHitsBig = $masonry_params['mas_db_hits_big'];
		$detailBoxCountBig = $masonry_params['mas_db_count_big'];
		$detailBoxReadmoreBig = $masonry_params['mas_db_readmore_big'];

		// Landscape
		$detailBoxLscape = $masonry_params['mas_db_lscape'];
		$detailBoxPositionLscape = $masonry_params['mas_db_position_lscape'];
		$detailBoxBackgroundLscape = $masonry_params['mas_db_bg_lscape'];
		$detailBoxBackgroundOpacityLscape = $masonry_params['mas_db_bg_opacity_lscape'];
		$detailBoxTextColorLscape = $masonry_params['mas_db_color_lscape'];
		$detailBoxTitleLscape = $masonry_params['mas_db_title_lscape'];
		$detailBoxIntrotextLscape = $masonry_params['mas_db_introtext_lscape'];
		$detailBoxDateLscape = $masonry_params['mas_db_date_lscape'];
		$detailBoxCategoryLscape = $masonry_params['mas_db_category_lscape'];
		$detailBoxLocationLscape = $masonry_params['mas_db_location_lscape'];
		$detailBoxTypeLscape = $masonry_params['mas_db_content_type_lscape'];
		$detailBoxAuthorLscape = $masonry_params['mas_db_author_lscape'];
		$detailBoxTagsLscape = isset($masonry_params['mas_db_tags_lscape']) ? $masonry_params['mas_db_tags_lscape'] : false;
		$detailBoxPriceLscape = $masonry_params['mas_db_price_lscape'];
		$detailBoxHitsLscape = $masonry_params['mas_db_hits_lscape'];
		$detailBoxCountLscape = $masonry_params['mas_db_count_lscape'];
		$detailBoxReadmoreLscape = $masonry_params['mas_db_readmore_lscape'];

		// Portrait
		$detailBoxPortrait = $masonry_params['mas_db_portrait'];
		$detailBoxPositionPortrait = $masonry_params['mas_db_position_portrait'];
		$detailBoxBackgroundPortrait = $masonry_params['mas_db_bg_portrait'];
		$detailBoxBackgroundOpacityPortrait = $masonry_params['mas_db_bg_opacity_portrait'];
		$detailBoxTextColorPortrait = $masonry_params['mas_db_color_portrait'];
		$detailBoxTitlePortrait = $masonry_params['mas_db_title_portrait'];
		$detailBoxIntrotextPortrait = $masonry_params['mas_db_introtext_portrait'];
		$detailBoxDatePortrait = $masonry_params['mas_db_date_portrait'];
		$detailBoxCategoryPortrait = $masonry_params['mas_db_category_portrait'];
		$detailBoxLocationPortrait = $masonry_params['mas_db_location_portrait'];
		$detailBoxTypePortrait = $masonry_params['mas_db_content_type_portrait'];
		$detailBoxAuthorPortrait = $masonry_params['mas_db_author_portrait'];
		$detailBoxTagsPortrait = isset($masonry_params['mas_db_tags_portrait']) ? $masonry_params['mas_db_tags_portrait'] : false;
		$detailBoxPricePortrait = $masonry_params['mas_db_price_portrait'];
		$detailBoxHitsPortrait = $masonry_params['mas_db_hits_portrait'];
		$detailBoxCountPortrait = $masonry_params['mas_db_count_portrait'];
		$detailBoxReadmorePortrait = $masonry_params['mas_db_readmore_portrait'];

		// Small
		$detailBoxSmall = $masonry_params['mas_db_small'];
		$detailBoxPositionSmall = $masonry_params['mas_db_position_small'];
		$detailBoxBackgroundSmall = $masonry_params['mas_db_bg_small'];
		$detailBoxBackgroundOpacitySmall = $masonry_params['mas_db_bg_opacity_small'];
		$detailBoxTextColorSmall = $masonry_params['mas_db_color_small'];
		$detailBoxTitleSmall = $masonry_params['mas_db_title_small'];
		$detailBoxIntrotextSmall = $masonry_params['mas_db_introtext_small'];
		$detailBoxDateSmall = $masonry_params['mas_db_date_small'];
		$detailBoxCategorySmall = $masonry_params['mas_db_category_small'];
		$detailBoxLocationSmall = $masonry_params['mas_db_location_small'];
		$detailBoxTypeSmall = $masonry_params['mas_db_content_type_small'];
		$detailBoxAuthorSmall = $masonry_params['mas_db_author_small'];
		$detailBoxTagsSmall = isset($masonry_params['mas_db_tags_small']) ? $masonry_params['mas_db_tags_small'] : false;
		$detailBoxPriceSmall = $masonry_params['mas_db_price_small'];
		$detailBoxHitsSmall = $masonry_params['mas_db_hits_small'];
		$detailBoxCountSmall = $masonry_params['mas_db_count_small'];
		$detailBoxReadmoreSmall = $masonry_params['mas_db_readmore_small'];

		// Columns
		$detailBoxColumns = $masonry_params['mas_db_columns'];
		$detailBoxPositionColumns = $masonry_params['mas_db_position_columns'];
		$detailBoxBackgroundColumns = $masonry_params['mas_db_bg_columns'];
		$detailBoxBackgroundOpacityColumns = $masonry_params['mas_db_bg_opacity_columns'];
		$detailBoxTextColorColumns = $masonry_params['mas_db_color_columns'];
		$detailBoxTitleColumns = $masonry_params['mas_db_title_columns'];
		$detailBoxIntrotextColumns = $masonry_params['mas_db_introtext_columns'];
		$detailBoxDateColumns = $masonry_params['mas_db_date_columns'];
		$detailBoxCategoryColumns = $masonry_params['mas_db_category_columns'];
		$detailBoxLocationColumns = $masonry_params['mas_db_location_columns'];
		$detailBoxTypeColumns = $masonry_params['mas_db_content_type_columns'];
		$detailBoxAuthorColumns = $masonry_params['mas_db_author_columns'];
		$detailBoxTagsColumns = isset($masonry_params['mas_db_tags_columns']) ? $masonry_params['mas_db_tags_columns'] : false;
		$detailBoxPriceColumns = $masonry_params['mas_db_price_columns'];
		$detailBoxHitsColumns = $masonry_params['mas_db_hits_columns'];
		$detailBoxCountColumns = $masonry_params['mas_db_count_columns'];
		$detailBoxReadmoreColumns = $masonry_params['mas_db_readmore_columns'];

		if ($page === '1')
		{
			// Responsive Utilities
			$responsive_masonry = $this->model->responsive_masonry;
			if (array_key_exists('mas_enable_responsive', $masonry_params))
			{
				if ($masonry_params['mas_enable_responsive'])
				{
					$responsive_masonry->loadResponsiveMasonry($masonry_params, $widgetID);
				}
			}
			else
			{
				$responsive_masonry->loadResponsiveMasonry($masonry_params, $widgetID);
			}
		}

		// Detail box overall vars
		$detailBoxAll = true;
		$detailBoxTitleAll = true;
		$detailBoxIntrotextAll = true;
		$detailBoxDateAll = true;
		$detailBoxCategoryAll = true;
		$detailBoxLocationAll = true;
		$detailBoxTypeAll = true;
		$detailBoxAuthorAll = true;
		$detailBoxTagsAll = true;
		$detailBoxPriceAll = true;
		$detailBoxHitsAll = true;
		$detailBoxCountAll = true;
		$detailBoxReadmoreAll = true;
		if ((int)$gridType != '98' && (int)$gridType != '99')
		{
			if (!$detailBoxBig && !$detailBoxLscape && !$detailBoxPortrait && !$detailBoxSmall && !$detailBoxColumns)
			{
				$detailBoxAll = false;
			}
			if (!$detailBoxTitleBig && !$detailBoxTitleLscape && !$detailBoxTitlePortrait && !$detailBoxTitleSmall && !$detailBoxTitleColumns)
			{
				$detailBoxTitleAll = false;
			}
			if (!$detailBoxIntrotextBig && !$detailBoxIntrotextLscape && !$detailBoxIntrotextPortrait && !$detailBoxIntrotextSmall && !$detailBoxIntrotextColumns)
			{
				$detailBoxIntrotextAll = false;
			}
			if (!$detailBoxDateBig && !$detailBoxDateLscape && !$detailBoxDatePortrait && !$detailBoxDateSmall && !$detailBoxDateColumns)
			{
				$detailBoxDateAll = false;
			}
			if (!$detailBoxCategoryBig && !$detailBoxCategoryLscape && !$detailBoxCategoryPortrait && !$detailBoxCategorySmall && !$detailBoxCategoryColumns)
			{
				$detailBoxCategoryAll = false;
			}
			if (!$detailBoxLocationBig && !$detailBoxLocationLscape && !$detailBoxLocationPortrait && !$detailBoxLocationSmall && !$detailBoxLocationColumns)
			{
				$detailBoxLocationAll = false;
			}
			if (!$detailBoxTypeBig && !$detailBoxTypeLscape && !$detailBoxTypePortrait && !$detailBoxTypeSmall && !$detailBoxTypeColumns)
			{
				$detailBoxTypeAll = false;
			}
			if (!$detailBoxAuthorBig && !$detailBoxAuthorLscape && !$detailBoxAuthorPortrait && !$detailBoxAuthorSmall && !$detailBoxAuthorColumns)
			{
				$detailBoxAuthorAll = false;
			}
			if (!$detailBoxTagsBig && !$detailBoxTagsLscape && !$detailBoxTagsPortrait && !$detailBoxTagsSmall && !$detailBoxTagsColumns)
			{
				$detailBoxTagsAll = false;
			}
			if (!$detailBoxPriceBig && !$detailBoxPriceLscape && !$detailBoxPricePortrait && !$detailBoxPriceSmall && !$detailBoxPriceColumns)
			{
				$detailBoxPriceAll = false;
			}
			if (!$detailBoxHitsBig && !$detailBoxHitsLscape && !$detailBoxHitsPortrait && !$detailBoxHitsSmall && !$detailBoxHitsColumns)
			{
				$detailBoxHitsAll = false;
			}
			if (!$detailBoxCountBig && !$detailBoxCountLscape && !$detailBoxCountPortrait && !$detailBoxCountSmall && !$detailBoxCountColumns)
			{
				$detailBoxCountAll = false;
			}
			if (!$detailBoxReadmoreBig && !$detailBoxReadmoreLscape && !$detailBoxReadmorePortrait && !$detailBoxReadmoreSmall && !$detailBoxReadmoreColumns)
			{
				$detailBoxReadmoreAll = false;
			}
		}
		else
		{
			if (!$detailBoxColumns)
			{
				$detailBoxAll = false;
			}
			if (!$detailBoxTitleColumns)
			{
				$detailBoxTitleAll = false;
			}
			if (!$detailBoxIntrotextColumns)
			{
				$detailBoxIntrotextAll = false;
			}
			if (!$detailBoxDateColumns)
			{
				$detailBoxDateAll = false;
			}
			if (!$detailBoxCategoryColumns)
			{
				$detailBoxCategoryAll = false;
			}
			if (!$detailBoxLocationColumns)
			{
				$detailBoxLocationAll = false;
			}
			if (!$detailBoxTypeColumns)
			{
				$detailBoxTypeAll = false;
			}
			if (!$detailBoxAuthorColumns)
			{
				$detailBoxAuthorAll = false;
			}
			if (!$detailBoxTagsColumns)
			{
				$detailBoxTagsAll = false;
			}
			if (!$detailBoxPriceColumns)
			{
				$detailBoxPriceAll = false;
			}
			if (!$detailBoxHitsColumns)
			{
				$detailBoxHitsAll = false;
			}
			if (!$detailBoxCountColumns)
			{
				$detailBoxCountAll = false;
			}
			if (!$detailBoxReadmoreColumns)
			{
				$detailBoxReadmoreAll = false;
			}
		}

		// Hover box
		$hoverBox = $masonry_params['mas_hb'];
		$hoverBoxBg = $masonry_params['mas_hb_bg'];
		$hoverBoxBgOpacity = $masonry_params['mas_hb_bg_opacity'];
		$hoverBoxTextColor = $masonry_params['mas_hb_text_color'];
		$hoverBoxEffect = $masonry_params['mas_hb_effect'];
		$hoverBoxEffectSpeed = $masonry_params['mas_hb_effect_speed'];
		$hoverBoxEffectEasing = $masonry_params['mas_hb_effect_easing'];
		$hoverBoxTitle = $masonry_params['mas_hb_title'];
		$hoverBoxTitleLimit = $masonry_params['mas_hb_title_limit'];
		$hoverBoxIntrotext = $masonry_params['mas_hb_introtext'];
		$hoverBoxIntrotextLimit = $masonry_params['mas_hb_introtext_limit'];
		$hoverBoxStripTags = $masonry_params['mas_hb_strip_tags'];
		$hoverBoxDate = $masonry_params['mas_hb_date'];
		$hoverBoxDateFormat = $masonry_params['mas_hb_date_format'];
		$hoverBoxCategory = $masonry_params['mas_hb_category'];
		$hoverBoxLocation = $masonry_params['mas_hb_location'];
		$hoverBoxType = $masonry_params['mas_hb_type'];
		$hoverBoxAuthor = $masonry_params['mas_hb_author'];
		$hoverBoxPrice = $masonry_params['mas_hb_price'];
		$hoverBoxHits = $masonry_params['mas_hb_hits'];
		$hoverBoxLinkButton = $masonry_params['mas_hb_link'];
		$hoverBoxLightboxButton = false;
		if (isset($masonry_params['mas_hb_lightbox']))
		{
			$hoverBoxLightboxButton = $masonry_params['mas_hb_lightbox'];
		}

		// Hover effects
		$hoverEffectClass = '';
		if ($hoverBoxEffect == '4')
		{
			$hoverEffectClass = 'slideInRight';
		}
		if ($hoverBoxEffect == '5')
		{
			$hoverEffectClass = 'slideInLeft';
		}
		if ($hoverBoxEffect == '6')
		{
			$hoverEffectClass = 'slideInTop';
		}
		if ($hoverBoxEffect == '7')
		{
			$hoverEffectClass = 'slideInBottom';
		}
		if ($hoverBoxEffect == '8')
		{
			$hoverEffectClass = 'mnwzoomIn';
		}

		// Transition styles
		$animated = '';
		if ($hoverBoxEffect != 'no' && $hoverBoxEffect != '2' && $hoverBoxEffect != '3')
		{
			$animated = '
			transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-webkit-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-o-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-ms-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			';
		}
		$animated_flip = '';
		if ($hoverBoxEffect == '2' || $hoverBoxEffect == '3')
		{
			$animated_flip = '
			transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-webkit-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-o-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			-ms-transition: all '.$hoverBoxEffectSpeed.'s '.$hoverBoxEffectEasing.' 0s;
			';
		}

		// Hover box background
		$hb_bg_class = $this->utilities->hex2RGB($hoverBoxBg, true);
		$hb_bg_opacity_class = number_format((float)$hoverBoxBgOpacity, 2, '.', '');

		// Hover box text color
		if ($hoverBoxTextColor == '1') {
			$hoverTextColor = 'dark-text';
		} else {
			$hoverTextColor = 'light-text';
		}

		if ($page === '1')
		{
			// Reset button
			$resetButton = $masonry_params['mas_reset_filters'];
			$this->assignRef('resetButton', $resetButton);
		}

		// Get wall
		if ($page === '1')
		{
			$wall = $this->model->getAllResults($widgetID);
		}
		else
		{
			$wall = $this->model->getAllResultsAjax($widgetID);
		}

		// Create display params
		$detailBoxParams = array();
		$detailBoxParams['images'] = $mas_images;
		$detailBoxParams['crop_images'] = $mas_crop_images;
		$detailBoxParams['image_width'] = $mas_image_width;
		$detailBoxParams['image_height'] = $mas_image_height;
		$detailBoxParams['fallback_image'] = '';
		if (array_key_exists('mas_fallback_image', $masonry_params))
		{
			$detailBoxParams['fallback_image'] = $masonry_params['mas_fallback_image'];
		}
		$detailBoxParams['detailBoxTitleLimit'] = $detailBoxTitleLimit;
		$detailBoxParams['detailBoxIntrotextLimit'] = $detailBoxIntrotextLimit;
		$detailBoxParams['detailBoxStripTags'] = $detailBoxStripTags;
		$detailBoxParams['detailBoxDateFormat'] = $detailBoxDateFormat;

		$hoverBoxParams = array();
		$hoverBoxParams['hoverBox'] = $hoverBox;
		$hoverBoxParams['hoverBoxTitle'] = $hoverBoxTitle;
		$hoverBoxParams['hoverBoxTitleLimit'] = $hoverBoxTitleLimit;
		$hoverBoxParams['hoverBoxIntrotext'] = $hoverBoxIntrotext;
		$hoverBoxParams['hoverBoxIntrotextLimit'] = $hoverBoxIntrotextLimit;
		$hoverBoxParams['hoverBoxStripTags'] = $hoverBoxStripTags;
		$hoverBoxParams['hoverBoxDate'] = $hoverBoxDate;
		$hoverBoxParams['hoverBoxDateFormat'] = $hoverBoxDateFormat;

		// Get widget with display options
		$options = $this->model->options;
		$wall = $options->getWidgetDisplayOptions($widgetID, $wall, $detailBoxParams, $hoverBoxParams);

		// Assign a grid item number to each item
		if (isset($wall))
		{
			if ($gridType === '999c') // custom grid
			{
				$this->custom_grid_id = isset($masonry_params['mas_custom_grid']) ? $masonry_params['mas_custom_grid'] : false;
				$custom_grid = $utilities->getCustomGrid($this->custom_grid_id);
				$grid_elements = json_decode($custom_grid->elements, false);

				// Init custom grid widths
				if ($page === '1' && !empty($grid_elements))
				{
					$grid_columns = $custom_grid->columns;
					$responsive_masonry->initCustomGridWidths($masonry_params, $grid_elements, $grid_columns, $widgetID);
				}

				// Get number of items in custom grid
				$grid = count($grid_elements);

				if (!$grid)
				{
					echo '<div class="alert alert-danger"><b>Error:</b> '
					.JText::_('COM_MINITEKWALL_GRID_NOT_FOUND')
					.'</div>';
					return false;
				}
			}
			else
			{
				$this->custom_grid_id = false;
				$grid = (int) $gridType;
			}

			foreach ($wall as $key=>$item)
			{
				// Item sizes
				if ($page === '1')
				{
					$index = $key + 1;
				}
				else
				{
					$index = $startLimit + (($page - 2) * $pageLimit) + ($key + 1);
				}

				$item->rawIndex = $index;

				if ($index > $grid)
				{
					$item->itemIndex = $utilities->recurseMasItemIndex($index, $grid);
				}
				else
				{
					$item->itemIndex = $index;
				}
			}
		}

		// Display wall
		if ($page === '1')
		{
			if (!array_key_exists('mas_empty_message', $masonry_params))
			{
				$masonry_params['mas_empty_message'] = true;
			}
		}
		if ((!$wall || $wall == '' || $wall == 0))
		{
			if ($page === '1')
			{
				if ($masonry_params['mas_empty_message'])
				{
					$output = '<div class="mnw-results-empty-results">';
					$output .= '<span>'.JText::_('COM_MINITEKWALL_NO_ITEMS').'</span>';
					$output .= '</div>';
					echo $output;
				}
			}
		}
		else
		{
			// Get source
			$source_id = $this->utilities->getSourceID($widgetID);
			$data_source = $this->utilities->getSource($widgetID, $source_id);
			$this->assignRef('data_source', $data_source);

			if ($page === '1')
			{
				// Get items ordering
				$this->active_ordering = $this->utilities->getItemsOrdering($data_source);
				$this->active_direction = $this->utilities->getItemsDirection($data_source);

				// Get filters
				if ($masonry_params['mas_category_filters'] ||
					$masonry_params['mas_tag_filters'] ||
					$masonry_params['mas_location_filters'] ||
					$masonry_params['mas_date_filters'])
				{
					$filters = true;
				} else {
					$filters = NULL;
				}
				$this->assignRef('filters', $filters);

				// Get Sortings
				if ($masonry_params['mas_title_sorting'] ||
					$masonry_params['mas_category_sorting'] ||
					$masonry_params['mas_author_sorting'] ||
					$masonry_params['mas_date_sorting'] ||
					$masonry_params['mas_hits_sorting'])
				{
					$sortings = true;
				} else {
					$sortings = NULL;
				}
				$this->assignRef('sortings', $sortings);

				if (isset($filters) || isset($sortings))
				{
					$masonry_filters = $this->model->masonry_filters;
					$masonry_filters->getFiltersCss($masonry_params, $widgetID);
				}
			}

			// Assign variables
			if ($page === '1')
			{
				$this->assignRef('suffix', $suffix);
				$this->assignRef('mnwall_grid', $mnwall_grid);

				// Pagination
				$this->assignRef('pagination', $pagination);
				$this->assignRef('totalCount', $totalCount);
				$this->assignRef('remainingCount', $remainingCount);
				$this->assignRef('showRemaining', $showRemaining);
				$this->assignRef('totalPages', $totalPages);
				$this->assignRef('arrows', $arrows);
			}

			$this->assignRef('widgetID', $widgetID);
			$this->assignRef('wall', $wall);

			// Grid
			$this->assignRef('gridType', $gridType);
			$this->assignRef('cols', $cols);
			$this->assignRef('gutter', $gutter);
			$this->assignRef('mas_border_radius', $mas_border_radius);
			$this->assignRef('mas_border', $mas_border);
			$this->assignRef('mas_border_color', $mas_border_color);
			$this->assignRef('mas_images', $mas_images);
			$this->assignRef('full_width_image', $full_width_image);
			$this->assignRef('mnwall_layout', $mnwall_layout);
			$this->assignRef('animated', $animated);
			$this->assignRef('animated_flip', $animated_flip);
			$this->assignRef('hb_bg_class', $hb_bg_class);
			$this->assignRef('hb_bg_opacity_class', $hb_bg_opacity_class);
			$this->assignRef('hoverTextColor', $hoverTextColor);

			// Detail box
			// Big
			$this->assignRef('detailBoxBig', $detailBoxBig);
			$this->assignRef('detailBoxPositionBig', $detailBoxPositionBig);
			$this->assignRef('detailBoxBackgroundBig', $detailBoxBackgroundBig);
			$this->assignRef('detailBoxBackgroundOpacityBig', $detailBoxBackgroundOpacityBig);
			$this->assignRef('detailBoxTextColorBig', $detailBoxTextColorBig);
			$this->assignRef('detailBoxTitleBig', $detailBoxTitleBig);
			$this->assignRef('detailBoxIntrotextBig', $detailBoxIntrotextBig);
			$this->assignRef('detailBoxDateBig', $detailBoxDateBig);
			$this->assignRef('detailBoxCategoryBig', $detailBoxCategoryBig);
			$this->assignRef('detailBoxLocationBig', $detailBoxLocationBig);
			$this->assignRef('detailBoxTypeBig', $detailBoxTypeBig);
			$this->assignRef('detailBoxAuthorBig', $detailBoxAuthorBig);
			$this->assignRef('detailBoxTagsBig', $detailBoxTagsBig);
			$this->assignRef('detailBoxPriceBig', $detailBoxPriceBig);
			$this->assignRef('detailBoxHitsBig', $detailBoxHitsBig);
			$this->assignRef('detailBoxCountBig', $detailBoxCountBig);
			$this->assignRef('detailBoxReadmoreBig', $detailBoxReadmoreBig);

			// Landscape
			$this->assignRef('detailBoxLscape', $detailBoxLscape);
			$this->assignRef('detailBoxPositionLscape', $detailBoxPositionLscape);
			$this->assignRef('detailBoxBackgroundLscape', $detailBoxBackgroundLscape);
			$this->assignRef('detailBoxBackgroundOpacityLscape', $detailBoxBackgroundOpacityLscape);
			$this->assignRef('detailBoxTextColorLscape', $detailBoxTextColorLscape);
			$this->assignRef('detailBoxTitleLscape', $detailBoxTitleLscape);
			$this->assignRef('detailBoxIntrotextLscape', $detailBoxIntrotextLscape);
			$this->assignRef('detailBoxDateLscape', $detailBoxDateLscape);
			$this->assignRef('detailBoxCategoryLscape', $detailBoxCategoryLscape);
			$this->assignRef('detailBoxLocationLscape', $detailBoxLocationLscape);
			$this->assignRef('detailBoxTypeLscape', $detailBoxTypeLscape);
			$this->assignRef('detailBoxAuthorLscape', $detailBoxAuthorLscape);
			$this->assignRef('detailBoxTagsLscape', $detailBoxTagsLscape);
			$this->assignRef('detailBoxPriceLscape', $detailBoxPriceLscape);
			$this->assignRef('detailBoxHitsLscape', $detailBoxHitsLscape);
			$this->assignRef('detailBoxCountLscape', $detailBoxCountLscape);
			$this->assignRef('detailBoxReadmoreLscape', $detailBoxReadmoreLscape);

			// Portrait
			$this->assignRef('detailBoxPortrait', $detailBoxPortrait);
			$this->assignRef('detailBoxPositionPortrait', $detailBoxPositionPortrait);
			$this->assignRef('detailBoxBackgroundPortrait', $detailBoxBackgroundPortrait);
			$this->assignRef('detailBoxBackgroundOpacityPortrait', $detailBoxBackgroundOpacityPortrait);
			$this->assignRef('detailBoxTextColorPortrait', $detailBoxTextColorPortrait);
			$this->assignRef('detailBoxTitlePortrait', $detailBoxTitlePortrait);
			$this->assignRef('detailBoxIntrotextPortrait', $detailBoxIntrotextPortrait);
			$this->assignRef('detailBoxDatePortrait', $detailBoxDatePortrait);
			$this->assignRef('detailBoxCategoryPortrait', $detailBoxCategoryPortrait);
			$this->assignRef('detailBoxLocationPortrait', $detailBoxLocationPortrait);
			$this->assignRef('detailBoxTypePortrait', $detailBoxTypePortrait);
			$this->assignRef('detailBoxAuthorPortrait', $detailBoxAuthorPortrait);
			$this->assignRef('detailBoxTagsPortrait', $detailBoxTagsPortrait);
			$this->assignRef('detailBoxPricePortrait', $detailBoxPricePortrait);
			$this->assignRef('detailBoxHitsPortrait', $detailBoxHitsPortrait);
			$this->assignRef('detailBoxCountPortrait', $detailBoxCountPortrait);
			$this->assignRef('detailBoxReadmorePortrait', $detailBoxReadmorePortrait);

			// Small
			$this->assignRef('detailBoxSmall', $detailBoxSmall);
			$this->assignRef('detailBoxPositionSmall', $detailBoxPositionSmall);
			$this->assignRef('detailBoxBackgroundSmall', $detailBoxBackgroundSmall);
			$this->assignRef('detailBoxBackgroundOpacitySmall', $detailBoxBackgroundOpacitySmall);
			$this->assignRef('detailBoxTextColorSmall', $detailBoxTextColorSmall);
			$this->assignRef('detailBoxTitleSmall', $detailBoxTitleSmall);
			$this->assignRef('detailBoxIntrotextSmall', $detailBoxIntrotextSmall);
			$this->assignRef('detailBoxDateSmall', $detailBoxDateSmall);
			$this->assignRef('detailBoxCategorySmall', $detailBoxCategorySmall);
			$this->assignRef('detailBoxLocationSmall', $detailBoxLocationSmall);
			$this->assignRef('detailBoxTypeSmall', $detailBoxTypeSmall);
			$this->assignRef('detailBoxAuthorSmall', $detailBoxAuthorSmall);
			$this->assignRef('detailBoxTagsSmall', $detailBoxTagsSmall);
			$this->assignRef('detailBoxPriceSmall', $detailBoxPriceSmall);
			$this->assignRef('detailBoxHitsSmall', $detailBoxHitsSmall);
			$this->assignRef('detailBoxCountSmall', $detailBoxCountSmall);
			$this->assignRef('detailBoxReadmoreSmall', $detailBoxReadmoreSmall);

			// Columns
			$this->assignRef('detailBoxColumns', $detailBoxColumns);
			$this->assignRef('detailBoxPositionColumns', $detailBoxPositionColumns);
			$this->assignRef('detailBoxBackgroundColumns', $detailBoxBackgroundColumns);
			$this->assignRef('detailBoxBackgroundOpacityColumns', $detailBoxBackgroundOpacityColumns);
			$this->assignRef('detailBoxTextColorColumns', $detailBoxTextColorColumns);
			$this->assignRef('detailBoxTitleColumns', $detailBoxTitleColumns);
			$this->assignRef('detailBoxIntrotextColumns', $detailBoxIntrotextColumns);
			$this->assignRef('detailBoxDateColumns', $detailBoxDateColumns);
			$this->assignRef('detailBoxCategoryColumns', $detailBoxCategoryColumns);
			$this->assignRef('detailBoxLocationColumns', $detailBoxLocationColumns);
			$this->assignRef('detailBoxTypeColumns', $detailBoxTypeColumns);
			$this->assignRef('detailBoxAuthorColumns', $detailBoxAuthorColumns);
			$this->assignRef('detailBoxTagsColumns', $detailBoxTagsColumns);
			$this->assignRef('detailBoxPriceColumns', $detailBoxPriceColumns);
			$this->assignRef('detailBoxHitsColumns', $detailBoxHitsColumns);
			$this->assignRef('detailBoxCountColumns', $detailBoxCountColumns);
			$this->assignRef('detailBoxReadmoreColumns', $detailBoxReadmoreColumns);

			// Overall
			$this->assignRef('detailBoxAll', $detailBoxAll);
			$this->assignRef('detailBoxTitleAll', $detailBoxTitleAll);
			$this->assignRef('detailBoxIntrotextAll', $detailBoxIntrotextAll);
			$this->assignRef('detailBoxDateAll', $detailBoxDateAll);
			$this->assignRef('detailBoxCategoryAll', $detailBoxCategoryAll);
			$this->assignRef('detailBoxLocationAll', $detailBoxLocationAll);
			$this->assignRef('detailBoxTypeAll', $detailBoxTypeAll);
			$this->assignRef('detailBoxAuthorAll', $detailBoxAuthorAll);
			$this->assignRef('detailBoxTagsAll', $detailBoxTagsAll);
			$this->assignRef('detailBoxPriceAll', $detailBoxPriceAll);
			$this->assignRef('detailBoxHitsAll', $detailBoxHitsAll);
			$this->assignRef('detailBoxCountAll', $detailBoxCountAll);
			$this->assignRef('detailBoxReadmoreAll', $detailBoxReadmoreAll);

			// Hover box
			$this->assignRef('hoverBox', $hoverBox);
			$this->assignRef('hoverBoxBg', $hoverBoxBg);
			$this->assignRef('hoverBoxBgOpacity', $hoverBoxBgOpacity);
			$this->assignRef('hoverBoxTextColor', $hoverBoxTextColor);
			$this->assignRef('hoverBoxEffect', $hoverBoxEffect);
			$this->assignRef('hoverEffectClass', $hoverEffectClass);
			$this->assignRef('hoverBoxEffectSpeed', $hoverBoxEffectSpeed);
			$this->assignRef('hoverBoxTitle', $hoverBoxTitle);
			$this->assignRef('hoverBoxIntrotext', $hoverBoxIntrotext);
			$this->assignRef('hoverBoxDate', $hoverBoxDate);
			$this->assignRef('hoverBoxCategory', $hoverBoxCategory);
			$this->assignRef('hoverBoxLocation', $hoverBoxLocation);
			$this->assignRef('hoverBoxType', $hoverBoxType);
			$this->assignRef('hoverBoxAuthor', $hoverBoxAuthor);
			$this->assignRef('hoverBoxPrice', $hoverBoxPrice);
			$this->assignRef('hoverBoxHits', $hoverBoxHits);
			$this->assignRef('hoverBoxLinkButton', $hoverBoxLinkButton);
			$this->assignRef('hoverBoxLightboxButton', $hoverBoxLightboxButton);

			if ($page === '1')
			{
				// Because the application sets a default page title,
				// we need to get it from the menu item itself
				$app = JFactory::getApplication();
				$menus = $app->getMenu();
				$menu = $menus->getActive();

				$this->mas_page_title = false;
				if (array_key_exists('mas_page_title', $masonry_params) && $masonry_params['mas_page_title'])
				{
					$this->mas_page_title = true;

					if ($menu)
					{
						$params->def('page_heading', $params->get('page_title', $menu->title));
					}

					$title = $params->get('page_title', '');

					// Check for empty title and add site name if param is set
					if (empty($title))
					{
						$title = $app->get('sitename');
					}
					elseif ($app->get('sitename_pagetitles', 0) == 1)
					{
						$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
					}
					elseif ($app->get('sitename_pagetitles', 0) == 2)
					{
						$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
					}

					$document->setTitle($title);

					if ($params->get('menu-meta_description'))
					{
						$document->setDescription($params->get('menu-meta_description'));
					}

					if ($params->get('menu-meta_keywords'))
					{
						$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
					}

					if ($params->get('robots'))
					{
						$document->setMetadata('robots', $params->get('robots'));
					}
				}

				if (isset($menu->query['option']) && $menu->query['option'] == 'com_minitekwall')
				{
					$title = $params->get('page_title', '');

					// Check for empty title and add site name if param is set
					if (empty($title))
					{
						$title = $app->get('sitename');
					}
					elseif ($app->get('sitename_pagetitles', 0) == 1)
					{
						$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
					}
					elseif ($app->get('sitename_pagetitles', 0) == 2)
					{
						$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
					}

					$document->setTitle($title);
				}
			}

			// Check for errors.
			if (count($errors = $this->get('Errors')))
			{
				JError::raiseError(500, implode('<br />', $errors));
				return false;
			}

			// Get Layout
			if ($page === '1')
			{
				$layout = $masonry_params['mas_layout'];
				if ($layout)
				{
					$this->setLayout($layout);
					$viewName = $jinput->get('view', 'masonry', 'WORD');
					$layoutTemplate = $this->getLayoutTemplate(); // This is empty for modules if the override has the name 'default'.
					$this->addTemplatePath(JPATH_SITE.'/templates/'.$layoutTemplate.'/html/com_minitekwall/'.$viewName);
				}
			}

      parent::display($tpl);
		}
	}

	public function getColumnsItemOptions()
	{
		$utilities = $this->model->utilities;

		$options = array(
			"db_class" => "",
			"title_class" => "",
			"introtext_class" => "",
			"date_class" => "",
			"category_class" => "",
			"location_class" => "",
			"type_class" => "",
			"author_class" => "",
			"tags_class" => "",
			"price_class" => "",
			"hits_class" => "",
			"count_class" => "",
			"readmore_class" => "",
			"db_bg_class" => "",
			"db_bg_opacity_class" => "",
			"db_color_class" => "",
			"position_class" => ""
		);

		$options['db_bg_class'] = $utilities->hex2RGB($this->detailBoxBackgroundColumns, true);
		$options['db_bg_opacity_class'] = number_format((float)$this->detailBoxBackgroundOpacityColumns, 2, '.', '');
		$options['db_color_class'] = $this->detailBoxTextColorColumns;
		$options['position_class'] = 'content-'.$this->detailBoxPositionColumns;

		if (!$this->detailBoxColumns)
		{
			$options['db_class'] = 'db-hidden';
		}

		if (!$this->detailBoxTitleColumns)
		{
			$options['title_class'] = 'title-hidden';
		}

		if (!$this->detailBoxIntrotextColumns)
		{
			$options['introtext_class'] = 'introtext-hidden';
		}

		if (!$this->detailBoxDateColumns)
		{
			$options['date_class'] = 'date-hidden';
		}

		if (!$this->detailBoxCategoryColumns)
		{
			$options['category_class'] = 'category-hidden';
		}

		if (!$this->detailBoxLocationColumns)
		{
			$options['location_class'] = 'location-hidden';
		}

		if (!$this->detailBoxTypeColumns)
		{
			$options['type_class'] = 'type-hidden';
		}

		if (!$this->detailBoxAuthorColumns)
		{
			$options['author_class'] = 'author-hidden';
		}

		if (!$this->detailBoxTagsColumns)
		{
			$options['tags_class'] = 'tags-hidden';
		}

		if (!$this->detailBoxPriceColumns)
		{
			$options['price_class'] = 'price-hidden';
		}

		if (!$this->detailBoxHitsColumns)
		{
			$options['hits_class'] = 'hits-hidden';
		}

		if (!$this->detailBoxCountColumns)
		{
			$options['count_class'] = 'count-hidden';
		}

		if (!$this->detailBoxReadmoreColumns)
		{
			$options['readmore_class'] = 'readmore-hidden';
		}

		return $options;
	}

	public function getMasonryItemOptions($item_size)
	{
		$utilities = $this->model->utilities;

		$options = array(
			"detail_box" => "",
			"db_class" => "",
			"title_class" => "",
			"introtext_class" => "",
			"date_class" => "",
			"category_class" => "",
			"location_class" => "",
			"type_class" => "",
			"author_class" => "",
			"tags_class" => "",
			"price_class" => "",
			"hits_class" => "",
			"count_class" => "",
			"readmore_class" => "",
			"db_bg_class" => "",
			"db_bg_opacity_class" => "",
			"db_color_class" => "",
			"position_class" => ""
		);

		switch ($item_size)
		{
			case 'mnwall-big':
				$options['detail_box'] = $this->detailBoxBig;
				$options['db_bg_class'] = $this->utilities->hex2RGB($this->detailBoxBackgroundBig, true);
				$options['db_bg_opacity_class'] = number_format((float)$this->detailBoxBackgroundOpacityBig, 2, '.', '');
				$options['db_color_class'] = $this->detailBoxTextColorBig;
				$options['position_class'] = 'content-'.$this->detailBoxPositionBig;

				if (!$this->detailBoxBig)
				{
					$options['db_class'] = 'db-hidden';
				}

				if (!$this->detailBoxTitleBig)
				{
					$options['title_class'] = 'title-hidden';
				}

				if (!$this->detailBoxIntrotextBig)
				{
					$options['introtext_class'] = 'introtext-hidden';
				}

				if (!$this->detailBoxDateBig)
				{
					$options['date_class'] = 'date-hidden';
				}

				if (!$this->detailBoxCategoryBig)
				{
					$options['category_class'] = 'category-hidden';
				}

				if (!$this->detailBoxLocationBig)
				{
					$options['location_class'] = 'location-hidden';
				}

				if (!$this->detailBoxTypeBig)
				{
					$options['type_class'] = 'type-hidden';
				}

				if (!$this->detailBoxAuthorBig)
				{
					$options['author_class'] = 'author-hidden';
				}

				if (!$this->detailBoxTagsBig)
				{
					$options['tags_class'] = 'tags-hidden';
				}

				if (!$this->detailBoxPriceBig)
				{
					$options['price_class'] = 'price-hidden';
				}

				if (!$this->detailBoxHitsBig)
				{
					$options['hits_class'] = 'hits-hidden';
				}

				if (!$this->detailBoxCountBig)
				{
					$options['count_class'] = 'count-hidden';
				}

				if (!$this->detailBoxReadmoreBig)
				{
					$options['readmore_class'] = 'readmore-hidden';
				}
				break;

			case 'mnwall-horizontal':
				$options['detail_box'] = $this->detailBoxLscape;
				$options['db_bg_class'] = $this->utilities->hex2RGB($this->detailBoxBackgroundLscape, true);
				$options['db_bg_opacity_class'] = number_format((float)$this->detailBoxBackgroundOpacityLscape, 2, '.', '');
				$options['db_color_class'] = $this->detailBoxTextColorLscape;
				$options['position_class'] = 'content-'.$this->detailBoxPositionLscape;

				if (!$this->detailBoxLscape)
				{
					$options['db_class'] = 'db-hidden';
				}

				if (!$this->detailBoxTitleLscape)
				{
					$options['title_class'] = 'title-hidden';
				}

				if (!$this->detailBoxIntrotextLscape)
				{
					$options['introtext_class'] = 'introtext-hidden';
				}

				if (!$this->detailBoxDateLscape)
				{
					$options['date_class'] = 'date-hidden';
				}

				if (!$this->detailBoxCategoryLscape)
				{
					$options['category_class'] = 'category-hidden';
				}

				if (!$this->detailBoxLocationLscape)
				{
					$options['location_class'] = 'location-hidden';
				}

				if (!$this->detailBoxTypeLscape)
				{
					$options['type_class'] = 'type-hidden';
				}

				if (!$this->detailBoxAuthorLscape)
				{
					$options['author_class'] = 'author-hidden';
				}

				if (!$this->detailBoxTagsLscape)
				{
					$options['tags_class'] = 'tags-hidden';
				}

				if (!$this->detailBoxPriceLscape)
				{
					$options['price_class'] = 'price-hidden';
				}

				if (!$this->detailBoxHitsLscape)
				{
					$options['hits_class'] = 'hits-hidden';
				}

				if (!$this->detailBoxCountLscape)
				{
					$options['count_class'] = 'count-hidden';
				}

				if (!$this->detailBoxReadmoreLscape)
				{
					$options['readmore_class'] = 'readmore-hidden';
				}
				break;

			case 'mnwall-vertical':
				$options['detail_box'] = $this->detailBoxPortrait;
				$options['db_bg_class'] = $this->utilities->hex2RGB($this->detailBoxBackgroundPortrait, true);
				$options['db_bg_opacity_class'] = number_format((float)$this->detailBoxBackgroundOpacityPortrait, 2, '.', '');
				$options['db_color_class'] = $this->detailBoxTextColorPortrait;
				$options['position_class'] = 'content-'.$this->detailBoxPositionPortrait;

				if (!$this->detailBoxPortrait)
				{
					$options['db_class'] = 'db-hidden';
				}

				if (!$this->detailBoxTitlePortrait)
				{
					$options['title_class'] = 'title-hidden';
				}

				if (!$this->detailBoxIntrotextPortrait)
				{
					$options['introtext_class'] = 'introtext-hidden';
				}

				if (!$this->detailBoxDatePortrait)
				{
					$options['date_class'] = 'date-hidden';
				}

				if (!$this->detailBoxCategoryPortrait)
				{
					$options['category_class'] = 'category-hidden';
				}

				if (!$this->detailBoxLocationPortrait)
				{
					$options['location_class'] = 'location-hidden';
				}

				if (!$this->detailBoxTypePortrait)
				{
					$options['type_class'] = 'type-hidden';
				}

				if (!$this->detailBoxAuthorPortrait)
				{
					$options['author_class'] = 'author-hidden';
				}

				if (!$this->detailBoxTagsPortrait)
				{
					$options['tags_class'] = 'tags-hidden';
				}

				if (!$this->detailBoxPricePortrait)
				{
					$options['price_class'] = 'price-hidden';
				}

				if (!$this->detailBoxHitsPortrait)
				{
					$options['hits_class'] = 'hits-hidden';
				}

				if (!$this->detailBoxCountPortrait)
				{
					$options['count_class'] = 'count-hidden';
				}

				if (!$this->detailBoxReadmorePortrait)
				{
					$options['readmore_class'] = 'readmore-hidden';
				}
				break;

			case 'mnwall-small':
				$options['detail_box'] = $this->detailBoxSmall;
				$options['db_bg_class'] = $this->utilities->hex2RGB($this->detailBoxBackgroundSmall, true);
				$options['db_bg_opacity_class'] = number_format((float)$this->detailBoxBackgroundOpacitySmall, 2, '.', '');
				$options['db_color_class'] = $this->detailBoxTextColorSmall;
				$options['position_class'] = 'content-'.$this->detailBoxPositionSmall;

				if (!$this->detailBoxSmall)
				{
					$options['db_class'] = 'db-hidden';
				}

				if (!$this->detailBoxTitleSmall)
				{
					$options['title_class'] = 'title-hidden';
				}

				if (!$this->detailBoxIntrotextSmall)
				{
					$options['introtext_class'] = 'introtext-hidden';
				}

				if (!$this->detailBoxDateSmall)
				{
					$options['date_class'] = 'date-hidden';
				}

				if (!$this->detailBoxCategorySmall)
				{
					$options['category_class'] = 'category-hidden';
				}

				if (!$this->detailBoxLocationSmall)
				{
					$options['location_class'] = 'location-hidden';
				}

				if (!$this->detailBoxTypeSmall)
				{
					$options['type_class'] = 'type-hidden';
				}

				if (!$this->detailBoxAuthorSmall)
				{
					$options['author_class'] = 'author-hidden';
				}

				if (!$this->detailBoxTagsSmall)
				{
					$options['tags_class'] = 'tags-hidden';
				}

				if (!$this->detailBoxPriceSmall)
				{
					$options['price_class'] = 'price-hidden';
				}

				if (!$this->detailBoxHitsSmall)
				{
					$options['hits_class'] = 'hits-hidden';
				}

				if (!$this->detailBoxCountSmall)
				{
					$options['count_class'] = 'count-hidden';
				}

				if (!$this->detailBoxReadmoreSmall)
				{
					$options['readmore_class'] = 'readmore-hidden';
				}
				break;
		}

		return $options;
	}
}
