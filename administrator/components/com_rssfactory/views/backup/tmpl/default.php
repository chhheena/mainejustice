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

<form class="form-horizontal"
      action="<?php echo JRoute::_('index.php?option=' . $this->option . '&view=' . $this->getName()); ?>" method="post"
      name="adminForm" id="adminForm" enctype="multipart/form-data">

    <div class="row-fluid row">
        <?php echo $this->loadTemplate('sidebar'); ?>

        <div id="j-main-container" class="<?php echo !empty($this->sidebar) ? 'span10 col-10' : 'span12 col-12'; ?>">

            <fieldset>
                <?php echo JHtmlBootstrap::startTabSet('backup', ['active' => 'backup']); ?>
                    <?php echo JHtmlBootstrap::addTab('backup', 'backup', FactoryTextRss::_('backup_tab_label_backup')); ?>
                        <?php echo $this->loadTemplate('backup'); ?>
                    <?php echo JHtmlBootstrap::endTab(); ?>

                    <?php echo JHtmlBootstrap::addTab('backup', 'restore', FactoryTextRss::_('backup_tab_label_restore')); ?>
                        <?php echo $this->loadTemplate('restore'); ?>
                    <?php echo JHtmlBootstrap::endTab(); ?>

                    <?php echo JHtmlBootstrap::addTab('backup', 'import', FactoryTextRss::_('backup_tab_label_import')); ?>
                        <?php echo $this->loadTemplate('import'); ?>
                    <?php echo JHtmlBootstrap::endTab(); ?>
                <?php echo JHtmlBootstrap::endTabSet(); ?>
            </fieldset>

        </div>

        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
