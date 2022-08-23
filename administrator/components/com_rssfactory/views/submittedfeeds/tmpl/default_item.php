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

    <td class="nowrap has-context">
        <?php echo $this->escape($this->item->title); ?>

        <div class="small">
            <?php echo JText::_($this->item->comment); ?>
        </div>
    </td>

    <td class="hidden-phone small">
        <a href="<?php echo $this->item->url; ?>" target="_blank">
            <?php echo JHtml::_('string.truncate', $this->escape($this->item->url), 40); ?>
        </a>
    </td>

    <td class="hidden-phone small">
        <?php echo JHtml::_('date', $this->item->date, JText::_('DATE_FORMAT_LC2')); ?>
    </td>

    <td class="center hidden-phone">
        <?php echo $this->item->id; ?>
    </td>
</tr>
