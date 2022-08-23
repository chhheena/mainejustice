<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class MinitekWallViewScroller extends JViewLegacy
{
	function display($tpl = null)
	{
 		$document = JFactory::getDocument();
		$this->model = $this->getModel();
		$utilities = $this->model->utilities;
		$this->assignRef('utilities', $utilities);
		$params = $utilities->getParams('com_minitekwall');
		$this->assignRef('params', $params);
		$jinput = JFactory::getApplication()->input;
		$widgetID = $jinput->get('widget_id');

		// Get scroller parameters
		$scroller_params = $utilities->getScrollerParams($widgetID);

		// Get Scroller type
		$type = $scroller_params['scr_type'];
		$scroller_layout = $type;
		$suffix = $scroller_params['scr_suffix'];
		$contain_type = $scroller_params['scr_contain_type'];
		$cont_bg = $scroller_params['scr_cont_bg'];
		$cont_image = $scroller_params['scr_cont_image'];
		$cont_padding = (int)$scroller_params['scr_cont_padding'];
		$gutter = (int)$scroller_params['scr_gutter'];
		$border_radius = (int)$scroller_params['scr_border_radius'];
		$border = (int)$scroller_params['scr_border'];
		$border_color = $scroller_params['scr_border_color'];

		// Images
		$scr_images = $scroller_params['scr_images'];
		$this->scr_image_link = true;
		if (array_key_exists('scr_image_link', $scroller_params))
		{
			$this->scr_image_link = $scroller_params['scr_image_link'];
		}
		$scr_crop_images = $scroller_params['scr_crop_images'];
		$scr_image_width = $scroller_params['scr_image_width'];
		$scr_image_height = $scroller_params['scr_image_height'];
		$scr_fallback_image = $scroller_params['scr_fallback_image'];

		// Get navigation
		$arrows = $scroller_params['scr_arrows'];
		$bullets = $scroller_params['scr_bullets'];

		// Get Total count
		$globalLimit = $scroller_params['scr_global_limit'];
		$totalCount = $this->model->getAllResultsCount($widgetID);

		// Load scroller css
		$document->addStyleSheet(JURI::base().'components/com_minitekwall/assets/css/scroller.css?v=3.8.0');

		$responsive_scroller = $this->model->responsive_scroller;
		$responsive_scroller->loadResponsiveScroller($scroller_params, $widgetID);

		// Add scripts
		if ($contain_type == 'not_contained')
		{
			$document->addCustomTag('<script src="'.JURI::base().'components/com_minitekwall/assets/js/mnwall-scroller.js" type="text/javascript"></script>');
		}
		else if ($contain_type == 'contained')
		{
			$document->addCustomTag('<script src="'.JURI::base().'components/com_minitekwall/assets/js/mnwall-scroller-cont.js" type="text/javascript"></script>');
		}

		// Get lightbox
		$lightbox = false;
		if ($scroller_params['scr_hb'] && isset($scroller_params['scr_hb_lightbox']) && $scroller_params['scr_hb_lightbox'] && $params->get('load_lightbox', true))
		{
			$lightbox = true;
			$document->addStyleSheet(JURI::base().'components/com_minitekwall/assets/lightbox/lightbox.min.css');
			$document->addCustomTag('<script src="'.JURI::base().'components/com_minitekwall/assets/lightbox/lightbox.min.js" type="text/javascript"></script>');
		}

		// Add javascript.php
		$scroller_javascript = $this->model->scroller_javascript;
		$scroller_javascript->loadScrollerJavascript($scroller_params, $widgetID, $totalCount);

		// Detail box
		$detailBox = $scroller_params['scr_db'];
		$detailBoxBackground = $scroller_params['scr_db_bg'];
		$detailBoxBackground = $utilities->hex2RGB($detailBoxBackground, true);
		$detailBoxBackgroundOpacity = $scroller_params['scr_db_bg_opacity'];
		$detailBoxBackgroundOpacity = number_format((float)$detailBoxBackgroundOpacity, 2, '.', '');
		$detailBoxTextColor = $scroller_params['scr_db_color'];
		$detailBoxTitle = $scroller_params['scr_db_title'];
		$this->detailBoxTitleLink = true;
		if (array_key_exists('scr_db_title_link', $scroller_params))
		{
			$this->detailBoxTitleLink = $scroller_params['scr_db_title_link'];
		}
		$detailBoxTitleLimit = $scroller_params['scr_db_title_limit'];
		$detailBoxIntrotext = $scroller_params['scr_db_introtext'];
		$detailBoxIntrotextLimit = $scroller_params['scr_db_introtext_limit'];
		$detailBoxStripTags = $scroller_params['scr_db_strip_tags'];
		$detailBoxDate = $scroller_params['scr_db_date'];
		$detailBoxDateFormat = $scroller_params['scr_db_date_format'];
		$detailBoxCategory = $scroller_params['scr_db_category'];
		$detailBoxLocation = $scroller_params['scr_db_location'];
		$detailBoxType = $scroller_params['scr_db_content_type'];
		$detailBoxAuthor = $scroller_params['scr_db_author'];
		$detailBoxPrice = $scroller_params['scr_db_price'];
		$detailBoxHits = $scroller_params['scr_db_hits'];
		$detailBoxCount = $scroller_params['scr_db_count'];
		$detailBoxReadmore = $scroller_params['scr_db_readmore'];

		// Hover box
		$hoverBox = $scroller_params['scr_hb'];
		$hoverBoxBg = $scroller_params['scr_hb_bg'];
		$hoverBoxBgOpacity = $scroller_params['scr_hb_bg_opacity'];
		$hoverBoxTextColor = $scroller_params['scr_hb_text_color'];
		$hoverBoxEffect = $scroller_params['scr_hb_effect'];
		$hoverBoxEffectSpeed = $scroller_params['scr_hb_effect_speed'];
		$hoverBoxEffectEasing = $scroller_params['scr_hb_effect_easing'];
		$hoverBoxTitle = $scroller_params['scr_hb_title'];
		$hoverBoxTitleLimit = $scroller_params['scr_hb_title_limit'];
		$hoverBoxIntrotext = $scroller_params['scr_hb_introtext'];
		$hoverBoxIntrotextLimit = $scroller_params['scr_hb_introtext_limit'];
		$hoverBoxStripTags = $scroller_params['scr_hb_strip_tags'];
		$hoverBoxDate = $scroller_params['scr_hb_date'];
		$hoverBoxDateFormat = $scroller_params['scr_hb_date_format'];
		$hoverBoxCategory = $scroller_params['scr_hb_category'];
		$hoverBoxLocation = $scroller_params['scr_hb_location'];
		$hoverBoxType = $scroller_params['scr_hb_type'];
		$hoverBoxAuthor = $scroller_params['scr_hb_author'];
		$hoverBoxPrice = $scroller_params['scr_hb_price'];
		$hoverBoxHits = $scroller_params['scr_hb_hits'];
		$hoverBoxLinkButton = $scroller_params['scr_hb_link'];
		$hoverBoxLightboxButton = false;
		if (isset($scroller_params['scr_hb_lightbox']))
		{
			$hoverBoxLightboxButton = $scroller_params['scr_hb_lightbox'];
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

		// Get wall
		$wall = $this->model->getAllResults($widgetID);

		// Create display params
		$detailBoxParams = array();
		$detailBoxParams['images'] = $scr_images;
		$detailBoxParams['crop_images'] = $scr_crop_images;
		$detailBoxParams['image_width'] = $scr_image_width;
		$detailBoxParams['image_height'] = $scr_image_height;
		$detailBoxParams['fallback_image'] = $scr_fallback_image;
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

		// Display wall
		if (!$wall || $wall == '' || $wall == 0)
		{
			$output = '<div class="mnw-results-empty-results">';
			$output .= '<span>'.JText::_('COM_MINITEKWALL_NO_ITEMS').'</span>';
			$output .= '</div>';
			echo $output;
		}
		else
		{
			// Assign variables
			$this->assignRef('widgetID', $widgetID);
			$this->assignRef('wall', $wall);

			// Grid
			$this->assignRef('type', $type);
			$this->assignRef('scroller_layout', $scroller_layout);
			$this->assignRef('suffix', $suffix);
			$this->assignRef('cont_bg', $cont_bg);
			$this->assignRef('cont_image', $cont_image);
			$this->assignRef('cont_padding', $cont_padding);
			$this->assignRef('gutter', $gutter);
			$this->assignRef('border_radius', $border_radius);
			$this->assignRef('border', $border);
			$this->assignRef('border_color', $border_color);
			$this->assignRef('scr_images', $scr_images);
			$this->assignRef('scr_image_width', $scr_image_width);
			$this->assignRef('scr_image_height', $scr_image_height);
			$this->assignRef('animated', $animated);
			$this->assignRef('animated_flip', $animated_flip);
			$this->assignRef('hb_bg_class', $hb_bg_class);
			$this->assignRef('hb_bg_opacity_class', $hb_bg_opacity_class);
			$this->assignRef('hoverTextColor', $hoverTextColor);

			// Pagination
			$this->assignRef('arrows', $arrows);
			$this->assignRef('bullets', $bullets);
			$this->assignRef('totalCount', $totalCount);

			// Detail box
			$this->assignRef('detailBox', $detailBox);
			$this->assignRef('detailBoxBackground', $detailBoxBackground);
			$this->assignRef('detailBoxBackgroundOpacity', $detailBoxBackgroundOpacity);
			$this->assignRef('detailBoxTextColor', $detailBoxTextColor);
			$this->assignRef('detailBoxTitle', $detailBoxTitle);
			$this->assignRef('detailBoxIntrotext', $detailBoxIntrotext);
			$this->assignRef('detailBoxDate', $detailBoxDate);
			$this->assignRef('detailBoxCategory', $detailBoxCategory);
			$this->assignRef('detailBoxLocation', $detailBoxLocation);
			$this->assignRef('detailBoxType', $detailBoxType);
			$this->assignRef('detailBoxAuthor', $detailBoxAuthor);
			$this->assignRef('detailBoxPrice', $detailBoxPrice);
			$this->assignRef('detailBoxHits', $detailBoxHits);
			$this->assignRef('detailBoxCount', $detailBoxCount);
			$this->assignRef('detailBoxReadmore', $detailBoxReadmore);

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

			// Because the application sets a default page title,
			// we need to get it from the menu item itself
			$app = JFactory::getApplication();
			$menus = $app->getMenu();
			$menu = $menus->getActive();

			$this->scr_page_title = false;
			if (array_key_exists('scr_page_title', $scroller_params) && $scroller_params['scr_page_title'])
			{
				$this->scr_page_title = true;

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

			// Check for errors.
			if (count($errors = $this->get('Errors')))
			{
				JError::raiseError(500, implode('<br />', $errors));
				return false;
			}

			// Get Layout
			$layout = $scroller_params['scr_layout'];
			if ($layout)
 	    {
        $this->setLayout($layout);
				$viewName = $jinput->get('view', 'scroller', 'WORD');
				$layoutTemplate = $this->getLayoutTemplate(); // This is empty if the override has the name 'default'. Does not work for modules.
				$this->addTemplatePath(JPATH_SITE.'/templates/'.$layoutTemplate.'/html/com_minitekwall/'.$viewName);
      }

      parent::display($tpl);
		}
	}
}
