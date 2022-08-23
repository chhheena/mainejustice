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
            <?php echo JHtml::_('jgrid.published', $this->item->published, $this->i, 'ads.', true); ?>
        </div>
    </td>

    <td class="nowrap has-context">
        <a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ad.edit&id=' . $this->item->id); ?>"
           title="<?php echo JText::_('JACTION_EDIT'); ?>">
            <?php echo $this->escape($this->item->title); ?>
        </a>
    </td>

    <td class="small hidden-phone">
        <?php if ($this->item->categories): ?>
            <?php echo $this->escape($this->item->categories); ?>
        <?php else: ?>
            <?php echo FactoryTextRss::_('ads_list_all_categories'); ?>
        <?php endif; ?>

    </td>

    <td class="center hidden-phone">
        <?php echo $this->item->id; ?>
    </td>
</tr>
