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

use ContentTemplaterHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;

class Content
{
	static $editor_placeholder = '[:CT-EDITOR:]';

	public static function get($editors)
	{
		$items = Items::get();

		if (empty($items))
		{
			return false;
		}

		$data = Buttons::get();

		$content = [];

		foreach ($data as $item)
		{
			if (empty($item->items) || $item->modal)
			{
				continue;
			}

			$content[] = self::getContentHtmlList($item);
		}

		$content = implode('', $content);

		$contents = [];
		foreach ($editors as $editor)
		{
			$contents[] = str_replace(self::$editor_placeholder, $editor, $content);
		}

		return implode('', $contents);
	}

	public static function getContentHtmlModal($item)
	{
		$filter_category = JFactory::getApplication()->getUserState('contenttemplater_catid', '');
		$filter_category = JFactory::getApplication()->input->getString('catid', $filter_category);
		JFactory::getApplication()->setUserState('contenttemplater_catid', $filter_category);

		[$options, $categories] = self::getOptions($item->items, true, $filter_category);

		$layout = new JLayoutFile('modal', __DIR__ . '/layouts');

		return $layout->render([
			'form_id'    => 'contenttemplater-modal-' . self::$editor_placeholder . '-' . $item->id,
			'options'    => $options,
			'categories' => $categories,
			'toolbar'    => self::getContentHtmlModalToolbar(),
		]);
	}

	/**
	 * place content on page load
	 */
	public static function loadDefault(&$buffer)
	{
	}

	public static function place(&$buffer)
	{
		$editors = Editors::get($buffer);

		if (empty($editors))
		{
			return;
		}

		$content = self::get($editors);

		if (empty($content))
		{
			return;
		}

		$buffer .= '<div style="display:none;" class="contenttemplater_data">'
			. $content
			. '</div>';
	}

	private static function getContentHtmlList($item)
	{
		[$options, $categories] = self::getOptions($item->items);

		$layout = new JLayoutFile('list', __DIR__ . '/layouts');

		return $layout->render([
			'id'         => 'contenttemplater-list-' . self::$editor_placeholder . '-' . $item->id,
			'options'    => $options,
			'categories' => $categories,
		]);
	}

	private static function getContentHtmlModalToolbar()
	{
		if (RL_Document::isClient('site'))
		{
			return '';
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_contenttemplater/helpers/helper.php';
		$canDo = ContentTemplaterHelper::getActions();
		if ( ! $canDo->get('core.create'))
		{
			return '';
		}

		$layout = new JLayoutFile('modal_toolbar', __DIR__ . '/layouts');

		return $layout->render('');
	}

	private static function getItemImage($image)
	{
		// convert image to icon class
		$icon = str_replace('.png', '', $image);

		if (empty($icon) || $icon == -1)
		{
			return '';
		}

		return '<span class="icon-' . $icon . '"></span> ';
	}

	private static function getOptions($items, $is_modal = false, $filter_category = null)
	{
		$options    = [];
		$categories = [];

		if (empty($items))
		{
			return [$options, $categories];
		}

		$params = Params::get();

		$previous_category = '';

		foreach ($items as $item)
		{
			$category      = $item->category;
			$category_icon = '';

			if (strpos($category, '::'))
			{
				[$category, $category_icon] = explode('::', $category, 2);
				$category_icon = '<span class="icon-' . $category_icon . '"></span> ';
			}

			$categories[$category] = $category;

			if ($filter_category && $filter_category != $category)
			{
				continue;
			}

			if ( ! $filter_category && $params->display_categories == 'titled' && $category != $previous_category)
			{
				$options[] = '<span>' . $category_icon . '<strong>' . $category . '</strong></span>';
			}

			$onclick = ($is_modal ? 'parent.' : '')
				. 'ContentTemplater.loadTemplate(' . $item->id . ', \'' . self::$editor_placeholder . '\', \'' . Helper::getArticleId() . '\', false, ' . ($is_modal ? 'true' : 'false') . ');';

			if ($item->show_confirm == 1 || ($item->show_confirm == -1 && $params->show_confirm))
			{
				$onclick = 'if( confirm(\'' . sprintf(JText::_('CT_ARE_YOU_SURE', true), '\n') . '\') ) { ' . $onclick . ' };';
			}

			$image = self::getItemImage($item->image);

			$layout = new JLayoutFile('option', __DIR__ . '/layouts');

			$options[] = $layout->render([
				'text'        => $item->text,
				'description' => $item->description,
				'onclick'     => $onclick . ';return false;',
				'image'       => $image,
			]);

			$previous_category = $category;
		}

		if (count($categories) == 1)
		{
			$categories = [];
		}

		asort($categories);

		return [$options, $categories];
	}

}
