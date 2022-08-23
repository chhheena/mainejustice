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

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Plugin\System\ContentTemplater\Buttons as CT_Buttons;
use RegularLabs\Plugin\System\ContentTemplater\Content as CT_Content;

require_once JPATH_PLUGINS . '/system/contenttemplater/vendor/autoload.php';

// load the admin language file
RL_Language::load('plg_editors-xtd_contenttemplater');

$params = RL_Parameters::getComponent('contenttemplater');

$id     = JFactory::getApplication()->input->get('id');
$editor = JFactory::getApplication()->input->getString('editor');

$data = CT_Buttons::get();

$content = '';

foreach ($data as $item)
{
	if ($item->id . '' !== $id)
	{
		continue;
	}

	$content = CT_Content::getContentHtmlModal($item);
	break;
}

echo str_replace('[:CT-EDITOR:]', $editor, $content);
