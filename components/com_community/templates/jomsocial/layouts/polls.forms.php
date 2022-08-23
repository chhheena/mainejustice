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
$endDate = new JDate($poll->enddate);

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$pageid = $jinput->getInt('pageid', 0);
$groupid = $jinput->getInt('groupid', 0);
$eventid = $jinput->getInt('eventid', 0);
?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_($isNew ? 'COM_COMMUNITY_POLLS_CREATE_NEW_GROUP' : 'COM_COMMUNITY_POLLS_EDIT_TITLE'); ?></h3>
    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form(this);">

        <div class="joms-form__group">
            <?php if ($isNew) { ?>
                <?php if ($pollcreatelimit != 0 && $pollCreated / $pollcreatelimit >= COMMUNITY_SHOW_LIMIT) { ?>
                    <p><?php echo JText::sprintf('COM_COMMUNITY_POLLS_LIMIT_STATUS', $pollCreated, $pollcreatelimit); ?></p>
                <?php } ?>
            <?php } ?>
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_POLLS_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="title" required=""
                title="<?php echo JText::_('COM_COMMUNITY_POLLS_TITLE_TIPS'); ?>"
                value="<?php echo $this->escape($poll->title); ?>">
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_POLLS_CATEGORY'); ?> <span class="joms-required">*</span></span>
            <?php echo $lists['categoryid']; ?>
        </div>

        <?php if (!$pageid && !$groupid && !$eventid) { ?>
            <div class="joms-form__group">
                <span><?php echo JText::_('COM_COMMUNITY_POLLS_PRIVACY'); ?> <span class="joms-required">*</span></span>
                <?php echo CPrivacy::getHTML('permissions', $poll->permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 'select'); ?>
            </div>
        <?php } ?>

        <script>

            joms_tmp_pickadateOpts = {
                min      : true,
                format   : 'yyyy-mm-dd',
                firstDay : <?php echo $config->get('event_calendar_firstday') === 'Monday' ? 1 : 0 ?>,
                today    : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CURRENT", true) ?>',
                'clear'  : '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_CLEAR", true) ?>'
            };

            joms_tmp_pickadateOpts.weekdaysFull = [
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_1", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_2", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_3", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_4", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_5", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_6", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_DAY_7", true) ?>'
            ];

            joms_tmp_pickadateOpts.weekdaysShort = [];
            for ( i = 0; i < joms_tmp_pickadateOpts.weekdaysFull.length; i++ )
                joms_tmp_pickadateOpts.weekdaysShort[i] = joms_tmp_pickadateOpts.weekdaysFull[i].substr( 0, 3 );

            joms_tmp_pickadateOpts.monthsFull = [
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_1", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_2", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_3", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_4", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_5", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_6", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_7", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_8", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_9", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_10", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_11", true) ?>',
                '<?php echo JText::_("COM_COMMUNITY_DATEPICKER_MONTH_12", true) ?>'
            ];

            joms_tmp_pickadateOpts.monthsShort = [];
            for ( i = 0; i < joms_tmp_pickadateOpts.monthsFull.length; i++ )
                joms_tmp_pickadateOpts.monthsShort[i] = joms_tmp_pickadateOpts.monthsFull[i].substr( 0, 3 );

        </script>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EVENTS_END_TIME'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" id="enddate" name="enddate" required=""
                placeholder="<?php echo JText::_('COM_COMMUNITY_POSTBOX_EVENT_END_DATE_HINT'); ?>"
                data-value="<?php echo $endDate->format('Y-m-d'); ?>"
                style="cursor:text">
            <div id="endtime" style="margin-top:5px">
                <?php echo $endHourSelect; ?> :
                <?php echo $endMinSelect; ?>
                <?php echo $endAmPmSelect; ?>
            </div>
            <script>
                window.joms_queue || (joms_queue = []);
                joms_queue.push(function( $ ) {
                    joms_tmp_endDate = $('#enddate').pickadate( $.extend({}, joms_tmp_pickadateOpts, {
                        klass: { frame: 'picker__frame endDate' },
                        min: <?php echo $poll->id > 0 ? 'false' : 'true' ?>,
                    }) ).pickadate('picker');
                });
            </script>
        </div>

        <script>
            window.joms_queue || (joms_queue = []);
            joms_queue.push(function( $ ) {
                var $ehour = $('#endtime-hour'),
                    $emin  = $('#endtime-min'),
                    $eampm = $('#endtime-ampm'),
                    isAmpm = $sampm.length;

                // Validate time.
                $ehour.add($emin).add($eampm).change(function() {
                    var edate = new Date( $('#enddate').val() ).getTime(),
                        ehour, emin, nextDay;

                    if (!edate) {
                        return;
                    }

                    ehour = +$ehour.val();
                    emin  = +$emin.val();

                    if (isAmpm) {
                        if ($eampm.val() === 'PM') {
                            ehour += ehour < 12 ? 12 : 0;
                        } else if (ehour === 12) {
                            ehour = 0;
                        }
                    }

                    emin = smin + 15;
                    if (emin >= 60) {
                        emin = 0;
                        ehour += 1;
                        if ( ehour >= 24 ) {
                            ehour = 0;
                            nextDay = true;
                        }
                    }

                    $emin.val( emin );

                    if ( !isAmpm ) {
                        $ehour.val( ehour );
                    } else {
                        if ( ehour === 0 ) {
                            $ehour.val( 12 );
                            $eampm.val('AM');
                        } else if ( ehour < 12 ) {
                            $ehour.val( ehour );
                            $eampm.val('AM');
                        } else if ( ehour === 12 ) {
                            $ehour.val( 12 );
                            $eampm.val('PM');
                        } else {
                            $ehour.val( ehour - 12 );
                            $eampm.val('PM');
                        }
                    }

                    if ( nextDay ) {
                        edate = new Date( joms_tmp_startDate.get() );
                        edate.setDate( edate.getDate() + 1 );
                        joms_tmp_endDate.set({ select: edate }, { muted: true }, { format: 'yyyy-mm-dd' });
                    }

                });

            });
        </script>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_POLLS_OPTIONS'); ?> <span class="joms-required">*</span></span>
            <?php if ($isNew) { ?>
            <div class="joms-poll-option">
                <input 
                    type="text" 
                    class="joms-input poll-input" 
                    name="pollItem[]" 
                    value="" 
                    placeholder="<?php echo JText::_("COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION") ?>">
                <a href="javascript:;" onclick="joms.view.poll.removeOption(this)" class="joms-poll-option__remove">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
                    </svg>
                </a>
            </div>
            <div class="joms-poll-option">
                <input 
                    type="text" 
                    class="joms-input poll-input" 
                    name="pollItem[]" 
                    value="" 
                    placeholder="<?php echo JText::_("COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION") ?>">
                <a href="javascript:;" onclick="joms.view.poll.removeOption(this)" class="joms-poll-option__remove">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
                    </svg>
                </a>
            </div>
            <?php } else { ?>
                <?php 
                    $pollModel = CFactory::getModel('polls');
                    $pollItems = $pollModel->getPollItems($poll->id);

                    foreach ($pollItems as $item) {
                ?>
                    <div class="joms-poll-option joms-poll-item-<?php echo $item->id ?>">
                        <input type="hidden" class="joms-input" name="pollItemId[]" required="" value="<?php echo $item->id ?>">
                        <input 
                            type="text" 
                            class="joms-input poll-input" 
                            name="pollItem[]" 
                            value="<?php echo $item->value ?>"
                            placeholder="<?php echo JText::_("COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION") ?>">
                        <a href="javascript:;" onclick="joms.view.poll.removeOption(this)" class="joms-poll-option__remove">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
                            </svg>
                        </a>
                    </div>
                <?php } ?>
            <?php } ?>
            <a class="joms-poll-option__add" href="javascript:;" onclick="joms.view.poll.addOption(this)">+ <?php echo JText::_('COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION'); ?></a>
        </div>

        <div class="joms-form__group">
            <span></span>
            <div>
                <label class="joms-checkbox">
                    <input type="checkbox" class="joms-checkbox" name="multiple" value="1" <?php echo $poll->multiple ? ' checked="checked"' : ''; ?>>
                    <span><?php echo JText::_('COM_COMMUNITY_POLLS_MULTIPLE'); ?></span>
                </label>
            </div>
        </div>

        <div class="joms-form__group">
            <span></span>
            <div>
                <?php if ($isNew) { ?>
                    <input name="action" type="hidden" value="save">
                <?php } ?>

                <input type="hidden" name="pollid" value="<?php echo $poll->id; ?>">
                <?php echo JHTML::_('form.token'); ?>
                <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
                <input type="submit" value="<?php echo JText::_($isNew ? 'COM_COMMUNITY_POLLS_CREATE_POLL' : 'COM_COMMUNITY_SAVE_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>
        </div>

    </form>
</div>

<script type="text/template" id="joms-template-poll-option__input">
    <div class="joms-poll-option">
        <input 
            type="text" 
            class="joms-input poll-input" 
            name="pollItem[]" 
            value="" 
            placeholder="<?php echo JText::_("COM_COMMUNITY_POSTBOX_POLL_ADD_OPTION") ?>">
        <a href="javascript:;" onclick="joms.view.poll.removeOption(this)" class="joms-poll-option__remove">
            <svg viewBox="0 0 16 16" class="joms-icon">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-close"></use>
            </svg>
        </a>
    </div>
</script>

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

                $form.find('.poll-input').each( function() {
                    var $el = joms.jQuery( this );
                    if( !$el.val() ) {
                        errors++;
                        $el.css('border-color', '#ff0000')
                    } else {
                        $el.css('border-color', '')
                    }
                });

                if (errors) {
                    alert("<?php echo JText::_('COM_COMMUNITY_POLLS_ITEMS_ERROR') ?>")
                }

                return !errors;
            }
        });
    })( window );
</script>
