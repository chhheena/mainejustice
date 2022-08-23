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

<tr class="row<?php echo $this->i % 2; ?>">
    <td class="center hidden-phone">
        <?php echo JHtml::_('grid.id', $this->i, $this->item->id); ?>
    </td>

    <td class="center">
        <div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $this->item->published, $this->i, 'comments.', true); ?>
        </div>
    </td>

    <td class="nowrap has-context">
        <a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=comment.edit&id=' . $this->item->id); ?>"
           title="<?php echo JText::_('JACTION_EDIT'); ?>">
            <?php echo JHtml::_('string.truncate', $this->escape($this->item->text), 50); ?>
        </a>
    </td>

    <td class="nowrap has-context">
        <a href="<?php echo JRoute::_(JUri::root() . 'index.php?option=com_rssfactory&task=comments&story_id=' . $this->item->item_id, false); ?>"
           target="_blank">
            <?php echo JHtml::_('string.truncate', $this->escape($this->item->item_title), 20); ?>
        </a>
    </td>

    <td class="nowrap has-context">
        <?php if ($this->item->user_id): ?>
            <a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $this->item->user_id); ?>"
               title="<?php echo JText::_('JACTION_EDIT'); ?>">
                <?php echo $this->escape($this->item->username); ?>
            </a>
        <?php else: ?>
            <?php echo FactoryTextRss::_('comments_list_guest'); ?>
        <?php endif; ?>
    </td>

    <td class="hidden-phone small">
        <?php echo JHtml::_('date', $this->item->created_at, JText::_('DATE_FORMAT_LC2')); ?>
    </td>

    <td class="center hidden-phone">
        <?php echo $this->item->id; ?>
    </td>
</tr>
