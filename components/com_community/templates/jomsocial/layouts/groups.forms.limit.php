<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_($isNew ? 'COM_COMMUNITY_GROUPS_CREATE_NEW_GROUP' : 'COM_COMMUNITY_GROUPS_EDIT_TITLE'); ?></h3>
    
    <div class="joms-form__group">
        <p><?php echo JText::sprintf('COM_COMMUNITY_GROUPS_LIMIT_STATUS', $groupCreated, $groupCreationLimit); ?></p>
    </div>

    <div class="joms-form__group">
        <div>
            <input type="button" value="<?php echo JText::_('COM_COMMUNITY_BACK_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
        </div>
    </div>
</div>

