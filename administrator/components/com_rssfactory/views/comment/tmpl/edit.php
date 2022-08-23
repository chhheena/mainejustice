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

?>

<form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&layout=edit&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
    <input type="hidden" name="jform[categories_assigned]" value=""/>

    <div class="span10 form-horizontal">
        <?php echo $this->loadFieldset('details'); ?>
    </div>

    <div class="span2">
        <h4><?php echo JText::_('JDETAILS'); ?></h4>
        <hr/>

        <fieldset class="form-vertical">

            <div class="control-group">
                <div class="controls">
                    <?php echo $this->form->getValue('title'); ?>
                </div>
            </div>

            <?php foreach ($this->form->getFieldset('sidebar') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label; ?></div>
                    <div class="controls"><?php echo $field->input; ?></div>
                </div>
            <?php endforeach; ?>
        </fieldset>

    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
