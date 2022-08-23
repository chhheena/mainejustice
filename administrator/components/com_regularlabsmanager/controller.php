<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.1.3
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController as JController;

/**
 * Master Display Controller
 */
class RegularLabsManagerController extends JController
{
    /**
     * @var        string    The default view.
     */
    protected $default_view = 'default';
}
