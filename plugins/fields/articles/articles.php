<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Language as RL_Language;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'ARTICLESFIELD'))
{
	RL_Extension::disable('articles', 'plugin', 'fields');

	RL_Document::adminError(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('ARTICLESFIELD'))
	);

	return;
}

if (true)
{

	JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

	JForm::addFieldPath(JPATH_PLUGINS . '/fields/articles/fields');

	/**
	 * Fields Articles Plugin
	 */
	class PlgFieldsArticles extends FieldsPlugin
	{
	}
}
