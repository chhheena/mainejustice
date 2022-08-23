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

<div class="tab-pane" id="import">
    <fieldset class="uploadform">
        <legend><?php echo FactoryTextRss::_('backup_tab_import_title'); ?></legend>

        <div class="control-group"><?php echo FactoryTextRss::_('backup_tab_import_info'); ?></div>

        <div class="control-group">
            <label for="import_file"
                   class="control-label"><?php echo FactoryTextRss::_('backup_tab_import_select_file'); ?></label>

            <div class="controls">
                <input class="input_box" id="import_file" name="import_file" type="file" size="57"/>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label"><label id="import_separator"
                                              for="import_separator"><?php echo FactoryTextRss::_('backup_tab_import_separator'); ?></label>
            </div>
            <div class="controls">
                <?php echo JHtml::_(
                    'select.genericlist',
                    array(
                        'TAB' => FactoryTextRss::_('backup_tab_import_separator_tab'),
                        ';'   => FactoryTextRss::_('backup_tab_import_separator_semicolon'),
                        ','   => FactoryTextRss::_('backup_tab_import_separator_comma'),
                    ),
                    'import_separator'
                ); ?>
            </div>
        </div>

        <div class="form-actions">
            <input class="btn btn-primary" type="button"
                   value="<?php echo FactoryTextRss::_('backup_tab_import_upload_restore'); ?>"
                   onclick="Joomla.submitbutton('backup.import');"/>
        </div>
    </fieldset>
</div>
