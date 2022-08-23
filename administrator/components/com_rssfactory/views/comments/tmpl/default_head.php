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

<thead>
<tr>
    <th width="1%" class="hidden-phone">
        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
               onclick="Joomla.checkAll(this)"/>
    </th>

    <th width="1%" style="min-width:55px" class="nowrap center">
        <?php echo JHtml::_('grid.sort', 'JSTATUS', 'c.published', $this->listDirn, $this->listOrder); ?>
    </th>

    <th>
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_COMMENTS_LIST_TEXT', 'c.text', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="15%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_COMMENTS_LIST_STORY', 'cache.item_title', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="15%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_COMMENTS_LIST_USERNAME', 'u.username', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="20%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_COMMENTS_LIST_CREATED_AT', 'c.created_at', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="1%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'c.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
</thead>
