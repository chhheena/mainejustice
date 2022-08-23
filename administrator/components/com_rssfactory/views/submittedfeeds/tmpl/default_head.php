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

    <th>
        <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'f.title', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="25%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_SUBMITTEDFEEDS_LIST_URL', 'f.url', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="20%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'COM_RSSFACTORY_SUBMITTEDFEEDS_LIST_DATE', 'f.date', $this->listDirn, $this->listOrder); ?>
    </th>

    <th width="1%" class="nowrap hidden-phone">
        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'f.id', $this->listDirn, $this->listOrder); ?>
    </th>
</tr>
</thead>
