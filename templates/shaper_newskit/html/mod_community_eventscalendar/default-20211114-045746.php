<?php
    /**
     * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    defined('_JEXEC') or die('Unauthorized Access');

    $config = CFactory::getConfig();
    $showAmpm = $config->get('eventshowampm');

?>

<div class="joms-module">

    <?php // if($user->isOnline()):?>
    <?php // else:?>
    <!-- If not logged in -->
    <?php // endif;?>

    <div class="joms-module--eventscalendar">
        <div id="calendar"></div>
    </div>

</div>

<script>

    // load library
    joms.onStart(function ($) {
        var scriptUrl = '<?php echo JURI::root(true) . "/modules/mod_community_eventscalendar/calendar.js" ?>';
        joms.$LAB.script(scriptUrl).wait(function () {
            $(function () {
                joms_mod_eventscalendar_init($);
            });
        });
    });

    // initialize calender
    function joms_mod_eventscalendar_init($) {
        $('#calendar').eCalendar({
            firstDay: <?php echo $firstDay; ?>,
            weekDays: [
                '<?php echo JText::_("SUN") ?>',
                '<?php echo JText::_("MON") ?>',
                '<?php echo JText::_("TUE") ?>',
                '<?php echo JText::_("WED") ?>',
                '<?php echo JText::_("THU") ?>',
                '<?php echo JText::_("FRI") ?>',
                '<?php echo JText::_("SAT") ?>',
            ],
            months: [
                '<?php echo JText::_("JANUARY", true) ?>',
                '<?php echo JText::_("FEBRUARY", true) ?>',
                '<?php echo JText::_("MARCH", true) ?>',
                '<?php echo JText::_("APRIL", true) ?>',
                '<?php echo JText::_("MAY", true) ?>',
                '<?php echo JText::_("JUNE", true) ?>',
                '<?php echo JText::_("JULY", true) ?>',
                '<?php echo JText::_("AUGUST", true) ?>',
                '<?php echo JText::_("SEPTEMBER", true) ?>',
                '<?php echo JText::_("OCTOBER", true) ?>',
                '<?php echo JText::_("NOVEMBER", true) ?>',
                '<?php echo JText::_("DECEMBER", true) ?>'
            ],
            textArrows: {previous: '◀', next: '▶'},
            eventTitle: '<?php echo JText::_("COM_COMMUNITY_EVENTS", true) ?>',
            url: '',
            events: [
                <?php
                    foreach ($events as $event) {
                        $date = CTimeHelper::convertSQLtimetoChunk($event->startdate);
                        $datelabel = date( $dateFormat . ' ' . $timeFormat, strtotime(
                            $date['year'] . '-' .
                            $date['month'] . '-' .
                            $date['day'] . ' ' .
                            $date['hour'] . ':' .
                            $date['minute'] . ':' .
                            $date['second'])
                        );
                ?>
                {
                    title: '<?php echo str_replace("'", "\'", $event->title); ?>',
                    description: '<?php echo str_replace("'", "\'", preg_replace( "/\r?\n/", " ", $event->summary )); ?>',
                    url: '<?php echo html_entity_decode(CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id)); ?>',
                    showAmpm: +'<?php echo $showAmpm ?>',
                    datelabel: '<?php echo $datelabel ?>',
                    datetime: new Date(
                        <?php echo $date['year'];?>,
                        <?php echo intval($date['month']) - 1;?>,
                        <?php echo $date['day'];?>,
                        <?php echo $date['hour'];?>,
                        <?php echo $date['minute'];?>,
                        <?php echo $date['second'];?>

                    )
                },
                <?php } ?>
            ]
        });
    }

</script>
