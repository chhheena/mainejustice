<?php
/**
 * @package         Components Anywhere
 * @version         4.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ComponentsAnywhere\Component;
use RegularLabs\Plugin\System\ComponentsAnywhere\Document;
use RegularLabs\Plugin\System\ComponentsAnywhere\Params;
use RegularLabs\Plugin\System\ComponentsAnywhere\Protect;
use RegularLabs\Plugin\System\ComponentsAnywhere\Replace;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = JFactory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/SystemPlugin.php')
)
{
	JFactory::getLanguage()->load('plg_system_componentsanywhere', __DIR__);
	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('CA_EXTENSION_CAN_NOT_FUNCTION', JText::_('COMPONENTSANYWHERE'))
		. ' ' . JText::_('CA_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
		'error'
	);

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COMPONENTSANYWHERE'))
{
	RL_Extension::disable('componentsanywhere', 'plugin');

	RL_Document::adminError(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('COMPONENTSANYWHERE'))
	);

	return;
}

if (true)
{
	class PlgSystemComponentsAnywhere extends RL_SystemPlugin
	{
		public $_lang_prefix = 'CA';

		public $_has_tags              = true;
		public $_disable_on_components = true;
		public $_jversion              = 3;

		private $render_component = null;

		public function processArticle(&$string, $area = 'article', $context = '', $article = null, $page = 0)
		{
			Replace::processComponents($string, $area, $context, $article);
		}

		protected function changeDocumentBuffer(&$buffer)
		{
			if (JFactory::getApplication()->input->get('rendercomponent'))
			{
				$this->render_component = Component::getObject($buffer);
			}

			return Replace::replaceTags($buffer, 'component');
		}

		protected function changeFinalHtmlOutput(&$html)
		{
			if (RL_Document::isFeed())
			{
				Replace::replaceTags($html);

				return true;
			}

			[$pre, $body, $post] = RL_Html::getBody($html);

			if (JFactory::getApplication()->input->get('rendercomponent'))
			{
				$body = RL_RegEx::replace('^\s*<body.*?>\s*', '', $body);
				$body = RL_RegEx::replace('\s*</body>\s*$', '', $body);

				$this->render_component->html = $body;

				Component::render($this->render_component);

				return false;
			}

			// only do stuff in body
			Replace::replaceTags($body, 'body');

			Document::placeStylesAndScripts($pre, $body);
			$html = $pre . $body . $post;

			return true;
		}

		protected function cleanFinalHtmlOutput(&$html)
		{
			RL_Protect::removeAreaTags($html, 'COMPA');

			$params = Params::get();

			Protect::unprotectTags($html);

			RL_Protect::removeFromHtmlTagContent($html, Params::getTags(true));
			RL_Protect::removeInlineComments($html, 'Components Anywhere');

			if ( ! $params->place_comments)
			{
				RL_Protect::removeCommentTags($html, 'Components Anywhere');
			}
		}
	}
}
