<?php
/**
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;

$clientId = $this->state->get('filter.client_id');

// Show only Module Positions of published Templates
$published                = 1;
$positions                = JHtml::_('modules.positions', $clientId, $published);
$positions['']['items'][] = ModulesHelper::createOption('nochange', JText::_('COM_MODULES_BATCH_POSITION_NOCHANGE'));
$positions['']['items'][] = ModulesHelper::createOption('noposition', JText::_('COM_MODULES_BATCH_POSITION_NOPOSITION'));

// Add custom position to options
$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

// Build field
$attr = [
    'id'        => 'batch-position-id',
    'list.attr' => 'class="chzn-custom-value input-xlarge" '
        . 'data-custom_group_text="' . $customGroupText . '" '
        . 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
        . 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" ',
];

?>
<div class="container-fluid">
    <p><?php echo JText::_('COM_MODULES_BATCH_TIP'); ?></p>
    <div class="row-fluid">
        <?php if ($clientId != 1) : ?>
            <div class="control-group span6">
                <div class="controls">
                    <?php echo JLayoutHelper::render('joomla.html.batch.language', []); ?>
                </div>
            </div>
        <?php elseif ($clientId == 1 && JModuleHelper::isAdminMultilang()) : ?>
            <div class="control-group span6">
                <div class="controls">
                    <?php echo JLayoutHelper::render('joomla.html.batch.adminlanguage', []); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="control-group span6">
            <div class="controls">
                <?php echo JHtml::_('batch.access'); ?>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        <?php if ($published >= 0) : ?>
            <div class="span6">
                <div class="controls">
                    <label id="batch-choose-action-lbl" for="batch-choose-action">
                        <?php echo JText::_('COM_MODULES_BATCH_POSITION_LABEL'); ?>
                    </label>

                    <div id="batch-choose-action" class="control-group">
                        <?php echo JHtml::_('select.groupedlist', $positions, 'batch[position_id]', $attr); ?>
                        <div id="batch-copy-move" class="control-group radio">
                            <?php echo JHtml::_('modules.batchOptions'); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
