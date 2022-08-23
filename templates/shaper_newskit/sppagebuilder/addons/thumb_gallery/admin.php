<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

SpAddonsConfig::addonConfig(
    array( 
        'type'=>'content', 
        'addon_name'=>'sp_thumb_gallery',
        'category'=>'Newskit',
        'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_THUMB_GALLERY'),
        'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_THUMB_GALLERY_DESC'),
        'attr'=>array(
            'general' => array(

                'admin_label'=>array(
                    'type'=>'text',
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
                    'std'=> ''
                ),
                'title'=>array(
                    'type'=>'text', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
                    'std'=>  ''
                    ),

                'heading_selector'=>array(
                    'type'=>'select', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
                    'values'=>array(
                        'h1'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
                        'h2'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
                        'h3'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
                        'h4'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
                        'h5'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
                        'h6'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
                        ),
                    'std'=>'h3',
                    'depends'=>array(array('title', '!=', ''))
                ),

                'title_text_color'=>array(
                    'type'=>'color',
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
                    'depends'=>array(array('title', '!=', ''))
                    ),

                'category'=>array(
                    'type'=>'category',
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_LATEST_POSTS_SELECT_CATEGORY'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_LATEST_POSTS_SELECT_CATEGORY_DESC'),
                    'std'=>''
                    ),

                'order_by'=>array(
                    'type'=>'select', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_LP_ORDER_BY'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_LP_ORDER_BY_DESC'),
                    'values'=>array(
                        'latest'=>JText::_('COM_SPPAGEBUILDER_ADDON_LP_ORDER_BY_LATEST'),
                        'hits'=>JText::_('COM_SPPAGEBUILDER_ADDON_LP_ORDER_BY_HITS'),
                        'featured'=>JText::_('COM_SPPAGEBUILDER_ADDON_LP_ORDER_BY_FEATURED'),
                        ),
                    'std'=>'latest',
                ),      

                'item_limit'=>array(
                    'type'=>'number', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_LATEST_POSTS_LIMIT'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_LATEST_POSTS_LIMIT_DESC'),
                    'std'=>'8'
                ),

                'autoplay'=>array(
                    'type'=>'select', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY_DESC'),
                    'values'=>array(
                        1=>JText::_('JYES'),
                        0=>JText::_('JNO'),
                        ),
                    'std'=>1,
                    ),
                
                'arrows'=>array(
                    'type'=>'select', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS_DESC'),
                    'values'=>array(
                        1=>JText::_('JYES'),
                        0=>JText::_('JNO'),
                        ),
                    'std'=>1,
                    ),

                'class'=>array(
                    'type'=>'text', 
                    'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
                    'std'=> ''
                    ),
            )

        )
    )
);

