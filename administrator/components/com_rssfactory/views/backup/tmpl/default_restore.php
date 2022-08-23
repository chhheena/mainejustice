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

<div class="tab-pane" id="restore">
    <fieldset class="uploadform">
        <legend><?php echo FactoryTextRss::_('backup_tab_restore_title'); ?></legend>

        <div class="control-group"><?php echo FactoryTextRss::_('backup_tab_restore_info'); ?></div>

        <div class="control-group">
            <label for="restore_archive"
                   class="control-label"><?php echo FactoryTextRss::_('backup_tab_restore_select_file'); ?></label>

            <div class="controls">
                <input class="input_box" id="restore_archive" name="restore_archive" type="file" size="57"/>
            </div>
        </div>

        <div class="form-actions">
            <input class="btn btn-primary" type="button"
                   value="<?php echo FactoryTextRss::_('backup_tab_restore_upload_restore'); ?>"
                   onclick="Joomla.submitbutton('backup.restore');"/>
        </div>
    </fieldset>
</div>
