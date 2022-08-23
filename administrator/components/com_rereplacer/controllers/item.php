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

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController as JControllerForm;

jimport('joomla.application.component.controllerform');

/**
 * Item Controller
 */
class ReReplacerControllerItem extends JControllerForm
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'RL';
    // Parent class access checks are sufficient for this controller.
}
