<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'content',
		'addon_name'=>'sp_stock_scroller',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_DESC'),
		'category'=>'Gazette',
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
					'depends'=>array(array('title', '!=', '')),
				),

				'title_font_family'=>array(
					'type'=>'fonts',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY_DESC'),
					'depends'=>array(array('title', '!=', '')),
					'selector'=> array(
						'type'=>'font',
						'font'=>'{{ VALUE }}',
						'css'=>'.sppb-addon-title { font-family: {{ VALUE }}; }'
					)
				),

				'title_fontsize'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std'=>'',
					'responsive' => true,
					'max'=>400,
					'depends'=>array(array('title', '!=', '')),
				),

				'title_lineheight'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std'=>'',
					'responsive' => true,
					'max'=>400,
					'depends'=>array(array('title', '!=', '')),
				),

				'title_font_style'=>array(
					'type'=>'fontstyle',
					'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
					'depends'=>array(array('title', '!=', '')),
				),

				'title_letterspace'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
					'values'=>array(
						'0'=> 'Default',
						'1px'=> '1px',
						'2px'=> '2px',
						'3px'=> '3px',
						'4px'=> '4px',
						'5px'=> '5px',
						'6px'=>	'6px',
						'7px'=>	'7px',
						'8px'=>	'8px',
						'9px'=>	'9px',
						'10px'=> '10px'
					),
					'std'=>'0',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_text_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
					'depends'=>array(array('title', '!=', '')),
				),

				'title_margin_top'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder'=>'10',
					'responsive' => true,
					'max'=>400,
					'depends'=>array(array('title', '!=', '')),
				),

				'title_margin_bottom'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder'=>'10',
					'responsive' => true,
					'max'=>400,
					'depends'=>array(array('title', '!=', '')),
				),

				'separator_addon_options'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ADDON_OPTIONS')
				),

				'symbols'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_SYMBOLS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_SYMBOLS_DESC'),
					'std'=>  'AAPL,FB,TSLA,GOOG,GOOGL,MSFT,INTC,AMD,AMZN,BRK-A,BRK-B,BABA,JNJ,JPM,XOM,BAC,WMT,V,T,CHL,VZ,ORCL'
				),

				'api_key'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_APIKEY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_APIKEY_DESC'),
					'std'=>  'sk_89986f24886a43e79e5931857f21a5b8'
				),

				'list_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIST_TYPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIST_TYPE_DESC'),
					'values'=>array(
						'quote'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIST_TYPE_QUOTE'),
						'chart'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIST_TYPE_CHART'),
					),
					'std'=>'quote',
				),

				'time_span'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_DESC'),
					'values'=>array(
						'2h'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_2HOURS'),
						'6h'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_6HOURS'),
						'12h'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_12HOURS'),
						'1d'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_1DAY'),
						'5d'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_TIMESPAN_5DAYS'),
					),
					'std'=>'12h',
					'depends'=>array('list_type'=>'chart')
				),

				'change_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_CHANGETYPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_CHANGETYPE_DESC'),
					'values'=>array(
						'price'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_CHANGETYPE_PRICE'),
						'percentage'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_CHANGETYPE_PERCENTATE'),
					),
					'std'=>'1d',
					'depends'=>array('list_type'=>'quote')
				),

				'limit'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIMIT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_STOCK_SCROLLER_LIMIT_DESC'),
					'std'=>'5',
					'depends'=>array(
						array('list_type', '!=', 'quote'),
						array('time_span', '!=', '2h'),
						array('time_span', '!=', '6h'),
						array('time_span', '!=', '12h'),
					),
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=>''
				),

			),
		),
	)
);
