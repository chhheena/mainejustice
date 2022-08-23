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

<script>

    function joms_change_filter(value) {
        window.location = value;
    }

</script>

<?php
    $task = JFactory::getApplication()->input->getCmd('task');
    $isPage = isset($pageId) && $pageId;
    $isGroup = isset($groupId) && $groupId;
    $isEvent = isset($eventId) && $eventId;

    if(!isset($title)) {
        if ($task == 'mypolls') {
            $title = JText::_('COM_COMMUNITY_POLLS_MY_POLLS');
        } else if ($pageId) {
            $title = JText::_('COM_COMMUNITY_PAGE_POLLS');
        } else if ($groupId) {
            $title = JText::_('COM_COMMUNITY_GROUP_POLLS');
        } else if ($eventId) {
            $title = JText::_('COM_COMMUNITY_EVENT_POLLS');
        } else {
            $title = JText::_('COM_COMMUNITY_POLLS');
        }
    }

    $addPollsButton = false;
    if ($task == 'mypolls' && $my->id == $user->id) {
        if ($my->authorise('community.create', 'polls')) $addPollsButton = true;
    } else if ($pageId) {
        $addPollsButton = $my->authorise('community.create', 'pages.polls.' . $pageId);
    } else if ($groupId) {
        $addPollsButton = $my->authorise('community.create', 'groups.polls.' . $groupId);
    } else if ($eventId) {
        $addPollsButton = $my->authorise('community.create', 'events.polls.' . $eventId);
    }
 ?>

<div class="joms-page">
    <div class="joms-list__search">
        <div class="joms-list__search-title">
            <h3 class="joms-page__title"><?php echo $title; ?></h3>
        </div>

        <div class="joms-list__utilities">
            <?php
            if(CPollsAccess::pollsSearchView()) {
                ?>
                <form method="GET" class="joms-inline--desktop"
                      action="<?php echo CRoute::_('index.php?option=com_community&view=polls&task=search'); ?>">
                <span>
                    <input type="text" class="joms-input--search" name="search"
                           placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_POLL_PLACEHOLDER'); ?>">
                </span>
                    <?php echo JHTML::_('form.token') ?>
                    <span>
                    <button class="joms-button--neutral"><?php echo JText::_('COM_COMMUNITY_SEARCH_GO'); ?></button>
                </span>
                    <input type="hidden" name="option" value="com_community"/>
                    <input type="hidden" name="view" value="polls"/>
                    <input type="hidden" name="task" value="search"/>
                    <input type="hidden" name="Itemid" value="<?php echo CRoute::getItemId(); ?>"/>
                </form>
            <?php
            }

            if ($addPollsButton) { ?>
                <button onclick="window.location='<?php echo $createLink; ?>';" class="joms-button--add">
                    <?php 
                        if (isset($pageId) && $pageId) echo JText::_('COM_COMMUNITY_CREATE_PAGE_POLL');
                        else if (isset($groupId) && $groupId) echo JText::_('COM_COMMUNITY_CREATE_GROUP_POLL');
                        else if (isset($eventId) && $eventId) echo JText::_('COM_COMMUNITY_CREATE_EVENT_POLL');
                        else echo JText::_('COM_COMMUNITY_POLLS_CREATE_POLL'); 
                    ?>
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-plus"></use>
                    </svg>
                </button>
            <?php } ?>
        </div>
    </div>

    <?php if ($submenu) { ?>
        <?php echo $submenu;?>
        <div class="joms-gap"></div>
    <?php } ?>

    <!-- sorting -->
    <?php if ($task == 'display' || $task == 'mypolls' || $task == '' || $task == 'pagepolls' || $task == 'grouppolls' || $task == 'eventpolls') { ?>
        <div class="joms-sortings">
            <?php if ($task != 'search') {
                echo $sortings;
            } ?>

            <?php if (($task == 'display' || $task == '' || $task == 'pagepolls' || $task == 'grouppolls' || $task == 'eventpolls') && !$isPage && !$isGroup && !$isEvent) { ?>
                <select class="joms-select" onchange="joms_change_filter(this.value);">
                    <option
                        value="<?php echo CRoute::_($allPollsUrl); ?>"><?php echo JText::_('COM_COMMUNITY_ALL_CATEGORIES'); ?></option>
                    <?php if ($category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT) { ?>

                    <?php
                    } else {
                        $catid = '';
                        if ($category->parent != 0) {
                            $catid = '&catid=' . $category->parent;
                        }
                        ?>
                    <?php } ?>

                    <?php if ($categories) { ?>
                        <?php foreach ($categories as $row): ?>
                            <option <?php if ($categoryId == $row->id){ ?>selected="selected" <?php } ?>
                                    value="<?php echo CRoute::_($catPollUrl . $row->id); ?>"><?php echo JText::_($this->escape(trim($row->name))); ?></option>
                        <?php endforeach; ?>
                    <?php } ?>
                </select>
            <?php } ?>
        </div>

        <div class="joms-gap"></div>

    <?php } else { ?>
        <?php if ($task != 'search') {
            echo $sortings;
        } ?>
    <?php } ?>

    <?php echo $pollsHTML;?>
</div>
