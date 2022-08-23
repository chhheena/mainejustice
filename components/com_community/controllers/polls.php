<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityPollsController extends CommunityBaseController 
{   
    /**
     * Call the View object to compose the resulting HTML display
     *
     * @param string View function to be called
     * @param mixed extra data to be passed to the View
     */
    public function renderView($viewfunc, $var = NULL) {

        $my = CFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get($viewfunc, $var);
    }

    /**
     * Displays the default polls view
     * */
    public function display($cacheable = false, $urlparams = false)
    {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        
        if (!$my->authorise('community.view', 'polls.list')) {
            echo JText::_('COM_COMMUNITY_POLLS_DISABLE');
            return;
        }
        
        $this->renderView(__FUNCTION__);
    }

    public function edit()
    {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $jinput = JFactory::getApplication()->input;
        $viewName = $jinput->get('view', $this->getName());
        $config = CFactory::getConfig();

        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $pollId = $jinput->get('pollid', '', 'INT');
        $model = $this->getModel('polls');
        $my = CFactory::getUser();
        $validated = true;
        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load($pollId);

        if (empty($poll->id)) {
            echo CSystemHelper::showErrorPage();
            return;
        }

        if (!$my->authorise('community.edit', 'polls.' . $pollId, $poll)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $data = $jinput->post->getArray();

            $poll->bind($data);

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-polls-forms'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $redirect = CRoute::_('index.php?option=com_community&view=polls&task=edit&pollid=' . $pollId, false);

                $title = $jinput->post->get('title', '', 'STRING');
                $catid = $jinput->post->get('catid', '', 'INT');
                $enddate = $jinput->post->get('enddate', '', 'STRING');
                $permissions = $jinput->post->get('permissions', '', 'INT');
                $multiple = $jinput->post->get('multiple', 0, 'INT');
                $pollitems = $jinput->post->get('pollItem', '', 'STRING');
                $pollitemIds = $jinput->post->get('pollItemId', '', 'STRING');
                
                if (empty($title)) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_EMPTY_NAME_ERROR'), 'error');
                }
                
                if (empty($catid)) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_CATEGORY_NOT_SELECTED'), 'error');
                }

                if (empty($enddate)) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_ENDDATE_ERROR'), 'error');
                }

                if (empty($pollitems)) {
                    $validated = false;
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_ITEMS_ERROR'), 'error');
                }

                // end datetime format
                $data = $this->_formatStartEndDate($data);
                $poll->enddate = $data['enddate'];
                $poll->multiple = $multiple;

                if ($validated) {
                    if ($poll->store()) {
                        foreach ($pollitems as $key => $item) {
                            $pollitem = JTable::getInstance('PollItem', 'CTable');
                            
                            if (isset($pollitemIds[$key])) {
                                $pollitem->load($pollitemIds[$key]);
                            } else {
                                $pollitem->poll_id = $poll->id;
                                $pollitem->count = 0;
                            }

                            $pollitem->value = $item;
                            $pollitem->store();
                        }

                        $db = JFactory::getDBO();
                        $query = 'SELECT * FROM ' . $db->quoteName('#__community_activities') . ' '
                            . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote('polls') . ' '
                            . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($poll->id);
                        
                        $db->setQuery($query);
                        $activityItem = $db->loadObject();

                        if ($activityItem) {
                            $activity = JTable::getInstance('activity', 'CTable');
                            $activity->load($activityItem->id);

                            $activity->title = $title;
                            $activity->access = $permissions;
                            
                            $activity->store();
                        }
                    }

                    // Reupdate the display.
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_UPDATED'));

                    if ($poll->groupid) {
                        $url = CRoute::_('index.php?option=com_community&view=polls&groupid=' . $poll->groupid, false);
                    } else if ($poll->eventid) {
                        $url = CRoute::_('index.php?option=com_community&view=polls&eventid=' . $poll->eventid, false);
                    } else {
                        $url = CRoute::_('index.php?option=com_community&view=polls', false);
                    }

                    $mainframe->redirect($url);

                    return;
                }
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_POLLS_CAT, COMMUNITY_CACHE_TAG_POLLS));
        echo $view->get(__FUNCTION__);
    }

    /**
     * Method to display the create poll form
     * */
    public function create() 
    {   
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        $pageId = $jinput->request->get('pageid', 0, 'Int');
        $groupId = $jinput->request->get('groupid', 0, 'Int');
        $eventId = $jinput->request->get('eventid', 0, 'Int');

        $isBanned = false;
        
        if ($pageId) {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageId);

            $isBanned = $page->isBanned($my->id);
        } else if ($groupId) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            $isBanned = $group->isBanned($my->id);
        }

        if ($my->authorise('community.add', 'polls') && !$isBanned) {
            $model = CFactory::getModel('Polls');
            if (CLimitsLibrary::exceedDaily('polls')) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_LIMIT_REACHED'), 'error');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=polls', false));
            }

            $model = $this->getModel('polls');
            $data = new stdClass();
            $data->categories = $model->getCategories();

            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-polls-forms'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $pollid = $this->save();

                    if ($pollid !== FALSE) {
                        $mainframe = JFactory::getApplication();

                        $poll = JTable::getInstance('Poll', 'CTable');
                        $poll->load($pollid);

                        if ($config->get('moderatepollcreation')) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_POLLS_MODERATION_MSG', $poll->title), 'message');
                            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=polls', false));
                            return;
                        }
                        
                        if ($pageId) {
                            $url = CRoute::_('index.php?option=com_community&view=polls&pageid=' . $pageId, false);
                        } else if ($groupId) {
                            $url = CRoute::_('index.php?option=com_community&view=polls&groupid=' . $groupId, false);
                        } else if ($eventId) {
                            $url = CRoute::_('index.php?option=com_community&view=polls&eventid=' . $eventId, false);
                        } else {
                            $url = CRoute::_('index.php?option=com_community&view=polls', false);
                        }
                        
                        $mainframe->redirect($url);
                        return;
                    }
                }
            }
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                CSystemHelper::showErrorPage();
            }
            return;
        }

        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT));

        $this->renderView(__FUNCTION__, $data);
    }

    public function save()
    {   
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (CStringHelper::strtoupper($jinput->getMethod()) != 'POST') {
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $viewName = $jinput->get('view', $this->getName());
            $view = $this->getView($viewName, '', $viewType);
            $view->addWarning(JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
            return false;
        }

        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $validated = true;
        $poll = JTable::getInstance('Poll', 'CTable');
        $model = $this->getModel('polls');

        $title = $jinput->post->get('title', '', 'STRING');
        $catid = $jinput->post->get('catid', '', 'INT');
        $enddate = $jinput->post->get('enddate', '', 'STRING');
        $permissions = $jinput->post->get('permissions', '', 'INT');
        $multiple = $jinput->post->get('multiple', '', 'INT');
        $pollitems = $jinput->post->get('pollItem', '', 'STRING');
        
        if (empty($title)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_EMPTY_NAME_ERROR'), 'error');
        }

        if (empty($catid)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_CATEGORY_NOT_SELECTED'), 'error');
        }

        if (empty($enddate)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_ENDDATE_ERROR'), 'error');
        }
        
        $endHourType = $jinput->get('endtime-ampm');
        $endHour = $endHourType === 'AM' ? $jinput->getInt('endtime-hour', 0) : $jinput->getInt('endtime-hour', 0) + 12;
        $endMinute = str_pad($jinput->getInt('endtime-min'), 2, '0', STR_PAD_RIGHT);
        $end = "$enddate $endHour:$endMinute:00";
        $offset = JFactory::getConfig()->get('offset');
        $zone = new DateTimeZone($offset);
        $endUTC = JFactory::getDate($end, $zone)->toUnix();

        if ($endUTC <= time()) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_END_DATETIME_SHOULD_NOT_IN_THE_PAST'), 'error');
        }

        if (empty($pollitems)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_POLLS_ITEMS_ERROR'), 'error');
        }

        if ($validated) {
            $now = new JDate();

            // Bind the post with the table first
            $poll->title = $title;
            $poll->catid = $catid;
            $poll->creator = $my->id;

            $poll->pageid = $jinput->request->get('pageid', 0, 'Int');
            $poll->groupid = $jinput->request->get('groupid', 0, 'Int');
            $poll->eventid = $jinput->request->get('eventid', 0, 'Int');

            if ($poll->pageid) {
                $poll->permissions = 0;
            } else if ($poll->groupid) {
                $poll->permissions = 0;
            } else if ($poll->eventid) {
                $poll->permissions = 0;
            } else {
                $poll->permissions = $permissions;
            }
            
            $poll->multiple = $multiple;
            $poll->created = $now->toSql();
            $poll->published = ($config->get('moderatepollcreation') ? 0 : 1);

            // end datetime format
            $postData = $jinput->post->getArray();
            $postData = $this->_formatStartEndDate($postData);
            $poll->enddate = $postData['enddate'];

            if ($poll->store()) {
                foreach ($pollitems as $item) {
                    $pollitem = JTable::getInstance('PollItem', 'CTable');
                    $pollitem->poll_id = $poll->id;
                    $pollitem->value = $item;
                    $pollitem->count = 0;

                    $pollitem->store();
                }
                
                $act = new stdClass();
                $act->access = $poll->permissions;
                $act->cmd = 'poll.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = $poll->title;
                $act->content = '';
                $act->app = 'polls';
                $act->cid = $poll->id;

                if ($poll->pageid) {
                    $page = JTable::getInstance('Page', 'CTable');
                    $page->load($poll->pageid);

                    $act->access = 0;
                    $act->pageid = $page->id;
                    $act->page_access = $page->approvals;
                } else if ($poll->groupid) {
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($poll->groupid);

                    $act->access = 0;
                    $act->groupid = $group->id;
                    $act->group_access = $group->approvals;
                } else if ($poll->eventid) {
                    $event = JTable::getInstance('Event', 'CTable');
                    $event->load($poll->eventid);

                    $act->access = 0;
                    $act->eventid = $event->id;
                    $act->group_access = $event->permission;
                }

                // Allow comments
                $act->comment_type = 'polls.create';
                $act->like_type = 'polls.create';
                $act->comment_id = CActivities::COMMENT_SELF;
                $act->like_id = CActivities::LIKE_SELF;

                $params = new CParameter('');
                $params->set('action', 'poll.create');
                $params->set('poll_url', 'index.php?option=com_community&view=polls');
                $params->set('category_url', 'index.php?option=com_community&view=polls&categoryid=' . $poll->catid);

                // Add activity logging
                CActivityStream::add($act, $params->toString());

                if ($config->get('moderatepollcreation')) {
                    $db = JFactory::getDbo();
                    $query = "UPDATE ".$db->quoteName('#__community_activities')
                        ." SET ".$db->quoteName('archived')."=".$db->quote(1)
                        ." WHERE ".$db->quoteName('cid')."=".$db->quote($poll->id)
                        ." AND ".$db->quoteName('app')."=".$db->quote('polls');

                    $db->setQuery($query);
                    $db->execute();
                }
            }

            // if need approval should send email notification to admin
            if ($config->get('moderatepollcreation')) {
                $title_email = JText::_('COM_COMMUNITY_EMAIL_NEW_POLL_NEED_APPROVAL_TITLE');
                $message_email = JText::sprintf('COM_COMMUNITY_EMAIL_NEW_POLL_NEED_APPROVAL_MESSAGE', $my->getDisplayName(), $poll->title);
                $from = $mainframe->get('mailfrom');
                $to = $config->get('notifyMaxReport');
                CNotificationLibrary::add('polls_create', $from, $to, $title_email, $message_email, '', '');
            }

            //add user points
            CUserPoints::assignPoint('poll.create');

            $validated = $poll->id;
        }

        return $validated;
    }

    public function ajaxCreate($postData, $title, $objResponse)
    {
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $postData = $filter->clean($postData, 'array');

        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!JSession::checkToken('post')) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $objResponse->sendResponse();
        }

        //check for user daily limit first, then check for the total limit
        if (CFactory::getConfig()->get("limit_polls_perday") <= CFactory::getModel("polls")->getTotalToday($my->id)) {
            $pollLimit = CFactory::getConfig()->get("limit_polls_perday");
            $objResponse->addScriptCall(
                '__throwError',
                JText::sprintf('COM_COMMUNITY_POLLS_DAILY_LIMIT', $pollLimit)
            );
            $objResponse->sendResponse();
        } else {
            if (CLimitsHelper::exceededPollCreation($my->id)) {
                $pollLimit = $config->get('pollcreatelimit');
                $objResponse->addScriptCall('__throwError', JText::sprintf('COM_COMMUNITY_POLLS_LIMIT', $pollLimit));
                $objResponse->sendResponse();
            }
        }

        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load();

        if (!$my->authorise('community.add', 'polls')) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
            $objResponse->sendResponse();
        }
        
        $postData['enddate'] = $postData['polltime']['enddate'][0] . ' ' . $postData['polltime']['endtime'][0] . ':00';

        if ($postData['element'] == 'pages') {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($postData['target']);

            $postData['privacy'] = 0;
        } else if ($postData['element'] == 'groups') {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($postData['target']);

            $postData['privacy'] = 0;
        } else if ($postData['element'] == 'events') {
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($postData['target']);

            $postData['privacy'] = 0;
        }

        $now = new JDate();

        // Bind the post with the table first
        $poll->title = $title;
        $poll->catid = $postData['catid'];
        $poll->creator = $my->id;
        $poll->permissions = $postData['privacy'];
        $poll->multiple = $postData['settings']['allow_multiple'] * 1;
        $poll->created = $now->toSql();
        $poll->published = ($config->get('moderatepollcreation') ? 0 : 1);
        $poll->enddate = $postData['enddate'];
        
        if ($postData['element'] == 'pages') {
            $poll->pageid = $postData['target'];
        } else {
            $poll->pageid = 0;
        }

        if ($postData['element'] == 'groups') {
            $poll->groupid = $postData['target'];
        } else {
            $poll->groupid = 0;
        }

        if ($postData['element'] == 'events') {
            $poll->eventid = $postData['target'];
        } else {
            $poll->eventid = 0;
        }

        if ($poll->store()) {
            foreach ($postData['options'] as $item) {
                $pollitem = JTable::getInstance('PollItem', 'CTable');
                $pollitem->poll_id = $poll->id;
                $pollitem->value = $item;
                $pollitem->count = 0;

                $pollitem->store();
            }
            
            $act = new stdClass();
            $act->access = $poll->permissions;
            $act->cmd = 'poll.create';
            $act->actor = $my->id;
            $act->target = 0;
            $act->title = $poll->title;
            $act->content = '';
            $act->app = 'polls';
            $act->cid = $poll->id;
            
            if ($postData['element'] == 'pages') {
                $act->pageid = $postData['target'];
            } else {
                $act->pageid = 0;
            }

            if ($postData['element'] == 'groups') {
                $act->groupid = $postData['target'];
            } else {
                $act->groupid = 0;
            }

            if ($postData['element'] == 'events') {
                $act->eventid = $postData['target'];
            } else {
                $act->eventid = 0;
            }

            // Allow comments
            $act->comment_type = 'polls.create';
            $act->like_type = 'polls.create';
            $act->comment_id = CActivities::COMMENT_SELF;
            $act->like_id = CActivities::LIKE_SELF;

            $params = new CParameter('');
            $params->set('action', 'poll.create');
            $params->set('poll_url', 'index.php?option=com_community&view=polls');
            $params->set('category_url', 'index.php?option=com_community&view=polls&categoryid=' . $poll->catid);

            // Add activity logging
            CActivityStream::add($act, $params->toString());

            if ($config->get('moderatepollcreation')) {
                $db = JFactory::getDbo();
                $query = "UPDATE ".$db->quoteName('#__community_activities')
                    ." SET ".$db->quoteName('archived')."=".$db->quote(1)
                    ." WHERE ".$db->quoteName('cid')."=".$db->quote($poll->id)
                    ." AND ".$db->quoteName('app')."=".$db->quote('polls');

                $db->setQuery($query);
                $db->execute();
            }
        }

        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT));

        return $poll;
    }

    public function created() {
        $this->renderView(__FUNCTION__);
    }

    public function mypolls() {
        $jinput = JFactory::getApplication()->input;
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'polls.my')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $userid = $jinput->getInt('userid',$my->id);
        $this->renderView(__FUNCTION__, $userid);
    }

    public function search() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        if (!$my->authorise('community.view', 'polls.search')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    /*
     * polls event name
     * object array
     */
    public function triggerPollEvents($eventName, &$args, $target = null)
    {
        CError::assert($args, 'object', 'istype', __FILE__, __LINE__);

        require_once( COMMUNITY_COM_PATH . '/libraries/apps.php' );
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params = array();
        $params[] = $args;

        if (!is_null($target))
            $params[] = $target;

        $appsLib->triggerEvent($eventName, $params);
        return true;
    }

    private function _formatStartEndDate($postData)
    {
        if (isset($postData['endtime-ampm']) && $postData['endtime-ampm'] == 'PM' && $postData['endtime-hour'] != 12) {
            $postData['endtime-hour'] = $postData['endtime-hour'] + 12;
        }

        if (isset($postData['endtime-ampm']) && $postData['endtime-ampm'] == 'AM' && $postData['endtime-hour'] == 12) {
            $postData['endtime-hour'] = 0;
        }

        $postData['enddate'] = $postData['enddate'] . ' ' . $postData['endtime-hour'] . ':' . $postData['endtime-min'] . ':00';

        unset($postData['enddatetime']);
        unset($postData['endtime-hour']);
        unset($postData['endtime-min']);
        unset($postData['endtime-ampm']);

        return $postData;
    }

    public function ajaxConfirmDeletePollOption($app, $pollItemId) {
        $filter = JFilterInput::getInstance();
        $pollItemId = $filter->clean($pollItemId, 'int');
        $pollitem = JTable::getInstance('Pollitem', 'CTable');
        $pollitem->load($pollItemId);
        
        $json = array(
            'title'     => JText::_('COM_COMMUNITY_POLL_ITEM_REMOVE'),
            'message'   => JText::sprintf('COM_COMMUNITY_POLL_ITEM_REMOVE_MESSAGE', $pollitem->value),
            'btnYes'    => JText::_('COM_COMMUNITY_YES'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxDeletePollOption($app, $pollItemId) {
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $pollItemId = $filter->clean($pollItemId, 'int');

        $pollitem = JTable::getInstance('Pollitem', 'CTable');
        $pollitem->load($pollItemId);

        $json = array();

        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load($pollitem->poll_id);

        if ($my->authorise('community.edit', 'polls.' . $poll->id, $poll)) {
            $pollitem->delete();

            $json = array( 'success' => true );
        } else {
            $json = array( 'error' => true );
        }

        die( json_encode($json) );
    }

    public function ajaxWarnPollDeletion($app, $pollId)
    {   
        $filter = JFilterInput::getInstance();
        $pollId = $filter->clean($pollId, 'int');

        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load($pollId);
        
        $json = array(
            'title'     => JText::_('COM_COMMUNITY_POLLS_DELETE_POLL'),
            'message'   => JText::sprintf('COM_COMMUNITY_POLLS_DELETE_MESSAGE', $poll->title),
            'btnYes'    => JText::_('COM_COMMUNITY_YES'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxDeletePoll($app, $pollId)
    {   
        $my = CFactory::getUser();
        $db = JFactory::getDbo();
        $filter = JFilterInput::getInstance();
        $pollId = $filter->clean($pollId, 'int');

        $json = array();
        $response = new JAXResponse();

        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load($pollId);
        
        if (!$my->authorise('community.delete', 'polls.' . $pollId, $poll)) {
            $json['error'] = JText::_('COM_COMMUNITY_POLLS_NOT_ALLOWED_DELETE');
            die( json_encode($json) );
        }

        if ($poll->delete()) {
            $query = "DELETE FROM ".$db->quoteName('#__community_polls_items')
                ." WHERE ".$db->quoteName('poll_id')."=".$db->quote($pollId);
            $db->setQuery($query);
            $db->execute();

            $query = "DELETE FROM ".$db->quoteName('#__community_polls_users')
                ." WHERE ".$db->quoteName('poll_id')."=".$db->quote($pollId);
            $db->setQuery($query);
            $db->execute();

            $query = "DELETE FROM ".$db->quoteName('#__community_activities')
                ." WHERE ".$db->quoteName('cid')."=".$db->quote($pollId)
                ." AND ".$db->quoteName('app')."=".$db->quote('polls');
            $db->setQuery($query);
            $db->execute();
            
            $content = JText::_('COM_COMMUNITY_POLLS_DELETED');
            $json['success'] = 1;
        } else {
            $content = JText::_('COM_COMMUNITY_POLLS_DELETE_ERROR');
            $json['error'] = 1;
        }

        $redirect = CRoute::_('index.php?option=com_community&view=polls');

        $json['message'] = $content;
        $json['redirect'] = $redirect;
        $json['btnDone'] = JText::_('COM_COMMUNITY_DONE_BUTTON');

        //Clear Cache for polls
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_POLLS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_POLLS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );
    }

    public function ajaxPollVote( $poll_id, $option_id, $collapsed, $type) {
        $db = JFactory::getDbo();
        $my = CFactory::getUser();

        if ($my->id == 0) {
            exit;
        }

        $filter = JFilterInput::getInstance();
        $poll_id = $filter->clean($poll_id, 'int');
        $option_id = $filter->clean($option_id, 'int');
        $collapsed = $filter->clean($collapsed, 'int');

        $poll = JTable::getInstance('Poll', 'CTable');
        $poll->load($poll_id);
        $poll->expired = $poll->isExpired();
        
        if (!$poll->expired) {
            $_isVoted = $this->_isVoted($poll_id, $option_id);

            if ($_isVoted) {
                $this->_unvote($poll_id, $option_id);
            } 
            else if($poll->multiple) {
                $this->_vote($poll_id, $option_id);
            }
            else {
                $this->_vote($poll_id, $option_id);

                $without = true;
                $this->_unvote($poll_id, $option_id, $without);
            }

            // update vote counter for poll item
            $pollItem = JTable::getInstance('Pollitem', 'CTable');
            $pollItem->load($option_id);
            $pollItem->updateVoteCounter($poll->id);
        }

        $poll->collapsed = $collapsed;

        $pollHTML = $this->_renderPollContainer( $poll, $type );

        $json = array();
        $json['success'] = true;
        $json['html'] = $pollHTML;

        die(json_encode($json));
    }

    private function _renderPollContainer( $poll, $type ) {
        $type = explode( '.', $type);
        $html = array();

        if (in_array('stream', $type)) {
            $tmpl = new CTemplate();
            $html['stream'] = $tmpl->set('poll', $poll)->fetch('stream/poll-container');
        }
        
        if (in_array('module', $type)) {
            ob_start();
            include JModuleHelper::getLayoutPath('mod_community_polls', 'default_item');
            $html['module'] = ob_get_clean();
        }

        return $html;
    }

    private function _vote($poll_id, $option_id) {
        $my = CFactory::getUser();
        $pollUser = JTable::getInstance('Polluser', 'CTable');
        $pollUser->poll_id = $poll_id;
        $pollUser->poll_itemid = $option_id;
        $pollUser->user_id = $my->id;
        $pollUser->state = 1;
        $pollUser->store();
    }

    private function _unvote($poll_id, $option_id, $without = false) {
        $my = CFactory::getUser();
        $db = JFactory::getDbo();
        $query = 'DELETE FROM ' . $db->quoteName('#__community_polls_users')
                . ' WHERE ' . $db->quoteName('poll_id') . '=' . $db->Quote($poll_id);

        if ($without) {
            $query .= ' AND ' . $db->quoteName('poll_itemid') . '!=' . $db->Quote($option_id);
        } else {
            $query .= ' AND ' . $db->quoteName('poll_itemid') . '=' . $db->Quote($option_id);
        }
        
        $query .= ' AND ' . $db->quoteName('user_id') . '=' . $db->Quote($my->id);

        $db->setQuery($query);
        $db->execute(); 
    }

    private function _isVoted($poll_id, $option_id)
    {   
        $db = JFactory::getDbo();
        $my = CFactory::getUser();

        $query = 'SELECT COUNT(id) FROM ' . $db->quoteName('#__community_polls_users')
                . ' WHERE ' . $db->quoteName('poll_id') . '=' . $db->Quote($poll_id)
                . ' AND ' . $db->quoteName('poll_itemid') . '=' . $db->Quote($option_id)
                . ' AND ' . $db->quoteName('user_id') . '=' . $db->Quote($my->id);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (bool) $result;      
    }

    public function ajaxShowVotedUsers($poll_id, $option_id) {
        $filter = JFilterInput::getInstance();
        $poll_id = $filter->clean($poll_id, 'int');
        $option_id = $filter->clean($option_id, 'int');

        $objResponse = new JAXResponse();

        $json = array(
            'success' => true,
            'title' => JText::_('COM_COMMUNITY_POLLS_VOTED_PEOPLE'),
            'html' => $this->_getVotedUsers($objResponse, $poll_id, $option_id)
        );

        die( json_encode( $json ) );
    }

    private function _getVotedUsers($objResponse, $poll_id, $option_id) {
        $db = JFactory::getDbo();
        $my = JFactory::getUser();

        $query = 'SELECT user_id FROM ' . $db->quoteName('#__community_polls_users')
                . ' WHERE ' . $db->quoteName('poll_id') . '=' . $db->Quote($poll_id)
                . ' AND ' . $db->quoteName('poll_itemid') . '=' . $db->Quote($option_id);

        $db->setQuery($query);
        $result = $db->loadColumn();

        $votedHTML = '';
        $users = array();

        foreach ($result as $id) {
            $user = CFactory::getUser($id);
            $users[] = $user;
        }

        if (count($users)) {
            $tmpl = new CTemplate();
            $tmpl->set('users', $users);
            $votedHTML = $tmpl->fetch('ajax.stream.showothers');
        }

        return $votedHTML;
    }
}