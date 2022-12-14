<?php
/**
 * @package         Regular Labs Library
 * @version         22.8.11825
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class EasyblogPagetype
 * @package RegularLabs\Library\Condition
 */
class EasyblogPagetype extends Easyblog
{
    public function pass()
    {
        return $this->passByPageType('com_easyblog', $this->selection, $this->include_type);
    }
}
