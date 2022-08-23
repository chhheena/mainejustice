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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Object\CMSObject as JObject;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonHelper as RL_EditorButtonHelper;
use RegularLabs\Plugin\System\ContentTemplater\Buttons as CT_Buttons;

/**
 ** Plugin that places the button
 */
class PlgButtonContentTemplaterHelper extends RL_EditorButtonHelper
{
	/**
	 * Display the button
	 *
	 * @param string $editor_name
	 * @param string $content
	 *
	 * @return [JObject]|null A button object
	 */
	public function render($editor_name)
	{
		$data = CT_Buttons::get($editor_name);

		if (empty($data))
		{
			return null;
		}

		RL_Document::loadEditorButtonDependencies();
		JHtml::_('bootstrap.popover');

		RL_Document::script('contenttemplater/script.min.js', '10.2.0');
		RL_Document::style('contenttemplater/button.min.css', '10.2.0');

		$name = 'rl_ct_button-' . ($editor_name);

		$buttons = [];

		foreach ($data as $button)
		{
			$buttons[] = (object) [
				'modal'   => $button->modal,
				'class'   => $button->class . ' rl_button_contenttemplater rl_button_contenttemplater_' . ($button->id ?: 'main'),
				'text'    => $button->text,
				'name'    => $button->name . ' ' . $name,
				'link'    => $button->link,
				'onclick' => $button->onclick ? $button->onclick . 'return false;' : '',
				'options' => $button->options,
			];
		}


		foreach ($buttons as &$button)
		{
			$button = new JObject($button);
		}

		return $buttons;
	}
}
