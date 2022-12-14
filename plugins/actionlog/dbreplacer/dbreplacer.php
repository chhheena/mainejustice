<?php
/**
 * @package         DB Replacer
 * @version         7.4.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Library\ActionLogPlugin as RL_ActionLogPlugin;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Log as RL_Log;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ActionLogPlugin.php')
)
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
	RL_Extension::disable('dbreplacer', 'plugin', 'actionlog');

	return;
}

if (true)
{
	class PlgActionlogDBReplacer extends RL_ActionLogPlugin
	{
		public $name  = 'DBREPLACER';
		public $alias = 'dbreplacer';

		public function onAfterDatabaseReplace($context, $table_name)
		{
			if (strpos($context, $this->option) === false)
			{
				return;
			}

			if ( ! RL_Array::find(['*', 'replacement'], $this->events))
			{
				return;
			}

			$languageKey = 'DBR_ACTIONLOGS_REPLACEMENT';

			$message = [
				'table_name'     => (string) $table_name,
				'extension_name' => $this->name,
				'extension_link' => 'index.php?option=com_dbreplacer',
			];

			RL_Log::add($message, $languageKey, $context);
		}
	}
}
