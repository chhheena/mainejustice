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
    <h3 class="joms-page__title"><?php echo JText::_($isNew ? 'COM_COMMUNITY_PAGES_CREATE_NEW_PAGE' : 'COM_COMMUNITY_PAGES_EDIT_TITLE'); ?></h3>
    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">

        <div class="joms-form__group">
            <?php if ($isNew) { ?>
                <?php if ($pageCreationLimit != 0 && $pageCreated / $pageCreationLimit >= COMMUNITY_SHOW_LIMIT) { ?>
                <p><?php echo JText::sprintf('COM_COMMUNITY_PAGES_LIMIT_STATUS', $pageCreated, $pageCreationLimit); ?></p>
                <?php } ?>
            <?php } ?>
        </div>

        <?php if ($beforeFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $beforeFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group" style="margin-bottom:5px">
            <span><?php echo JText::_('COM_COMMUNITY_PAGES_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="name" required=""
                title="<?php echo JText::_('COM_COMMUNITY_PAGES_TITLE_TIPS'); ?>"
                value="<?php echo $this->escape($page->name); ?>">
        </div>
        <div class="joms-form__group">
            <span></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="approvals" onclick="joms_checkPrivacy();" value="1"
                        <?php echo ($page->approvals == COMMUNITY_PRIVATE_PAGE) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_APPROVAL_TIPS'); ?>">
                        <?php echo JText::_('COM_COMMUNITY_PAGES_PRIVATE_LABEL'); ?></span>
                </label>
            </div>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="unlisted" value="1"
                        <?php echo ($page->approvals == COMMUNITY_PRIVATE_PAGE) ? '' : ' disabled="disabled"'; ?>
                        <?php echo ($page->unlisted == 1 && $page->approvals == COMMUNITY_PRIVATE_PAGE) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_UNLISTED_TIPS'); ?>">
                        <?php echo JText::_('COM_COMMUNITY_PAGES_UNLISTED'); ?>
                    </span>
                </label>
            </div>
        </div>

        <script type="text/javascript">
            function joms_checkPrivacy() {
                var eventClosedCheckbox = joms.jQuery('[name=approvals]');
                var eventUnlistedCheckbox = joms.jQuery('[name=unlisted]');

                if( eventClosedCheckbox.prop('checked') === true ) {
                    eventUnlistedCheckbox.removeAttr('disabled');
                } else {
                    eventUnlistedCheckbox[0].checked = false;
                    eventUnlistedCheckbox.attr('disabled', 'disabled');
                }
            }
        </script>
        <div class="joms-form__group">
            <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_SUMMARY_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_SUMMARY'); ?></span>
            <textarea class="joms-textarea" name="summary" data-maxchars="120"><?php echo $this->escape($page->summary); ?></textarea>
        </div>

        <div class="joms-form__group joms-textarea--mobile">
            <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_DESCRIPTION_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_DESCRIPTION'); ?> <span class="joms-required">*</span></span>
            <textarea class="joms-textarea" name="description" data-wysiwyg="trumbowyg" data-wysiwyg-type="page" data-wysiwyg-id="<?php echo $isNew ? 0 : $page->id ?>"><?php echo $this->escape($page->description); ?></textarea>
        </div>
        
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY'); ?> <span class="joms-required">*</span></span>
            <?php echo $lists['categoryid']; ?>
        </div>

        <?php if ($config->get('enablephotos') && $config->get('pagephotos')) { ?>
        <?php $photoAllowed = $params->get('photopermission', 1) >= 1; ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_PHOTO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-photo-flag" name="photopermission-admin" value="1"<?php echo $photoAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_UPLOAD_ALOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-photo-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="photopermission-member" value="1"<?php echo $photoAllowed ? '' : ' disabled="disabled"'; ?><?php echo $photoAllowed && ( $params->get('photopermission') == PAGE_PHOTO_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_UPLOAD_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="pagerecentphotos" title="<?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_PHOTOS_TIPS'); ?>">
                    <?php for($i = 2; $i <= 10; $i = $i+2){ ?>
                    <option value="<?php echo $i; ?>" <?php echo ($page->pagerecentphotos == $i || ($i == 6 && $page->pagerecentphotos == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <?php if ($config->get('enablevideos') && $config->get('groupvideos')) { ?>
        <?php $videoAllowed = $params->get('videopermission', 1) >= 1; ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_VIDEO'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-video-flag" name="videopermission-admin" value="1"<?php echo $videoAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_VIDEOS_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_PAGES_VIDEO_UPLOAD_ALLOW_ADMIN'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-video-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="videopermission-member" value="1"<?php echo $videoAllowed ? '' : ' disabled="disabled"'; ?><?php echo $videoAllowed && ( $params->get('videopermission') == PAGE_VIDEO_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_VIDEO_UPLOAD_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_VIDEO_UPLOAD_ALLOW_MEMBER'); ?></span>
                </label>
                <select type="text" class="joms-select" name="pagerecentvideos" title="<?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_VIDEO_TIPS'); ?>">
                    <?php for ($i = 2; $i <= 10; $i = $i+2) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($page->pagerecentvideos == $i || ($i == 6 && $page->pagerecentvideos == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <?php if ($config->get('enableevents') && $config->get('page_events')) { ?>
        <?php $eventAllowed = $params->get('eventpermission', 1) >= 1; ?>
        <?php if (!isset($page->pagerecentevents)) $page->pagerecentevents = PAGE_EVENT_RECENT_LIMIT; ?>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PAGE_EVENTS'); ?></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox joms-js--group-event-flag" name="eventpermission-admin" value="1"<?php echo $eventAllowed ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_PERMISSIONS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_ADMIN_CREATION'); ?></span>
                </label>
            </div>
            <div class="joms-js--group-event-setting" style="display:none">
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="eventpermission-member" value="1"<?php echo $eventAllowed ? '' : ' disabled="disabled"'; ?><?php echo $eventAllowed && ( $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                    <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_EVENTS_MEMBERS_CREATION_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGE_EVENTS_MEMBERS_CREATION'); ?></span>
                </label>
                <select type="text" class="joms-select" name="pagerecentevents" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENT_TIPS'); ?>">
                    <?php for ($i = 2; $i <= 10; $i = $i+2) { ?>
                        <option value="<?php echo $i; ?>" <?php echo ($page->pagerecentevents == $i || ($i == 6 && $page->pagerecentevents == 0)) ? 'selected': ''; ?>><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <?php } ?>

        <?php if ($config->get('file_sharing_page')) { ?>
            <?php $filesharingAllowed = $params->get('filesharingpermission') >= 1; ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_FILESHARING'); ?></span>
                <div>
                    <label class="joms-checkbox">
                        <input type="checkbox" class="joms-checkbox joms-js--group-filesharing-flag" name="filesharingpermission-admin" value="1"<?php echo $filesharingAllowed ? ' checked="checked"' : ''; ?>>
                        <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_ALOW_ADMIN'); ?></span>
                    </label>
                </div>
                <div class="joms-js--group-filesharing-setting" style="display:none">
                    <label class="joms-checkbox">
                        <input type="checkbox" class="joms-checkbox" name="filesharingpermission-member" value="1"<?php echo $filesharingAllowed ? '' : ' disabled="disabled"'; ?><?php echo $filesharingAllowed && ( $params->get('filesharingpermission') == PAGE_FILESHARING_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                        <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_ALLOW_MEMBER'); ?></span>
                    </label>
                </div>
            </div>
        <?php } ?>
        
        <?php if ($config->get('page_polls')) { ?>
            <?php $pollsAllowed = $params->get('pollspermission') >= 1; ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_POLLS'); ?></span>
                <div>
                    <label class="joms-checkbox">
                        <input type="checkbox" class="joms-checkbox joms-js--group-polls-flag" name="pollspermission-admin" value="1"<?php echo $pollsAllowed ? ' checked="checked"' : ''; ?>>
                        <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_PERMISSION_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_ALOW_ADMIN'); ?></span>
                    </label>
                </div>
                <div class="joms-js--group-polls-setting" style="display:none">
                    <label class="joms-checkbox">
                        <input type="checkbox" class="joms-checkbox" name="pollspermission-member" value="1"<?php echo $pollsAllowed ? '' : ' disabled="disabled"'; ?><?php echo $pollsAllowed && ( $params->get('pollspermission') == PAGE_POLLS_PERMISSION_ALL ) ? ' checked="checked"' : ''; ?>>
                        <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_ALLOW_MEMBER_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_ALLOW_MEMBER'); ?></span>
                    </label>
                </div>
            </div>
        <?php } ?>

        <script>
            joms.onStart(function( $ ) {
                $('.joms-js--group-photo-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-photo-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-video-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-video-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-event-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-event-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-filesharing-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-filesharing-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');

                $('.joms-js--group-polls-flag').on( 'click', function() {
                    var $div = $('.joms-js--group-polls-setting'),
                        $checkbox = $div.find('input');

                    if ( this.checked ) {
                        $checkbox.removeAttr('disabled');
                        $div.show();
                    } else {
                        $checkbox[0].checked = false;
                        $checkbox.attr('disabled', 'disabled');
                        $div.hide();
                    }
                }).triggerHandler('click');
            });
        </script>
        
        <?php if ($jConfig->get('sef') && !$isNew) { ?>

            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_PAGE_URL'); ?></span>
                <?php echo JText::sprintf('COM_COMMUNITY_CURRENT_PAGE_URL', $prefixURL); ?>
            </div>

        <?php } ?>

        <?php if ($afterFormDisplay) { ?>
        <div class="joms-form__group"><?php echo $afterFormDisplay; ?></div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <div>
                <?php if ($isNew) { ?>
                <input name="action" type="hidden" value="save">
                <?php } ?>
                <input type="hidden" name="pageid" value="<?php echo $page->id; ?>">
                <?php echo JHTML::_('form.token'); ?>
                <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
                <input type="submit" value="<?php echo JText::_($isNew ? 'COM_COMMUNITY_PAGES_CREATE_PAGE' : 'COM_COMMUNITY_SAVE_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>
        </div>

    </form>
</div>
<script>
    function joms_validate_form() {
        return false;
    }

    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            joms_validate_form = function( $form ) {
                var errors = 0;

                $form = joms.jQuery( $form );
                $form.find('[required]').each(function() {
                    var $el = joms.jQuery( this );
                    if ( !joms.jQuery.trim( $el.val() ) ) {
                        $el.triggerHandler('blur');
                        errors++;
                    }
                });

                return !errors;
            }
        });
    })( window );
</script>
