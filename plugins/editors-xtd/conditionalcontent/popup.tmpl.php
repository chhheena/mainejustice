<?php
/**
 * @package         Conditional Content
 * @version         4.0.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

$xmlfile = __DIR__ . '/fields.xml';
?>
<div class="reglab-overlay"></div>

<div class="header">
    <h1 class="page-title">
        <span class="icon-reglab icon-conditionalcontent"></span>
        <?php echo JText::_('CONDITIONALCONTENT'); ?>
    </h1>
</div>

<nav class="navbar">
    <div class="navbar-inner">
        <div class="container-fluid">
            <div class="btn-toolbar" id="toolbar">
                <div class="btn-wrapper" id="toolbar-apply">
                    <button onclick="if(RegularLabsConditionalContentPopup.insertText()){window.parent.SqueezeBox.close();}" class="btn btn-small btn-success">
                        <span class="icon-apply icon-white"></span> <?php echo JText::_('RL_INSERT') ?>
                    </button>
                </div>
                <div class="btn-wrapper" id="toolbar-cancel">
                    <button onclick="if(confirm('<?php echo JText::_('RL_ARE_YOU_SURE'); ?>')){window.parent.SqueezeBox.close();}" class="btn btn-small">
                        <span class="icon-cancel "></span> <?php echo JText::_('JCANCEL') ?>
                    </button>
                </div>

                <?php if (JFactory::getApplication()->isClient('administrator') && $user->authorise('core.admin', 1)) : ?>
                    <div class="btn-wrapper" id="toolbar-options">
                        <button onclick="window.open('index.php?option=com_plugins&filter_folder=system&filter_search=<?php echo JText::_('CONDITIONALCONTENT') ?>');" class="btn btn-small">
                            <span class="icon-options"></span> <?php echo JText::_('JOPTIONS') ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid container-main">
    <form action="index.php" id="conditionalcontentForm" method="post" class="form-horizontal">
        <?php
        $form = new JForm('conditionalcontent', ['control' => '']);
        $form->loadFile($xmlfile, 1, '//config');
        ?>
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'tab_content']); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tab_content', JText::_('COC_CONTENT')); ?>
        <div class=" form-vertical">
            <?php echo $form->renderFieldset($this->params->use_editors ? 'content_editor' : 'content'); ?>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tab_alternative_content', JText::_('COC_ALTERNATIVE_CONTENT')); ?>
        <div class=" form-vertical">
            <?php echo $form->renderFieldset($this->params->use_editors ? 'alternative_content_editor' : 'alternative_content'); ?>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tab_conditions', JText::_('COC_CONDITIONS')); ?>
        <div class="">
            <?php echo $form->renderFieldset('conditions'); ?>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </form>
</div>
