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

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

if ($user->get('guest'))
{
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$params = RL_Parameters::getPlugin('conditionalcontent');

if (RL_Document::isClient('site') && ! $params->enable_frontend)
{
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

(new PlgButtonConditionalContentPopup($params))->render();

class PlgButtonConditionalContentPopup
{
    var $params = null;

    public function __construct(&$params)
    {
        $this->params = $params;
    }

    public function render()
    {
        jimport('joomla.filesystem.file');

        // Load plugin language
        RL_Language::load('plg_system_regularlabs');
        RL_Language::load('plg_editors-xtd_conditionalcontent');
        RL_Language::load('plg_system_conditionalcontent');

        RL_Document::loadPopupDependencies();

        // Tag character start and end
        [$tag_start, $tag_end] = explode('.', $this->params->tag_characters);

        $editor = JFactory::getApplication()->input->getString('name', 'text');
        // Remove any dangerous character to prevent cross site scripting
        $editor = RL_RegEx::replace('[\'\";\s]', '', $editor);

        $script = "
            var conditionalcontent_tag_show = '" . RL_RegEx::replace('[^a-z0-9-_]', '', $this->params->tag_show) . "';
            var conditionalcontent_tag_hide = '" . RL_RegEx::replace('[^a-z0-9-_]', '', $this->params->tag_hide) . "';
            var conditionalcontent_tag_characters = ['" . $tag_start . "', '" . $tag_end . "'];
            var conditionalcontent_editorname = '" . $editor . "';
        ";
        RL_Document::scriptDeclaration($script);

        RL_Document::script('conditionalcontent/popup.min.js', '4.0.0');

        echo $this->getHTML();
    }

    private function getHTML()
    {
        ob_start();
        include __DIR__ . '/popup.tmpl.php';
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
