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

<form id="adminForm" name="adminForm" action="<?php echo FactoryRouteRss::view('comments&story_id=' . $this->storyId); ?>"
      method="post" class="form-validate form-vertical">
    <fieldset>

        <?php foreach ($this->form->getFieldset('details') as $this->field): ?>
            <div class="control-group">
                <div class="control-label"><?php echo $this->field->label; ?></div>
                <div class="controls"><?php echo $this->field->input; ?></div>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <div class="btn-group">
        <button type="button" class="btn btn-primary btn-small validate" onclick="Joomla.submitbutton('comment.save');">
            <i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
        </button>
    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>
