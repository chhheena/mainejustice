<?php
/**
 * @package         Advanced Module Manager
 * @version         9.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', ['placement' => 'bottom']);
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.apply');"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.cancel');"></button>

<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
