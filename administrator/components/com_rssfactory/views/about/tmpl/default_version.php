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

<table>
    <tr>
        <td><?php echo JText::_('ABOUT_YOUR_VERSION'); ?>:</td>
        <td><?php echo $this->current_version; ?></td>
    </tr>
    <tr>
        <td><?php echo JText::_('ABOUT_LATEST_VERSION'); ?>:</td>
        <td><?php echo isset($this->information['latestversion']) ? $this->information['latestversion'] : 'n/a'; ?></td>
    </tr>
    <tr>
        <td colspan="2"
            style="color: #<?php echo !$this->new_version ? '000000' : 'ff0000'; ?>; font-weight: bold; padding-top: 10px;">
            <?php echo JText::_($this->new_version ? 'ABOUT_NEW_VERSION_AVAILABLE' : 'ABOUT_YOU_HAVE_LATEST_VERSION'); ?>
        </td>
    </tr>
</table>
