<?php
/**
 * @package         ReReplacer
 * @version         12.4.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use RegularLabs\Library\ActionLogPlugin as RL_ActionLogPlugin;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ActionLogPlugin.php')
)
{
    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
    RL_Extension::disable('rereplacer', 'plugin', 'actionlog');

    return;
}

if (true)
{
    class PlgActionlogReReplacer extends RL_ActionLogPlugin
    {
        public $name  = 'REREPLACER';
        public $alias = 'rereplacer';

        public function __construct(&$subject, array $config = [])
        {
            parent::__construct($subject, $config);

            $this->items = [
                'item' => (object) [
                    'title' => 'RR_ITEM',
                ],
            ];
        }
    }
}
