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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;

RL_Document::loadFormDependencies();
JHtml::_('behavior.formvalidator');
JText::script('ERROR');
?>
<style>
    #toolbar-popup-help {
        float: right;
    }
</style>

<form action="<?php echo JRoute::_('index.php?option=com_rereplacer&id=' . ( int ) $this->item->id); ?>" method="post"
      name="adminForm" id="item-form" class="form-validate">

    <div class="form-inline form-inline-header">
        <?php echo $this->item->form->renderField('name'); ?>
    </div>

    <div class="row-fluid form-horizontal">
        <div class="span12">
            <?php echo JHtml::_('bootstrap.startTabSet', 'main', ['active' => 'details']); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'details', JText::_('JDETAILS')); ?>
            <div class="row-fluid form-vertical">
                <div class="span6">
                    <fieldset>
                        <?php echo $this->render($this->item->form, 'search'); ?>
                        <?php echo $this->render($this->item->form, 'replace'); ?>
                        <?php echo $this->render($this->item->form, 'xml'); ?>
                    </fieldset>

                    <p><?php echo JText::sprintf('RR_HELP_ON_REGULAR_EXPRESSIONS', '<a href="index.php?rl_qp=1&folder=media.rereplacer.images&file=popup.php" target="_blank">', '</a>'); ?></p>
                </div>
                <div class="span3">
                    <fieldset><?php echo $this->render($this->item->form, 'options'); ?></fieldset>
                </div>
                <div class="span3">
                    <fieldset><?php echo $this->render($this->item->form, 'details'); ?></fieldset>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'areas', JText::_('RR_SEARCH_AREAS')); ?>
            <fieldset><?php echo $this->render($this->item->form, 'areas'); ?></fieldset>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'assignments', JText::_('RL_PUBLISHING_ASSIGNMENTS')); ?>
            <fieldset><?php echo $this->render($this->item->form, 'assignments'); ?></fieldset>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
    jQuery(document).ready(function() {
        if (Joomla.editors.instances['jform_search']) {
            Joomla.editors.instances['jform_search'].focus();
        }
    });

    Joomla.submitbutton = function(task) {
        if ( ! checkFields(task)) {
            return;
        }

        var f = document.getElementById('item-form');

        if (self != top) {
            if (task == 'item.cancel' || task == 'item.save') {
                f.target = '_top';
            } else {
                f.action += '&tmpl=component';
            }
        }
        Joomla.submitform(task, f);
    };

    function checkFields(task) {
        if (task == 'item.cancel') {
            return true;
        }

        var f = document.getElementById('item-form');

        error = {"error": []};

        if (f['jform[name]'].value == '') {
            error.error.unshift('<?php echo JText::_('RR_THE_ITEM_MUST_HAVE_A_NAME', true); ?>');
        }

            var search_editor = Joomla.editors.instances['jform_search'];
            var search_value  = search_editor ? search_editor.getValue() : f['jform[search]'].value.trim();

            if (search_value == '') {
                error.error.unshift('<?php echo JText::_('RR_THE_ITEM_MUST_HAVE_SOMETHING_TO_SEARCH_FOR', true); ?>');
            }

        if (error.error.length) {
            Joomla.renderMessages(error);
            return false;
        }

        return true;
    }
</script>
