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

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
if (
	$user->get('guest')
	|| (
		! $user->authorise('core.create', 'com_content')
		&& ! $user->authorise('core.edit', 'com_content')
		&& ! $user->authorise('core.edit.own', 'com_content')
		&& ! count($user->getAuthorisedCategories('com_content', 'core.create'))
		&& ! count($user->getAuthorisedCategories('com_content', 'core.edit'))
	)
)
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

if (RL_Document::isClient('site'))
{
	$params = JComponentHelper::getParams('com_contenttemplater');
	if ( ! $params->get('enable_frontend', 1))
	{
		throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}
}

(new PlgButtonContentTemplaterData)->render();
die;

class PlgButtonContentTemplaterData
{
	public function render()
	{
		header('Content-Type: text/html; charset=utf-8');

		$id = JFactory::getApplication()->input->getInt('id');

		if ( ! $id)
		{
			return;
		}

		RL_Document::style('regularlabs/popup.min.css');
		RL_Document::style('regularlabs/style.min.css');

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		$no_content  = JFactory::getApplication()->input->getInt('no_content', 0);
		$only_fields = JFactory::getApplication()->input->getInt('only_fields', 0);
		$unprotected = ($user->authorise('core.manage', 'com_contenttemplater')) ? JFactory::getApplication()->input->getInt('unprotect') : 0;

		require_once JPATH_ADMINISTRATOR . '/components/com_contenttemplater/models/item.php';

		// Create a new class of classname and set the default task: display
		$model = new ContentTemplaterModelItem;
		$item  = $model->getItem($id, false, true, true);

		if ( ! $item->published)
		{
			return;
		}

		$output = [];

		if ( ! $only_fields)
		{
			foreach ($item->params as $key => $val)
			{
				if ($val == ''
					|| is_object($val)
					|| isset($output[$key])
					|| strpos($key, '@') === 0
				)
				{
					continue;
				}

				if ($key == 'content' && $no_content)
				{
					continue;
				}

				$default      = $item->defaults->{$key} ?? '';
				$form_default = $item->form_defaults->{$key} ?? $default;

				if ($val == $default || ($default == '' && $val == $form_default))
				{
					continue;
				}

				if ($val == -2)
				{
					$val = '';
				}

				[$key, $val] = $this->getStr($model, $key, $val, $form_default);
				$output[$key] = $val;
			}
		}


		[$key, $val] = $this->getStr($model, 'override_content', $item->override_content, 0);
		$output[$key] = $val;

		[$key, $val] = $this->getStr($model, 'override_settings', $item->override_settings, 0);
		$output[$key] = $val;

		$str = implode("\n", $output);

		if ($unprotected)
		{
			echo $str;

			return;
		}

		echo wordwrap(base64_encode($str), 80, "\n", 1);
	}

	public function getStr(&$item, $key, $val, $default = '')
	{
		switch ($key)
		{
			case 'jform_access':
				$default = 1;
				break;
			case 'jform_categories_k2':
				$key     = 'catid';
				$default = 0;
				break;
			case 'jform_categories_zoo':
				$key     = 'categories';
				$default = '';
				break;
		}

		if (is_array($val))
		{
			$val = implode(',', $val);
		}

		if ($key != 'content')
		{

			$val = RL_String::html_entity_decoder($val);

			if (strpos($key, 'jform_') !== false)
			{

				$key = RL_RegEx::replace('jform_(params|attribs|images|urls|metadata|com_fields)_', 'jform[\1][', $key);
				$key = str_replace('jform_', 'jform[', $key) . ']';
			}
		}


		return [$key, '[CT]' . $key . '[CT]' . $default . '[CT]' . $val . '[/CT]'];
	}
}
