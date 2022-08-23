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

<div class="tab-pane active" id="backup">
    <fieldset class="uploadform">
        <legend><?php echo FactoryTextRss::_('backup_tab_backup_title'); ?></legend>

        <div class="control-group"><?php echo FactoryTextRss::_('backup_tab_backup_info'); ?></div>
        <div class="form-actions">
            <input class="btn btn-primary" type="button"
                   value="<?php echo FactoryTextRss::_('backup_tab_restore_generate_backup'); ?>"
                   onclick="Joomla.submitbutton('backup.generate');"/>
        </div>
    </fieldset>
</div>
