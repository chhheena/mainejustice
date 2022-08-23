<?php
/**
 * @package         Conditional Content
 * @version         4.0.0
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
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ConditionalContent\Protect;
use RegularLabs\Plugin\System\ConditionalContent\Replace;

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
    JFactory::getLanguage()->load('plg_system_conditionalcontent', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('COC_EXTENSION_CAN_NOT_FUNCTION', JText::_('CONDITIONALCONTENT'))
        . ' ' . JText::_('COC_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'CONDITIONALCONTENT'))
{
    RL_Extension::disable('conditionalcontent', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('CONDITIONALCONTENT'))
    );

    return;
}

if (true)
{
    class PlgSystemConditionalContent extends RL_SystemPlugin
    {
        public $_lang_prefix           = 'COC';
        public $_has_tags              = true;
        public $_disable_on_components = true;
        public $_can_disable_by_url    = false;
        public $_jversion              = 3;

        public function processArticle(&$string, $area = 'article', $context = '', $article = null, $page = 0)
        {
            Replace::replaceTags($string, $area, $context);
        }

        protected function changeDocumentBuffer(&$buffer)
        {
            return Replace::replaceTags($buffer, 'component');
        }

        protected function changeFinalHtmlOutput(&$html)
        {
            // only do stuff in body
            [$pre, $body, $post] = RL_Html::getBody($html);
            Replace::replaceTags($body, 'body');
            $html = $pre . $body . $post;

            return true;
        }

        protected function cleanFinalHtmlOutput(&$html)
        {
            Protect::unprotectTags($html);
            //RL_Protect::removeInlineComments($html, 'ConditionalContent');
        }
    }
}
