<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ContentTemplater;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

class Buttons
{
	static $editor_id = '';

	public static function get($editor = '')
	{
		self::$editor_id = $editor;

		$items = Items::get('button');

		$buttons = [];

		if ( ! empty($items))
		{
			$data = self::getData();

			foreach ($data as $item)
			{
				// button has no items and is not a standalone
				if (empty($item->items) && empty($item->id))
				{
					continue;
				}

				self::setButtonData($item);
				$buttons[] = $item;
			}
		}


		return $buttons;
	}

	private static function getButtonData($item)
	{
		return (object) [
			'id'           => $item->id,
			'text'         => $item->name,
			'image'        => $item->button_image,
			'description'  => $item->description,
			'category'     => $item->category,
			'show_confirm' => $item->show_confirm,
		];
	}

	private static function getButtonObject($text, $id = 0, $image = '', $class = '', $show_confirm = -1)
	{
		return (object) [
			'modal'        => false,
			'class'        => trim('btn ' . $class),
			'link'         => '#',
			'text'         => $text,
			'name'         => '',
			'onclick'      => '',
			'options'      => '',
			'id'           => $id,
			'image'        => $image,
			'items'        => [],
			'show_confirm' => $show_confirm,
		];
	}

	private static function getData()
	{
		$params    = Params::get();
		$all_items = Items::get('button');

		$text_ini = strtoupper(str_replace(' ', '_', $params->button_text));
		$text     = JText::_($text_ini);
		if ($text == $text_ini)
		{
			$text = JText::_($params->button_text);
		}

		$items = [];

		foreach ($all_items as $i => $item)
		{
			$id = [];

			if ($params->display_categories != 'none')
			{
				$id[] = $item->category ?: '0';
			}

			$id[] = $params->orderby == 'name'
				? $item->name
				: RL_String::str_pad($item->ordering, 12, '0', STR_PAD_LEFT);

			$id[] = RL_String::str_pad($i, 12, '0', STR_PAD_LEFT);

			$items[implode('.', $id)] = $item;
		}

		ksort($items);

		$main     = self::getButtonObject($text);
		$separate = [];
		$grouped  = [];

		foreach ($items as $item)
		{
			if ($item->button_separate)
			{
				$button     = self::getButtonObject(
					$item->button_name ?: $item->name,
					$item->id,
					$item->button_image,
					$item->button_class,
					$item->show_confirm
				);
				$separate[] = $button;

				continue;
			}


			$main->items[] = self::getButtonData($item);
		}

		return array_merge([$main], array_merge($separate, $grouped));
	}

	private static function getIconClass($image)
	{
		$params = Params::get();

		// convert image to icon class
		$icon = str_replace('.png', '', $image);

		if ($icon == -1 || $icon == '')
		{
			return $params->button_icon ? 'reglab icon-contenttemplater' : '';
		}

		return $icon;
	}

	private static function setButtonData(&$item)
	{
		$item->name = self::getIconClass($item->image);

		if (empty($item->items))
		{
			self::setButtonDataSeparate($item);

			return;
		}

		$params = Params::get();

		if ($params->open_in_modal == 1
			|| ($params->open_in_modal == 2 && count($item->items) >= $params->switch_to_modal)
		)
		{
			self::setButtonDataModal($item);

			return;
		}

		self::setButtonDataList($item);
	}

	private static function setButtonDataList(&$item)
	{
		$item->onclick = 'ContentTemplater.showList(\'' . $item->id . '\', \'' . self::$editor_id . '\');';
	}

	private static function setButtonDataModal(&$item)
	{
		$item->modal   = true;
		$item->link    = 'index.php?rl_qp=1&folder=plugins.editors-xtd.contenttemplater&file=popup.php'
			. '&id=' . $item->id
			. '&article_id=' . Helper::getArticleId()
			. '&editor=' . self::$editor_id
			. '&Itemid=' . JFactory::getApplication()->input->getInt('Itemid', 0);
		$item->options = "{handler: 'iframe', size: {x:500, y:600}}";
	}

	private static function setButtonDataSeparate(&$item)
	{
		$params = Params::get();

		$onclick = 'ContentTemplater.loadTemplate(\'' . $item->id . '\', \'' . self::$editor_id . '\', \'' . Helper::getArticleId() . '\');';

		if ($item->show_confirm == 1 || ($item->show_confirm == -1 && $params->show_confirm))
		{
			$onclick = 'if(confirm(\'' . sprintf(JText::_('CT_ARE_YOU_SURE', true), '\n') . '\')){' . $onclick . '};';
		}

		$item->onclick = 'try{IeCursorFix();}catch(e){} ' . $onclick;
	}
}
