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

<tr class="row<?php echo $this->i % 2; ?>" sortable-group-id="1">
    <td class="order nowrap center hidden-phone">
    <span class="sortable-handler hasTooltip <?php echo $this->saveOrder ? '' : 'inactive tip-top'; ?>"
          title="<?php echo $this->saveOrder ? '' : JText::_('JORDERINGDISABLED'); ?>">
			<i class="icon-menu"></i>
    </span>
        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $this->item->ordering; ?>"
               class="width-20 text-area-order "/>
    </td>

    <td class="center hidden-phone">
        <?php echo JHtml::_('grid.id', $this->i, $this->item->id); ?>
    </td>

    <td class="center">
        <div class="btn-group">
            <?php echo JHtml::_('jgrid.published', $this->item->published, $this->i, 'feeds.', true); ?>
        </div>
    </td>

    <td>
        <?php echo JHtml::_('feeds.icon', $this->item->id); ?>
    </td>

    <td class="nowrap has-context" style="line-height: normal;">
        <div class="pull-left">
            <a style="font-weight: bold;"
               href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=feed.edit&id=' . $this->item->id); ?>"
               title="<?php echo JText::_('JACTION_EDIT'); ?>">
                <?php echo $this->escape($this->item->title); ?>
            </a>

            <div class="small muted">
                <?php echo JText::_($this->item->category_title); ?>
            </div>

            <div class="small">
                <a href="<?php echo $this->item->url; ?>" target="_blank" class="muted">
                    <?php echo JHtml::_('string.abridge', $this->item->url, 50); ?>
                </a>
            </div>
        </div>

        <div class="pull-left">
            <?php echo JHtmlRssFactory::itemDropDown(array(
                'edit'       => array('id' => $this->item->id, 'prefix' => 'feed'),
                'publish'    => array('i' => $this->i, 'published' => $this->item->published, 'prefix' => 'feeds'),
                'refresh'    => array('i' => $this->i, 'prefix' => 'feeds'),
                'divider'    => array(),
                'clearcache' => array('i' => $this->i, 'prefix' => 'feeds'),
            )); ?>
        </div>
    </td>

    <td class="hidden-phone center">
        <b><?php echo $this->item->storiesCached; ?></b> / <?php echo $this->item->nrfeeds; ?>
    </td>

    <td class="hidden-phone small" style="line-height: normal;">
        <?php if (!is_null($this->item->date)): ?>
            <?php echo JHtml::_('date', $this->item->date, JText::_('DATE_FORMAT_LC2')); ?>

            <div class="muted">
                <?php echo JText::plural('COM_RSSFACTORY_FEEDS_LAST_REFRESH_NO_NEW_STORIES', $this->item->last_refresh_stories); ?>
            </div>
        <?php endif; ?>
    </td>

    <td class="hidden-phone center small">
        <?php if ($this->item->i2c_enabled): ?>
            <i class="icon-publish"></i>
        <?php endif; ?>
    </td>

    <td class="hidden-phone center small">
        <?php if ($this->item->rsserror): ?>
            <i style="cursor: pointer;" class="icon-publish hasTooltip"
               title="<?php echo $this->item->last_error; ?>"></i>
        <?php endif; ?>
    </td>

    <td class="center hidden-phone">
        <?php echo $this->item->id; ?>
    </td>
</tr>
