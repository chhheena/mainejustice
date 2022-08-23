<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION): ?>
    <div class="modal hide fade" id="collapseModal">
        <div class="modal-header">
            <button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
            <h3><?php echo FactoryTextRss::_('submittedfeeds_batch_options'); ?></h3>
        </div>

        <div class="modal-body" style="min-height: 200px;">
            <p><?php echo FactoryTextRss::_('submittedfeeds_batch_tip'); ?></p>

            <div class="control-group">
                <div class="controls">
                    <label id="batch-category-lbl"
                           for="batch-category-id"><?php echo FactoryTextRss::_('submittedfeeds_batch_category'); ?></label>

                    <select name="batch[category_id]" class="inputbox" id="batch-category-id">
                        <option value=""><?php echo JText::_('JSELECT'); ?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_rssfactory'), 'value', 'text'); ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';"
                    data-dismiss="modal">
                <?php echo JText::_('JCANCEL'); ?>
            </button>

            <button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('submittedfeed.batch');">
                <?php echo FactoryTextRss::_('submittedfeeds_batch_process'); ?>
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="modal" tabindex="-1" role="dialog" id="collapseModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo FactoryTextRss::_('submittedfeeds_batch_options'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="form-group">
                        <label id="batch-category-lbl"
                               for="batch-category-id"><?php echo FactoryTextRss::_('submittedfeeds_batch_category'); ?></label>

                        <select name="batch[category_id]" class="inputbox custom-select" id="batch-category-id">
                            <option value=""><?php echo JText::_('JSELECT'); ?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_rssfactory'), 'value', 'text'); ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal"><?php echo JText::_('JCANCEL'); ?></button>
                    <button type="button" class="btn btn-primary"
                            onclick="Joomla.submitbutton('feed.move')"><?php echo FactoryTextRss::_('feeds_batch_move_process'); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
