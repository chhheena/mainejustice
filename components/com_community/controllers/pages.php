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

/**
 *
 */
class CommunityPagesController extends CommunityBaseController {

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
    
    public function deletereview() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        

        $pageid = $jinput->get('pageid', '', 'INT');
        $reviewid = $jinput->get('reviewid', '', 'INT');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);

        $pageModel = CFactory::getModel('pages');
        $isAdmin = $pageModel->isAdmin($my->id, $page->id);
        $isSuperAdmin = COwnerHelper::isCommunityAdmin();
        
        if ($isAdmin || $isSuperAdmin) {
            $model = CFactory::getModel('Pages');
            $data = new stdClass();
            $data->categories = $model->getCategories();
            
            $reviewid = $jinput->get('reviewid', '', 'INT');
            $rating = JTable::getInstance('Rating', 'CTable');
            $rating->load($reviewid);
            
            $rating->delete();
            
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_REVIEW_DETELED'));
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id, false));
            return;
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    public function editreview() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        

        $pageid = $jinput->get('pageid', '', 'INT');
        $reviewid = $jinput->get('reviewid', '', 'INT');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);
        
        $pageModel = CFactory::getModel('pages');
        $isAdmin = $pageModel->isAdmin($my->id, $page->id);
        $isSuperAdmin = COwnerHelper::isCommunityAdmin();
        
        if ($isAdmin || $isSuperAdmin) {
            $model = CFactory::getModel('Pages');
            $data = new stdClass();
            $data->categories = $model->getCategories();
            
            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));

                $reviewid = $jinput->get('reviewid', '', 'INT');
                $rate = $jinput->get('rate', '', 'INT');
                $title = $inputFilter->clean($jinput->get('title', '', 'RAW'));
                $review = $inputFilter->clean($jinput->get('review', '', 'RAW'));

                $rating = JTable::getInstance('Rating', 'CTable');

                $rating->load($reviewid);

                $rating->rating = $rate;
                $rating->title = $title;
                $rating->review = $review;
                
                $rating->store();
                
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_REVIEW_SAVED'));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id, false));
                return;
            }
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    public function createreview() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        

        $pageid = $jinput->get('pageid', '', 'INT');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);

        if ($my->authorise('community.view', 'pages.invitelist')) {
            $model = CFactory::getModel('Pages');
            $data = new stdClass();
            $data->categories = $model->getCategories();
            
            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));

                $ratingid = $jinput->get('ratingid', '', 'INT');
                $rate = $jinput->get('rate', '', 'INT');
                $title = $inputFilter->clean($jinput->get('title', '', 'RAW'));
                $review = $inputFilter->clean($jinput->get('review', '', 'RAW'));

                $rating = JTable::getInstance('Rating', 'CTable');

                if ($ratingid) {
                    $rating->load($ratingid);
                } else {
                    $rating->type = 'pages';
                    $rating->cid = $page->id;
                    $rating->userid = $my->id;
                    $rating->params = '';
                }

                $rating->rating = $rate;
                $rating->title = $title;
                $rating->review = $review;
                
                $rating->store();
                
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_REVIEW_SAVED'));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewreviews&pageid=' . $page->id, false));
                return;
            }
        } else {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    public function banlist() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $data = new stdClass();
        $data->id = $jinput->get->get('pageid', '', 'INT');
        $this->renderView(__FUNCTION__, $data);
    }

    public function myinvites() {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'pages.invitelist')) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }
        $this->renderView(__FUNCTION__);
    }

    public function search() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        if (!$my->authorise('community.view', 'pages.search')) {
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

    public function inviteUsers($cid, $users, $emails, $message) {
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($cid);
        $content = '';
        $text = '';
        $title = JText::sprintf('COM_COMMUNITY_PAGES_JOIN_INVITATION_MESSAGE', $page->name);
        $params = '';
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'pages.invite.' . $cid, $page)) {
            return false;
        }

        $params = new CParameter('');
        $params->set('url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
        $params->set('pagename', $page->name);
        $params->set('page', $page->name);
        $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);

        if ($users) {
            foreach ($users as $id) {
                $pageInvite = JTable::getInstance('PageInvite', 'CTable');
                $pageInvite->pageid = $page->id;
                $pageInvite->userid = $id;
                $pageInvite->creator = $my->id;

                $pageInvite->store();
            }
        }
        $htmlTemplate = new CTemplate();
        $htmlTemplate->set('pagename', $page->name);
        $htmlTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id));
        $htmlTemplate->set('message', $message);

        $html = $htmlTemplate->fetch('email.pages.invite.html');

        $textTemplate = new CTemplate();
        $textTemplate->set('pagename', $page->name);
        $textTemplate->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id));
        $textTemplate->set('message', $message);
        $text = $textTemplate->fetch('email.pages.invite.text');

        return new CInvitationMail('pages_invite', $html, $text, $title, $params);
    }
    
    /**
     * Displays the default pages view
     * */
    public function display($cacheable = false, $urlparams = false) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'pages.list')) {
            echo JText::_('COM_COMMUNITY_PAGES_DISABLE');
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    public function edit() {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $jinput = JFactory::getApplication()->input;
        $viewName = $jinput->get('view', $this->getName());
        $config = CFactory::getConfig();

        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $pageid = $jinput->get('pageid', '', 'INT');
        $model = $this->getModel('pages');
        $my = CFactory::getUser();
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);

        if (empty($page->id)) {
            echo CSystemHelper::showErrorPage();
            return;
        }

        if (!$my->authorise('community.edit', 'pages.' . $pageid, $page)) {
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

            $config = CFactory::getConfig();
            $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));
            $description = $jinput->post->get('description', '', 'RAW');
            $data['description'] = $inputFilter->clean($description);

            $summary = $jinput->post->get('summary', '', 'string');
            $data['summary'] = $inputFilter->clean($summary);

            $data['unlisted'] = $jinput->post->get('unlisted', 0, 'int');
            if (!isset($data['approvals'])) {
                $data['approvals'] = 0;
            }

            $page->bind($data);

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-pages-forms'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                $redirect = CRoute::_('index.php?option=com_community&view=pages&task=edit&pageid=' . $pageid, false);

                $removeActivity = $config->get('removeactivities');

                if ($removeActivity) {
                    $activityModel = CFactory::getModel('activities');

                    $activityModel->removeActivity('pages', $page->id);
                }

                // validate all fields
                if (empty($page->name)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'),'error');
                    $mainframe->redirect($redirect);
                    return;
                }

                if ($model->pageExist($page->name, $page->id)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'),'error');
                    $mainframe->redirect($redirect);
                    return;
                }

                if (empty(strip_tags($page->description))) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'),'error');
                    $mainframe->redirect($redirect);
                    return;
                }

                if (empty($page->categoryid)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_CATEGORY_NOT_SELECTED'),'error');
                    $mainframe->redirect($redirect);
                    return;
                }

                // @rule: Retrieve params and store it back as raw string

                $params = $this->_bindParams();
                $oldParams = new CParameter($page->params);
                if ( $oldParams->get('coverPosition') ) {
                    $params->set('coverPosition', $oldParams->get('coverPosition'));
                }

                if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $description))) {
                    $linkFetchcontent = strip_tags($description);
                    $graphObject = CParsers::linkFetch($linkFetchcontent);

                    if ($graphObject){
                        $params->merge($graphObject);
                    }
                }

                $page->params = $params->toString();

                $page->updateStats();
                $page->store();
                
                // Set the user as page member
                $my->updatePageList();

                $params = new CParameter('');
                $params->set('action', 'page.update');
                $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $pageid);

                //add user points
                if(CUserPoints::assignPoint('page.updated')){
                    $act = new stdClass();
                    $act->cmd = 'page.update';
                    $act->actor = $my->id;
                    $act->target = 0;
                    $act->title = '';
                    $act->content = '';
                    $act->app = 'pages.update';
                    $act->cid = $page->id;
                    $act->pageid = $page->id;

                    // Add activity logging. Delete old ones
                    CActivityStream::remove($act->app, $act->cid);
                    CActivityStream::add($act, $params->toString());
                }

                // Update photos privacy
                $photoPermission = $page->permissions;
                $photoModel = CFactory::getModel('photos');
                $photoModel->updatePermissionByGroup($page->id, $photoPermission);

                // Reupdate the display.
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_UPDATED'));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));
                return;
            }
        }
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_ACTIVITIES));
        echo $view->get(__FUNCTION__);
    }

    /**
     * Method to display the create group form
     * */
    public function create() {
        $my = CFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();

        if ($my->authorise('community.add', 'pages')) {
            $model = CFactory::getModel('Pages');
            if (CLimitsLibrary::exceedDaily('pages')) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_LIMIT_REACHED'), 'error');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages', false));
            }

            $model = $this->getModel('pages');
            $data = new stdClass();
            $data->categories = $model->getCategories();
            
            if ($jinput->post->get('action', '', 'STRING') == 'save') {
                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-pages-forms'));
                
                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $pageid = $this->save();

                    if ($pageid !== FALSE) {
                        $mainframe = JFactory::getApplication();

                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($pageid);

                        // Set the user as page member
                        $my->updatePageList();

                        //lets create the default avatar for the page
                        $avatarAlbum = JTable::getInstance('Album', 'CTable');
                        $avatarAlbum->addAvatarAlbum($page->id, 'page');
                        $coverAlbum = JTable::getInstance('Album', 'CTable');
                        $coverAlbum->addCoverAlbum('page',$page->id);
                        $defaultAlbum = JTable::getInstance('Album', 'CTable');
                        $defaultAlbum->addDefaultAlbum($page->id, 'page');


                        //trigger for onPageCreate
                        $this->triggerPageEvents('onPageCreate', $page);

                        if ($config->get('moderatepagecreation')) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_PAGES_MODERATION_MSG', $page->name), 'message');
                            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid='.$page->id, false));
                            return;
                        }

                        $url = CRoute::_('index.php?option=com_community&view=pages&task=created&pageid=' . $pageid, false);
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
                echo $errorMsg;
            }
            return;
        }
        //Clear Cache in front page
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));
        $this->renderView(__FUNCTION__, $data);
    }

    /**
     * A new group has been created
     */
    public function created() {
        $this->renderView(__FUNCTION__);
    }

    private function _bindParams() {
        $params = new CParameter('');
        $jinput = JFactory::getApplication()->input;
        $pageid = $jinput->request->getInt('pageid', '');
        $mainframe = JFactory::getApplication();
        $redirect = CRoute::_('index.php?option=com_community&view=pages&task=edit&pageid=' . $pageid, false);

        $params->set('discussordering', 0);

        // Set the group photo permission
        if (array_key_exists('photopermission-admin', $jinput->post->getArray())) {
            $params->set('photopermission', PAGE_PHOTO_PERMISSION_ADMINS);

            if (array_key_exists('photopermission-member', $jinput->post->getArray())) {
                $params->set('photopermission', PAGE_PHOTO_PERMISSION_ALL);
            }
        } else {
            $params->set('photopermission', PAGE_PHOTO_PERMISSION_DISABLE);
        }

        // Set the group video permission
        if (array_key_exists('videopermission-admin', $jinput->post->getArray())) {
            $params->set('videopermission', PAGE_VIDEO_PERMISSION_ADMINS);
            if (array_key_exists('videopermission-member', $jinput->post->getArray())) {
                $params->set('videopermission', PAGE_VIDEO_PERMISSION_ALL);
            }
        } else {
            $params->set('videopermission', PAGE_VIDEO_PERMISSION_DISABLE);
        }

        $pagerecentevent = $jinput->request->getInt('pagerecentevents', PAGE_EVENT_RECENT_LIMIT);
        if ($pagerecentevent < 1) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGE_RECENT_EVENTS_SETTING_ERROR'));
            $mainframe->redirect($redirect);
            return;
        }
        $params->set('pagerecentevents', $pagerecentevent);

        // Set the group event permission
        if (array_key_exists('eventpermission-admin', $jinput->post->getArray())) {
            $params->set('eventpermission', PAGE_EVENT_PERMISSION_ADMINS);

            if (array_key_exists('eventpermission-member', $jinput->post->getArray())) {
                $params->set('eventpermission', PAGE_EVENT_PERMISSION_ALL);
            }
        } else {
            $params->set('eventpermission', PAGE_EVENT_PERMISSION_DISABLE);
        }

        // Set the group filesharing permission
        if (array_key_exists('filesharingpermission-admin', $jinput->post->getArray())) {
            $params->set('filesharingpermission', PAGE_FILESHARING_PERMISSION_ADMINS);

            if (array_key_exists('filesharingpermission-member', $jinput->post->getArray())) {
                $params->set('filesharingpermission', PAGE_FILESHARING_PERMISSION_ALL);
            }
        } else {
            $params->set('filesharingpermission', PAGE_FILESHARING_PERMISSION_DISABLE);
        }

        // Set the group polls permission
        if (array_key_exists('pollspermission-admin', $jinput->post->getArray())) {
            $params->set('pollspermission', PAGE_POLLS_PERMISSION_ADMINS);

            if (array_key_exists('pollspermission-member', $jinput->post->getArray())) {
                $params->set('pollspermission', PAGE_POLLS_PERMISSION_ALL);
            }
        } else {
            $params->set('pollspermission', PAGE_POLLS_PERMISSION_DISABLE);
        }

        $config = CFactory::getConfig();
        $pagerecentphotos = $jinput->request->getInt('pagerecentphotos', PAGE_PHOTO_RECENT_LIMIT);
        if ($pagerecentphotos < 1 && $config->get('enablephotos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_ALBUM_SETTING_ERROR'));
            $mainframe->redirect($redirect);
            return;
        }
        $params->set('pagerecentphotos', $pagerecentphotos);

        $pagerecentvideos = $jinput->request->getInt('pagerecentvideos', PAGE_VIDEO_RECENT_LIMIT);
        if ($pagerecentvideos < 1 && $config->get('enablevideos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUP_RECENT_VIDEOS_SETTING_ERROR'));
            $mainframe->redirect($redirect);
            return;
        }
        $params->set('pagerecentvideos', $pagerecentvideos);

        $newmembernotification = $jinput->post->getInt('newmembernotification', 0);
        $params->set('newmembernotification', $newmembernotification);

        $joinrequestnotification = $jinput->post->getInt('joinrequestnotification', 0);
        $params->set('joinrequestnotification', $joinrequestnotification);

        $wallnotification = $jinput->post->getInt('wallnotification', 0);
        $params->set('wallnotification', $wallnotification);

        $pagediscussionfilesharing = $jinput->post->getInt('pagediscussionfilesharing', 0);
        $params->set('pagediscussionfilesharing', $pagediscussionfilesharing);

        $pageannouncementfilesharing = $jinput->post->getInt('pageannouncementfilesharing', 0);
        $params->set('pageannouncementfilesharing', $pageannouncementfilesharing);

        return $params;
    }

    /**
     * Method to save the group
     * @return false if create fail, return the group id if create is successful
     * */
    public function save() {
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

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        // Get my current data.
        $my = CFactory::getUser();
        $validated = true;

        $page = JTable::getInstance('Page', 'CTable');
        $model = $this->getModel('pages');
        
        $permissions = $jinput->post->get('permissions', 10, 'INT');
        $name = $jinput->post->get('name', '', 'STRING');

        $config = CFactory::getConfig();
        $inputFilter = CFactory::getInputFilter($config->get('allowhtml'));

        $description = $jinput->post->get('description', '', 'RAW');
        $description = $inputFilter->clean($description);

        $summary = $jinput->post->get('summary', '', 'RAW');
        $summary = $inputFilter->clean($summary);

        $categoryId = $jinput->post->get('categoryid', '', 'INT');
        $website = $jinput->post->get('website', '', 'RAW');
        $pagerecentphotos = $jinput->post->get('pagerecentphotos', '', 'NONE');
        $pagerecentvideos = $jinput->post->get('pagerecentvideos', '', 'NONE');
        $pagerecentevents = $jinput->post->get('pagerecentevents', '', 'NONE');

        // @rule: Test for emptyness
        if (empty($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_EMPTY_NAME_ERROR'), 'error');
        }

        // @rule: Test if page exists
        if ($model->pageExist($name)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_NAME_TAKEN_ERROR'), 'error');
        }
        
        // @rule: Test for emptyness
        if (empty(strip_tags($description))) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_DESCRIPTION_EMPTY_ERROR'), 'error');
        }

        if (empty($categoryId)) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGE_CATEGORY_NOT_SELECTED'), 'error');
        }

        if ($pagerecentphotos < 1 && $config->get('enablephotos') && $config->get('pagephotos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGE_RECENT_ALBUM_SETTING_ERROR'), 'error');
        }

        if ($pagerecentvideos < 1 && $config->get('enablevideos') && $config->get('pagevideos')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGE_RECENT_VIDEOS_SETTING_ERROR'), 'error');
        }

        if ($pagerecentevents < 1 && $config->get('enableevents') && $config->get('page_events')) {
            $validated = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGE_RECENT_EVENTS_SETTING_ERROR'), 'error');
        }

        if ($validated) {
            // Assertions
            // Category Id must not be empty and will cause failure on this page if its empty.
            CError::assert($categoryId, '', '!empty', __FILE__, __LINE__);

            // @rule: Retrieve params and store it back as raw string
            $params = $this->_bindParams();


            if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $description))) {
                $linkFetchcontent = strip_tags($description);
                $graphObject = CParsers::linkFetch($linkFetchcontent);

                if ($graphObject){
                    $params->merge($graphObject);
                }
            }

            $now = new JDate();

            // Bind the post with the table first
            $page->name = $name;
            $page->permissions = $permissions;
            $page->description = $description;
            $page->summary= $summary;
            $page->categoryid = $categoryId;
            $page->website = $website;
            $page->ownerid = $my->id;
            $page->created = $now->toSql();
            $page->unlisted = $jinput->post->get('unlisted', 0, 'INT');

            if (array_key_exists('approvals', $jinput->post->getArray())) {
                $page->approvals = $jinput->post->get('approvals', '0', 'INT');
            } else {
                $page->approvals = 0;
            }

            $page->params = $params->toString();

            // @rule: check if moderation is turned on.
            $page->published = ( $config->get('moderatepagecreation') ) ? 0 : 1;

            $page->store();

            // Since this is storing pages, we also need to store the creator / admin
            // into the pages members table
            $member = JTable::getInstance('PageMembers', 'CTable');
            $member->pageid = $page->id;
            $member->memberid = $page->ownerid;

            // Creator should always be 1 as approved as they are the creator.
            $member->approved = 1;

            // @todo: Setup required permissions in the future
            $member->permissions = '1';
            $member->store();

            // @rule: Only add into activity once a page is created and it is published.
            if ($page->published && !$page->unlisted) {
                $act = new stdClass();
                $act->cmd = 'page.create';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = JText::sprintf('COM_COMMUNITY_PAGES_NEW_PAGE_CATEGORY', '{page_url}', $page->name, '{category_url}', $page->getCategoryName());
                $act->content = ( $page->approvals == 0) ? $page->description : '';
                $act->app = 'pages';
                $act->cid = $page->id;
                $act->pageid = $page->id;
                $act->page_access = $page->approvals;

                // Allow comments
                $act->comment_type = 'pages.create';
                $act->like_type = 'pages.create';
                $act->comment_id = CActivities::COMMENT_SELF;
                $act->like_id = CActivities::LIKE_SELF;

                // Store the page now.
                $page->updateStats();
                $page->store();

                $params = new CParameter('');
                $params->set('action', 'page.create');
                $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                $params->set('category_url', 'index.php?option=com_community&view=pages&categoryid=' . $page->categoryid);

                // Add activity logging
                CActivityStream::add($act, $params->toString());
            }

            // if need approval should send email notification to admin
            if ($config->get('moderatepagecreation')) {
                $title_email = JText::_('COM_COMMUNITY_EMAIL_NEW_PAGE_NEED_APPROVAL_TITLE');
                $message_email = JText::sprintf('COM_COMMUNITY_EMAIL_NEW_PAGE_NEED_APPROVAL_MESSAGE', $my->getDisplayName(), $page->name);
                $from = $mainframe->get('mailfrom');
                $to = $config->get('notifyMaxReport');
                CNotificationLibrary::add('pages_create', $from, $to, $title_email, $message_email, '', '');
            }

            //add user points
            CUserPoints::assignPoint('page.create');

            $validated = $page->id;
        }

        return $validated;
    }
    
    public function mypages() {
        $jinput = JFactory::getApplication()->input;
        $my = CFactory::getUser();

        if (!$my->authorise('community.view', 'pages.my')) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        // check if user exist or not
        $userid = $jinput->get('userid', $my->id);
        $user = CFactory::getUser($userid);
            
        if (!$user->id) {
            $redirectUrl = CRoute::_('index.php?option=com_community&view=pages', false);
            JFactory::getApplication()->redirect($redirectUrl);
        }

        $this->renderView(__FUNCTION__, $userid);
    }

    private function _saveMember($pageId)
    {
        $page = JTable::getInstance('Page', 'CTable');
        $member = JTable::getInstance('PageMembers', 'CTable');

        $page->load($pageId);
        $params = $page->getParams();
        $my = CFactory::getUser();

        // Set the properties for the members table
        $member->pageid = $page->id;
        $member->memberid = $my->id;

        // @rule: If approvals is required, set the approved status accordingly.
        $member->approved = ( $page->approvals == COMMUNITY_PRIVATE_PAGE ) ? '0' : 1;

        // @rule: Special users should be able to join the page regardless if it requires approval or not
        $member->approved = COwnerHelper::isCommunityAdmin() ? 1 : $member->approved;

        // @rule: Invited users should be able to join the page immediately.
        $pageInvite = JTable::getInstance('PageInvite', 'CTable');
        $keys = array('pageid' => $pageId, 'userid' => $my->id);
        if ($pageInvite->load($keys)) {
            $member->approved = 1;
        }

        //@todo: need to set the privileges
        $member->permissions = '0';

        $member->store();
        $owner = CFactory::getUser($page->ownerid);

        // Update user page list
        $my->updatePageList();

        // Test if member is approved, then we add logging to the activities.
        if ($member->approved) {
            CPages::joinApproved($pageId, $my->id);

            //trigger for onPageJoin
            $this->triggerPageEvents('onPageJoin', $page, $my->id);
        }

        return $member;
    }

    public function uploadAvatar() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $my = CFactory::getUser();
        $config = CFactory::getConfig();

        $pageid = $jinput->request->get('pageid', '', 'INT');
        $data = new stdClass();
        $data->id = $pageid;

        $pagesModel = $this->getModel('pages');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);

        if (!$my->authorise('community.upload', 'pages.avatar.' . $pageid, $page)) {
            $errorMsg = $my->authoriseErrorMsg();
            if (!$errorMsg) {
                return $this->blockUnregister();
            } else {
                echo $errorMsg;
            }
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            //CFactory::load( 'helpers' , 'image' );
            $fileFilter = new JInput($jinput->files->getArray());
            $file = $fileFilter->get('filedata', '', 'array');

            if (!CImageHelper::isValidType($file['type'])) {
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id . '&task=uploadAvatar', false));
            }

            //CFactory::load( 'libraries' , 'apps' );
            $appsLib = CAppPlugins::getInstance();
            $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-pages-uploadavatar'));

            if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                if (empty($file)) {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_NO_POST_DATA'), 'error');
                } else {
                    $uploadLimit = (double) $config->get('maxuploadsize');
                    $uploadLimit = ( $uploadLimit * 1024 * 1024 );

                    // @rule: Limit image size based on the maximum upload allowed.
                    if (filesize($file['tmp_name']) > $uploadLimit && $uploadLimit != 0) {
                        $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED_MB',CFactory::getConfig()->get('maxuploadsize')), 'error');
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=uploadavatar&pageid=' . $page->id, false));
                    }

                    if (!CImageHelper::isValid($file['tmp_name'])) {
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'), 'error');
                    } else {
                        // @todo: configurable width?
                        $imageMaxWidth = 160;

                        // Get a hash for the file name.
                        $fileName = JApplicationHelper::getHash($file['tmp_name'] . time());
                        $hashFileName = CStringHelper::substr($fileName, 0, 24);

                        // @todo: configurable path for avatar storage?
                        $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar/pages';
                        $storageImage = $storage . '/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $image = $config->getString('imagefolder') . '/avatar/pages/' . $hashFileName . CImageHelper::getExtension($file['type']);
                        $thumbnail = $config->getString('imagefolder') . '/avatar/pages/' . 'thumb_' . $hashFileName . CImageHelper::getExtension($file['type']);

                        // Generate full image
                        if (!CImageHelper::resizeProportional($file['tmp_name'], $storageImage, $file['type'], $imageMaxWidth)) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage), 'error');
                        }

                        // Generate thumbnail
                        if (!CImageHelper::createThumb($file['tmp_name'], $storageThumbnail, $file['type'])) {
                            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageThumbnail), 'error');
                        }

                        // Autorotate avatar based on EXIF orientation value
                        if ($file['type'] == 'image/jpeg') {
                            $orientation = CImageHelper::getOrientation($file['tmp_name']);
                            CImageHelper::autoRotate($storageImage, $orientation);
                            CImageHelper::autoRotate($storageThumbnail, $orientation);
                        }

                        // Update the page with the new image
                        $pagesModel->setImage($pageid, $image, 'avatar');
                        $pagesModel->setImage($pageid, $thumbnail, 'thumb');

                        // add points and generate stream if needed
                        $generateStream = CUserPoints::assignPoint('page.avatar.upload');
                        // @rule: only add the activities of the news if the page is not private.
                        if ($page->approvals == COMMUNITY_PUBLIC_PAGE && $generateStream) {
                            $url = CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $pageid);
                            $act = new stdClass();
                            $act->cmd = 'page.avatar.upload';
                            $act->actor = $my->id;
                            $act->target = 0;
                            $act->title = JText::sprintf('COM_COMMUNITY_PAGES_NEW_PAGE_AVATAR', '{page_url}', $page->name);
                            $act->content = '<img src="' . JURI::root(true) . '/' . $thumbnail . '" style="border: 1px solid #eee;margin-right: 3px;" />';
                            $act->app = 'pages';
                            $act->cid = $page->id;
                            $act->pageid = $page->id;

                            $params = new CParameter('');
                            $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);


                            CActivityStream::add($act, $params->toString());
                        }

                        $mainframe = JFactory::getApplication();
                        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_AVATAR_UPLOADED'));
                        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $pageid, false));
                        exit;
                    }
                }
            }
        }
        //ClearCache in frontpage
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES));

        echo $view->get(__FUNCTION__, $data);
    }

    public function viewmembers() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $data = new stdClass();
        $data->id = $jinput->get('pageid', '', 'INT');

        if (!$data->id) {
            $redirectUrl = CRoute::_('index.php?option=com_community&view=pages', false);
            $mainframe->redirect($redirectUrl);
        }

        if (!$my->authorise('community.view', 'pages.member.' . $data->id)) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__, $data);
    }

    public function viewreviews() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $data = new stdClass();
        $data->id = $jinput->get('pageid', '', 'INT');

        if (!$data->id) {
            $redirectUrl = CRoute::_('index.php?option=com_community&view=pages', false);
            $mainframe->redirect($redirectUrl);
        }

        if (!$my->authorise('community.view', 'pages.review.' . $data->id)) {
            $errorMsg = $my->authoriseErrorMsg();
            echo $errorMsg;
            return;
        }

        $this->renderView(__FUNCTION__, $data);
    }

    public function triggerPageEvents($eventName, &$args, $target = null) {
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
    
    /**
     * Method that loads the viewing of a specific page
     * */
    public function viewpage() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        if (!$my->authorise('community.view', 'pages.list')) {
            echo JText::_('COM_COMMUNITY_PAGES_DISABLE');
            return;
        }

        // Load the page table.
        $pageid = $jinput->getInt('pageid', '');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageid);

        $activityId = $jinput->get->get('actid', 0, 'INT');
        if($activityId){
            $activity = JTable::getInstance('Activity', 'CTable');
            $activity->load($activityId);
            $jinput->set('userid', $activity->actor);
            $userid = $activity->actor;
        }

        if (empty($page->id)) {
            echo CSystemHelper::showErrorPage();
            return;
        }

        $pageModel = CFactory::getModel('pages');
        if($page->unlisted && !$pageModel->isMember($my->id, $page->id) && !$pageModel->isInvited($my->id, $page->id) && !COwnerHelper::isCommunityAdmin()){
            return JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_UNLISTED_ERROR'), 'error');
        }
        if($activityId) {
            $activity->page = $page;
            echo $this->renderView('singleActivity', $activity);
        } else {
            $this->renderView(__FUNCTION__, $page);
        }
    }

    public function invitefriends() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $jinput->get('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        $my = CFactory::getUser();
        $invited = $jinput->post->get('invite-list', '', 'NONE');
        $inviteMessage = $jinput->post->get('invite-message', '', 'STRING');
        $pageId = $jinput->request->get('pageid', '', 'INT');
        $pagesModel = $this->getModel('pages');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        // Check if the user is banned
        $isBanned = $page->isBanned($my->id);

        if ($my->id == 0) {
            return $this->blockUnregister();
        }

        if ((!$page->isMember($my->id) || $isBanned) && !COwnerHelper::isCommunityAdmin()) {
            CSystemHelper::showErrorPage();
            return;
        }

        if ($jinput->getMethod() == 'POST') {
            JSession::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            if (!empty($invited)) {
                $mainframe = JFactory::getApplication();
                $pagesModel = CFactory::getModel('Pages');
                $page = JTable::getInstance('Page', 'CTable');
                $page->load($pageId);


                foreach ($invited as $invitedUserId) {
                    $pageInvite = JTable::getInstance('PageInvite', 'CTable');
                    $pageInvite->pageid = $page->id;
                    $pageInvite->userid = $invitedUserId;
                    $pageInvite->creator = $my->id;

                    $pageInvite->store();
                }

                $params = new CParameter('');
                $params->set('url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                $params->set('pagename', $page->name);
                $params->set('message', $inviteMessage);
                $params->set('page', $page->name);
                $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);

                CNotificationLibrary::add('pages_invite', $my->id, $invited, JText::sprintf('COM_COMMUNITY_PAGES_JOIN_INVITATION_MESSAGE'), '', 'pages.invite', $params);

                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_INVITATION_SEND_MESSAGE'));
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));
            } else {
                $view->addWarning(JText::_('COM_COMMUNITY_INVITE_NEED_AT_LEAST_1_FRIEND'));
            }
        }
        echo $view->get(__FUNCTION__);
    }

    public function ajaxShowUnpublishPage($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $response = new JAXResponse();

        $model = $this->getModel('pages');
        $my = CFactory::getUser();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $html  = JText::_('COM_COMMUNITY_PAGES_UNPUBLISH_CONFIRMATION') . ' <strong>' . $page->name . '</strong>?';
        $html .= '<form method="POST" action="' . CRoute::_('index.php?option=com_community&view=pages&task=ajaxUnpublishPage') . '" style="margin:0">';
        $html .= '<input type="hidden" value="' . $pageId . '" name="pageid">';
        $html .= '</form>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_PAGES_UNPUBLISH'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON'),
        );

        die( json_encode($json) );
    }

    public function ajaxUnpublishPage($pageId=null) {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $pageId = $jinput->post->get('pageid', '', 'INT');
        $response = new JAXResponse();

        CError::assert($pageId, '', '!empty', __FILE__, __LINE__);

        if (!CFactory::getUser()->authorise('community.pageeditstate', 'com_community')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'), 'error');
            return false;
        } else {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageId);

            if ($page->id == 0) {
                $response->addScriptCall('alert', JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM'));
            } else {
                $page->published = 0;

                if ($page->store()) {
                    //trigger for onGroupDisable
                    $this->triggerPageEvents('onPageDisable', $page);
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_UNPUBLISH_SUCCESS'));
                    $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages', false));
                } else {
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_SAVE_ERROR'), 'error');
                    return false;
                }
            }
        }
    }

    public function ajaxWarnPageDeletion($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $json = array(
            'title' => JText::sprintf('COM_COMMUNITY_PAGES_DELETE_PAGE', $page->name),
            'html'  => JText::_('COM_COMMUNITY_PAGES_DELETE_WARNING'),
            'btnDelete' => JText::_('COM_COMMUNITY_DELETE'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxDeletePage($pageId, $step = 1) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');
        $step = $filter->clean($step, 'int');

        $json = array();
        $response = new JAXResponse();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $pageModel = CFactory::getModel('pages');
        $membersCount = $pageModel->getMembersCount($pageId);

        $my = CFactory::getUser();

        // @rule: Do not allow anyone that tries to be funky!
        if (!$my->authorise('community.delete', 'pages.' . $pageId, $page)) {
            $json['error'] = JText::_('COM_COMMUNITY_PAGES_NOT_ALLOWED_DELETE');
            die( json_encode($json) );
        }

        $doneMessage = ' - <span class=\'success\'>' . JText::_('COM_COMMUNITY_DONE') . '</span><br />';
        $failedMessage = ' - <span class=\'failed\'>' . JText::_('COM_COMMUNITY_FAILED') . '</span><br />';
        $childId = 0;
        switch ($step) {
            case 1:
                // Nothing gets deleted yet. Just show a messge to the next step
                if (empty($pageId)) {
                    $json['error'] = JText::_('COM_COMMUNITY_PAGES_ID_NOITEM');
                } else {
                    $json['message']  = '<strong>' . JText::sprintf('COM_COMMUNITY_PAGES_DELETE_PAGE', $page->name) . '</strong><br/>';
                    $json['next'] = 2;

                    //trigger for onBeforePageDelete
                    $this->triggerPageEvents('onBeforePageDelete', $page);
                }
                break;

            case 2:
                $content = JText::_('COM_COMMUNITY_PAGES_DELETE_PAGE_MEMBERS');

                $json['message'] = $content;
                $json['next'] = 3;
                break;

            case 3:
                // Delete all page members
                if (CommunityModelPages::deletePageMembers($pageId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $content .= JText::_('COM_COMMUNITY_PAGES_WALLS_DELETE');

                $json['message'] = $content;
                $json['next'] = 4;
                break;

            case 4:
                // Delete all page wall
                if (CommunityModelPages::deletePageWall($pageId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $json['message'] = $content;
                $json['next'] = 5;
                break;

            case 5:
                $content = JText::_('COM_COMMUNITY_PAGES_DELETE_MEDIA');

                $json['message'] = $content;
                $json['next'] = 6;
                break;

            case 6:
                // Delete all page's media files
                if (CommunityModelPages::deletePageMedia($pageId)) {
                    $content = $doneMessage;
                } else {
                    $content = $failedMessage;
                }

                $json['message'] = $content;
                $json['next'] = 7;
                break;

            case 7:
                // Delete page
                $page = JTable::getInstance('Page', 'CTable');
                $page->load($pageId);
                $pageData = $page;

                if ($page->delete($pageId)) {
                    //CFactory::load( 'libraries' , 'featured' );
                    $featured = new CFeatured(FEATURED_PAGES);
                    $featured->delete($pageId);

                    jimport('joomla.filesystem.file');

                    //@rule: Delete only thumbnail and avatars that exists for the specific page
                    if ($pageData->avatar != "components/com_community/assets/page.jpg" && !empty($pageData->avatar)) {
                        $path = explode('/', $pageData->avatar);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    if ($pageData->thumb != "components/com_community/assets/page_thumb.jpg" && !empty($pageData->thumb)) {
                        $path = explode('/', $pageData->thumb);
                        $file = JPATH_ROOT . '/' . $path[0] . '/' . $path[1] . '/' . $path[2] . '/' . $path[3];
                        if (JFile::exists($file)) {
                            JFile::delete($file);
                        }
                    }

                    $db = JFactory::getDbo();
                    //remove all stats from the page
                    $query = "DELETE FROM ".$db->quoteName('#__community_page_stats')
                        ." WHERE ".$db->quoteName('pid')."=".$db->quote($pageId);
                    $db->setQuery($query);
                    $db->execute();

                    $content = JText::_('COM_COMMUNITY_PAGES_DELETED');

                    //trigger for onPageDelete
                    $this->triggerPageEvents('onAfterPageDelete', $pageData);
                } else {
                    $content = JText::_('COM_COMMUNITY_PAGES_DELETE_ERROR');
                }

                $redirect = CRoute::_('index.php?option=com_community&view=pages', false);

                $json['message'] = $content;
                $json['redirect'] = $redirect;
                $json['btnDone'] = JText::_('COM_COMMUNITY_DONE_BUTTON');
                break;

            default:
                break;
        }
        //Clear Cache for pages
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_PAGES, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_PAGES_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        die( json_encode($json) );
    }
    
    public function ajaxRemoveFeatured($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $json = array();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            $featured = new CFeatured(FEATURED_PAGES);
            $my = CFactory::getUser();

            if ($featured->delete($pageId)) {
                $json['success'] = true;
                $json['html'] = JText::_('COM_COMMUNITY_PAGE_REMOVED_FROM_FEATURED');
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_REMOVING_PAGE_FROM_FEATURED_ERROR');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_PAGES));

        die( json_encode($json) );
    }

    public function ajaxAddFeatured($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $json = array();

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_PAGES, $pageId)) {

                $featured = new CFeatured(FEATURED_PAGES);
                $table = JTable::getInstance('Page', 'CTable');
                $table->load($pageId);
                $my = CFactory::getUser();
                $config = CFactory::getConfig();
                $limit = $config->get( 'featured' . FEATURED_PAGES . 'limit' , 10 );

                if($featured->add($pageId, $my->id)===true){
                    $json['success'] = true;
                    $json['html'] = JText::sprintf('COM_COMMUNITY_PAGE_IS_FEATURED', $table->name);
                }else{
                    $json['error'] = JText::sprintf('COM_COMMUNITY_GROUP_LIMIT_REACHED_FEATURED', $table->name, $limit);
                }
            } else {
                $json['error'] = JText::_('COM_COMMUNITY_PAGES_ALREADY_FEATURED');
            }
        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        //ClearCache in Featured List
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_PAGES));

        die( json_encode($json) );
    }
    
    public function ajaxRemoveAdmin($memberId, $pageId) {
        return $this->updateAdmin($memberId, $pageId, false);
    }

    public function ajaxAddAdmin($memberId, $pageId) {
        return $this->updateAdmin($memberId, $pageId, true);
    }

    public function updateAdmin($memberId, $pageId, $doAdd = true) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();

        $model = $this->getModel('pages');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        //CFactory::load( 'helpers' , 'owner' );

        if (!$my->authorise('community.edit', 'pages.admin.' . $pageId, $page)) {
            $response->addScriptCall('joms.jQuery("#notice-message").html("' . JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING') . '");');
            $response->addScriptCall('joms.jQuery("#notice").css("display","block");');
            $response->addScriptCall('joms.jQuery("#notice").attr("class","alert alert-danger");');
        } else {
            $member = JTable::getInstance('PageMembers', 'CTable');

            $keys = array('pageId' => $page->id, 'memberId' => $memberId);
            $member->load($keys);
            $member->permissions = $doAdd ? 1 : 0;
            $member->approved = 1;

            $member->store();
            $message = $doAdd ? JText::_('COM_COMMUNITY_PAGES_NEW_ADMIN_MESSAGE') : JText::_('COM_COMMUNITY_PAGES_NEW_USER_MESSAGE');
            $response->addScriptCall('joms.jQuery("#member_' . $memberId . '");');
            $response->addScriptCall('joms.jQuery("#notice-message").html("' . $message . '");');
            $response->addScriptCall('joms.jQuery("#notice").css("display","block");');

            if ($doAdd) {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].removeClass("hide");');
            } else {
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[1].addClass("hide");');
                $response->addScriptCall('joms.jQuery("#member_' . $memberId . ' ul li.setAdmin")[0].removeClass("hide");');
            }
        }

        return $response->sendResponse();
    }

    public function ajaxConfirmMemberRemoval($memberId, $pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $json = array();

        // Get html
        $member = CFactory::getUser($memberId);
        $html  = JText::sprintf('COM_COMMUNITY_PAGES_MEMBER_REMOVAL_WARNING', $member->getDisplayName());
        $html .= '<div><label><input type="checkbox" name="block" class="joms-checkbox">&nbsp;' . JText::_('COM_COMMUNITY_ALSO_BAN_MEMBER') . '</label></div>';

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_PAGES));

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_REMOVE_MEMBER'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die( json_encode($json) );
    }

    /**
     * Ajax method to remove specific member
     *
     * @params  string  id  The member's id that needs to be approved.
     * @params  string  pageid The page id that the user is in.
     * */
    public function ajaxRemoveMember($memberId, $pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        $json = array();

        $model = $this->getModel('pages');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        $my = CFactory::getUser();

        if (!$my->authorise('community.remove', 'pages.member.' . $memberId, $page)) {
            $errorMsg = $my->authoriseErrorMsg();
            if ($errorMsg == 'blockUnregister') {
                return $this->ajaxBlockUnregister();
            } else {
                $json['error'] = $errorMsg;
            }
        } else {
            $pageMember = JTable::getInstance('PageMembers', 'CTable');
            $keys = array('pageId' => $pageId, 'memberId' => $memberId);
            $pageMember->load($keys);

            $data = new stdClass();

            $data->pageid = $pageId;
            $data->memberid = $memberId;

            $model->removeMember($data);
            $user = CFactory::getUser($memberId);
            $user->updatePageList(true);

            //trigger for onPageLeave
            $this->triggerPageEvents('onPageLeave', $page, $memberId);

            //add user points
            CUserPoints::assignPoint('page.member.remove', $my->id);

            //delete invitation
            $invitation = JTable::getInstance('Invitation', 'CTable');
            $invitation->deleteInvitation($pageId, $memberId, 'pages,inviteUsers');
            
            // remove like
            $unlike = new CLike();
            $unlike->unlike('pages', $pageId, $memberId);

            $json['success'] = true;
            $json['message'] = JText::_('COM_COMMUNITY_PAGES_MEMBERS_DELETE_SUCCESS');
        }

        // Store the page and update the data
        $page->updateStats();
        $page->store();

        die( json_encode($json) );
    }
   
    public function ajaxUnbanMember($memberId, $pageId) {
        return $this->updateMemberBan($memberId, $pageId, FALSE);
    }

    public function ajaxBanMember($memberId, $pageId) {
        return $this->updateMemberBan($memberId, $pageId, TRUE);
    }

    /**
     * Refactored from AjaxUnBanMember and AjaxBanMember
     */
    public function updateMemberBan($memberId, $pageId, $doBan = true) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');
        $memberId = $filter->clean($memberId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $json = array();
        $my = CFactory::getUser();

        $pageModel = CFactory::getModel('pages');
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);

        if (!$my->authorise('community.update', 'pages.member.ban.' . $pageId, $page)) {
            $json['error'] = JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING');
        } else {
            $member = JTable::getInstance('PageMembers', 'CTable');
            $keys = array('pageId' => $page->id, 'memberId' => $memberId);
            $member->load($keys);

            $member->permissions = ($doBan) ? COMMUNITY_PAGE_BANNED : COMMUNITY_PAGE_MEMBER;

            $member->store();

            $page->updateStats();

            $page->store();

            if ($doBan) { //if user is banned, display the appropriate response and color code
                //trigger for onPageBanned
                $this->triggerPageEvents('onPageBanned', $page, $memberId);
                $json['success'] = true;
                $json['message'] = JText::_('COM_COMMUNITY_PAGES_MEMBER_BEEN_BANNED');
            } else {
                //trigger for onPageUnbanned
                $this->triggerPageEvents('onPageUnbanned', $page, $memberId);
                $json['success'] = true;
                $json['message'] = JText::_('COM_COMMUNITY_PAGES_MEMBER_BEEN_UNBANNED');
            }
        }

        die( json_encode($json) );
    }

    public function ajaxUpdateCount($type, $pageid) {
        $response = new JAXResponse();
        $my = CFactory::getUser();

        if ($my->id) {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageid);
        }

        return $response->sendResponse();
    }

    public function ajaxShowPageFeatured($pageId) {
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);
        $page->updateStats(); //ensure that stats are up-to-date
        // Get Avatar
        $avatar = $page->getAvatar('avatar');

        // page date
        $config = CFactory::getConfig();
        $pageDate = JHTML::_('date', $page->created, JText::_('DATE_FORMAT_LC'));

        // Get page link
        $pageLink = CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);

        // Get unfeature icon
        $pageUnfeature = '<a class="album-action remove-featured" title="' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '" onclick="joms.featured.remove(\'' . $page->id . '\',\'pages\');" href="javascript:void(0);">' . JText::_('COM_COMMUNITY_REMOVE_FEATURED') . '</a>';

        // Get misc data
        $membercount = JText::sprintf((CStringHelper::isPlural($page->membercount)) ? 'COM_COMMUNITY_PAGES_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_PAGES_MEMBER_COUNT', $page->membercount);
        $memberCountLink = CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid=' . $page->id);

        // Get like
        $likes = new CLike();
        $likesHTML = $likes->getHTML('pages', $pageId, $my->id);

        $objResponse->addScriptCall('updatePage', $pageId, $page->name, $page->getCategoryName(), $likesHTML, $avatar, $pageDate, $pageLink, JHTML::_('string.truncate', strip_tags($page->description), 300), $membercount, $memberCountLink, $pageUnfeature);
        $objResponse->sendResponse();
    }

    public function ajaxAcceptInvitation($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('PageInvite', 'CTable');
        $keys = array('pageid' => $pageId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        $this->_saveMember($pageId);
        // delete invitation after approve
        $table->delete();
        
        // add like
        $like = new CLike();
        $like->addLike('pages', $pageId, 1, $my->id);
        
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($table->pageid);
        $url = CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
        $response->addScriptCall("joms.jQuery('#pages-invite-" . $pageId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_PAGES_ACCEPTED_INVIT', $page->name, $url) . "</span>');location.reload(true)");

        return $response->sendResponse();
    }

    public function ajaxRejectInvitation($pageId) {
        $filter = JFilterInput::getInstance();
        $pageId = $filter->clean($pageId, 'int');

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $table = JTable::getInstance('PageInvite', 'CTable');
        $keys = array('pageid' => $pageId, 'userid' => $my->id);
        $table->load($keys);

        if (!$table->isOwner()) {
            // when the user is the owner page we need avoid the invitation
            $table->delete();

            $response->addScriptCall('COM_COMMUNITY_INVALID_ACCESS');
            return $response->sendResponse();
        }

        if ($table->delete()) {
            //delete invitation
            $invitation = JTable::getInstance('Invitation', 'CTable');
            $invitation->deleteInvitation($pageId, $my->id, 'pages,inviteUsers');

            $page = JTable::getInstance('Page', 'CTable');
            $page->load($table->pageid);
            $url = CRoute::_('index.php?option=com_community&view=pages&task=viewgroup&groupid=' . $page->id);
            $response->addScriptCall("joms.jQuery('#pages-invite-" . $pageId . "').html('<span class=\"community-invitation-message\">" . JText::sprintf('COM_COMMUNITY_GROUPS_REJECTED_INVIT', $page->name, $url) . "</span>')");
        }

        return $response->sendResponse();
    }

    public function reportPage($link, $message, $pageId)
    {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $report = new CReportingLibrary();

        if (!$my->authorise('community.view', 'pages.report')) {
            return '';
        }

        $report->createReport(JText::_('Bad page'), $link, $message);

        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_PAGES_UNPUBLISH';
        $action->method = 'pages,unpublishPage';
        $action->parameters = $pageId;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    public function unpublishPage($pageId)
    {
        $page = JTable::getInstance('Page', 'CTable');
        $page->load($pageId);
        if($page->published == 1)
        {
            $page->published = '0';
            $msg = JText::_('COM_COMMUNITY_PAGES_UNPUBLISH_SUCCESS');
        }
        else
        {
            $page->published = 1;
            $msg = JText::_('COM_COMMUNITY_PAGES_PUBLISH_SUCCESS');
        }
        $page->store();

        return $msg;
    }
}