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

    <?php echo JHtmlBootstrap::startTabSet('feed', ['active' => 'details']); ?>
        <?php echo JHtmlBootstrap::addTab('feed', 'details', \Joomla\CMS\Language\Text::_('JDETAILS')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('details'); ?>
                    <?php echo $this->loadFieldset('http'); ?>
                    <?php echo $this->loadFieldset('ftp'); ?>
                </div>
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('filter'); ?>
                </div>
            </div>
        <?php echo JHtmlBootstrap::endTab(); ?>

        <?php $notice = '<span class="label label-important badge badge-danger badge-pill">' . FactoryTextRss::_('pro_version_notice') . '</span>'; ?>
        <?php $label = FactoryTextRss::_('form_feed_fieldset_import2content') . (!$this->form->getFieldset('import2content_details') ? '&nbsp;' . $notice : null); ?>
        <?php echo JHtmlBootstrap::addTab('feed', 'import2content', $label); ?>
            <?php if ($this->form->getFieldset('import2content_details')): ?>
                <div class="row-fluid row">
                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_details'); ?>
                        <?php echo $this->loadFieldset('import2content_filter'); ?>
                    </div>
                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_relevant_stories'); ?>
                        <?php echo $this->loadFieldset('import2content_publishing'); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php echo FactoryTextRss::_('feature_available_in_pro_version'); ?>
            <?php endif; ?>
        <?php echo JHtmlBootstrap::endTab(); ?>

        <?php $notice = '<span class="label label-important badge badge-danger badge-pill">' . FactoryTextRss::_('pro_version_notice') . '</span>'; ?>
        <?php $label = FactoryTextRss::_('form_feed_fieldset_import2content_rules') . (!$this->form->getFieldset('import2content_details') ? '&nbsp;' . $notice : null); ?>
        <?php echo JHtmlBootstrap::addTab('feed', 'import2content_rules', $label); ?>
            <?php if ($this->form->getFieldset('import2content_details')): ?>
                <div class="row-fluid row">
                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_rules_details'); ?>
                        <?php echo $this->loadFieldset('import2content_rules_preview'); ?>
                    </div>

                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_rules'); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php echo FactoryTextRss::_('feature_available_in_pro_version'); ?>
            <?php endif; ?>
        <?php echo JHtmlBootstrap::endTab(); ?>
    <?php echo JHtmlBootstrap::endTabSet(); ?>

    <input type="hidden" name="task" value=""/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<?php echo $this->loadTemplate('preview'); ?>
