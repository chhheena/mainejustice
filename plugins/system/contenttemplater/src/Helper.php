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

class Helper
{
	public static function getArticleId()
	{
		$input = JFactory::getApplication()->input;

		if ($input->get('folder') == 'plugins.editors-xtd.contenttemplater')
		{
			return $input->get('article_id');
		}

		if ($input->get('option') != 'com_content'
			|| $input->get('view') != 'article'
			|| $input->get('layout') != 'edit'
			|| ! $input->get('id'))
		{
			return 0;
		}

		return $input->get('id');
	}
}
