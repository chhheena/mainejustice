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

<div class="row-fluid row">
    <?php if (!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2 col-2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php endif; ?>

    <div id="j-main-container" class="<?php echo !empty($this->sidebar) ? 'span10 col-10' : 'span12 col-12'; ?>">
        <form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&view=' . $this->getName()); ?>"
              method="post" name="adminForm" id="adminForm">

            <?php echo JHtmlBootstrap::startTabSet('settings', ['active' => 'general']); ?>
            <?php echo JHtmlBootstrap::addTab('settings', 'general', FactoryTextRss::_('configuration_tab_general')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('general_general'); ?>
                    <?php echo $this->loadFieldset('general_comments'); ?>
                    <?php echo $this->loadFieldset('general_ads'); ?>
                </div>

                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('general_charset'); ?>
                    <?php echo $this->loadFieldset('general_cache'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::addTab('settings', 'display', FactoryTextRss::_('configuration_tab_display')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('display_read_more_link'); ?>
                </div>

                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('display_feeds'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::addTab('settings', 'refresh', FactoryTextRss::_('configuration_tab_refresh')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('refresh_general'); ?>
                </div>

                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('refresh_word_filter'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::addTab('settings', 'cron', FactoryTextRss::_('configuration_tab_cron')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('cron_cron'); ?>
                </div>

                <div class="span6 col-6">

                    <?php echo $this->loadFieldset('cron_pseudo_cron'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php $notice = '<span class="label label-important badge badge-danger badge-pill">' . FactoryTextRss::_('pro_version_notice') . '</span>'; ?>
            <?php $label = FactoryTextRss::_('configuration_tab_import2content') . (!$this->form->getFieldset('import2content_general') ? '&nbsp;' . $notice : null); ?>
            <?php echo JHtmlBootstrap::addTab('settings', 'import2content', $label); ?>
            <?php if ($this->form->getFieldset('import2content_general')): ?>
                <div class="row-fluid row">
                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_general'); ?>
                    </div>

                    <div class="span6 col-6">
                        <?php echo $this->loadFieldset('import2content_default_params'); ?>
                        <?php echo $this->loadFieldset('import2content_relevant_stories'); ?>
                        <?php echo $this->loadFieldset('import2content_word_filter'); ?>
                    </div>
                </div>
            <?php else: ?>
                <?php echo FactoryTextRss::_('feature_available_in_pro_version'); ?>
            <?php endif; ?>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::addTab('settings', 'permissions', FactoryTextRss::_('configuration_tab_permissions')); ?>
            <div class="row-fluid row">
                <div class="span12 col-12">
                    <?php echo $this->loadFieldset('permissions'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::addTab('settings', 'systeminfo', FactoryTextRss::_('configuration_tab_systeminfo')); ?>
            <div class="row-fluid row">
                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('systeminfo_joomla_settings'); ?>
                </div>

                <div class="span6 col-6">
                    <?php echo $this->loadFieldset('systeminfo_php_settings'); ?>
                </div>
            </div>
            <?php echo JHtmlBootstrap::endTab(); ?>

            <?php echo JHtmlBootstrap::endTabSet(); ?>

            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>

        </form>
    </div>
</div>
