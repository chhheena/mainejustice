<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

if (!class_exists("CommunityViewPages")) {

    class CommunityViewPages extends CommunityView {

        public function _addPageInPathway($pageId) {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageId);

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway($page->name, CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id));
        }

        public function _addSubmenu() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $task = $jinput->get('task', '');
            $config = CFactory::getConfig();
            $pageid = $jinput->get('pageid', '');
            $categoryid = $jinput->get('categoryid', '');
            $my = CFactory::getUser();


            $backLink = array('createreview', 'sendmail', 'invitefriends', 'viewreviews', 'viewmembers', 'viewdiscussion', 'viewdiscussions', 'editdiscussion', 'viewbulletins', 'adddiscussion', 'addnews', 'viewbulletin', 'uploadavatar', 'edit', 'banlist');
            $excludeBannedMembers = array('banlist', 'viewbulletin', 'viewdiscussion', 'addnews', 'edit', 'editdiscussion');

            $pagesModel = CFactory::getModel('pages');
            $isAdmin = $pagesModel->isAdmin($my->id, $pageid);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            // Load the page table.
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageid);
            $isBanned = $page->isBanned($my->id);

            if (in_array($task, $backLink)) {
                if ($task == 'viewdiscussion' && !$isBanned)
                    $this->addSubmenuItem('index.php?option=com_community&view=pages&task=viewdiscussions&pageid=' . $pageid, JText::_('COM_COMMUNITY_PAGES_VIEW_ALL_DISCUSSIONS'));

                if ($task == 'viewdiscussions' && !$isBanned)
                    $this->addSubmenuItem('index.php?option=com_community&view=pages&pageid=' . $pageid . '&task=adddiscussion', JText::_('COM_COMMUNITY_PAGES_DISCUSSION_CREATE'), '', SUBMENU_RIGHT);
                if ($task == 'viewbulletins' && ($isAdmin || $isSuperAdmin))
                    $this->addSubmenuItem('index.php?option=com_community&view=pages&pageid=' . $pageid . '&task=addnews', JText::_('COM_COMMUNITY_PAGES_BULLETIN_CREATE'), '', SUBMENU_RIGHT);
                if ($task == 'viewmembers' && !$isBanned) {

                    $friends = $pagesModel->getInviteFriendsList($my->id, $pageid);
                    $userIds = '';
                    $i = 0;
                    
                    if ($friends) {
                        foreach ($friends as $friend) {
                            if ($i > 0) {
                                $userIds .= ',';
                            }

                            if ($friend instanceof CUser) {
                                $userIds .= $friend->id;
                            } else {
                                $userIds .= $friend;
                            }

                            $i++;
                        }
                    }
                    
                    $this->addSubmenuItem('index.php?option=com_community&view=pages', JText::_('COM_COMMUNITY_PAGES_ALL_PAGES'));

                    if (COwnerHelper::isRegisteredUser()) {
                        $this->addSubmenuItem('index.php?option=com_community&view=pages&task=mypages&userid=' . $my->id, JText::_('COM_COMMUNITY_PAGES_MY_PAGES'));
                        $this->addSubmenuItem('index.php?option=com_community&view=pages&task=myinvites&userid=' . $my->id, JText::_('COM_COMMUNITY_PAGES_PENDING_INVITES'));
                    }
                }
            } else {
                $this->addSubmenuItem('index.php?option=com_community&view=pages', JText::_('COM_COMMUNITY_PAGES_ALL_PAGES'));

                if (COwnerHelper::isRegisteredUser()) {
                    $this->addSubmenuItem('index.php?option=com_community&view=pages&task=mypages&userid=' . $my->id, JText::_('COM_COMMUNITY_PAGES_MY_PAGES'));
                    $this->addSubmenuItem('index.php?option=com_community&view=pages&task=myinvites&userid=' . $my->id, JText::_('COM_COMMUNITY_PAGES_PENDING_INVITES'));
                }

                if ($config->get('createpages') && ( $isSuperAdmin || (COwnerHelper::isRegisteredUser() && $my->canCreatePages() ) )) {
                    $creationLink = $categoryid ? 'index.php?option=com_community&view=pages&task=create&categoryid=' . $categoryid : 'index.php?option=com_community&view=pages&task=create';
                }

                if ((!$config->get('enableguestsearchpages') && COwnerHelper::isRegisteredUser() ) || $config->get('enableguestsearchpages')) {
                    $tmpl = new CTemplate();
                    $html = $tmpl->set('url', CRoute::_('index.php?option=com_community&view=pages&task=search'))
                            ->fetch('pages.search.submenu');
                }
            }
        }

        public function showSubmenu($display=true) {
            $this->_addSubmenu();
            return parent::showSubmenu($display);
        }
        
        public function editreview($data) {
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $model = CFactory::getModel('pages');
            $totalPage = $model->getPagesCreationCount($my->id);

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            
            $pageid = $jinput->request->getInt('pageid');
            $reviewid = $jinput->request->getInt('reviewid');
            $userid = $jinput->request->getInt('userid');
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageid);

            $pageModel = CFactory::getModel('pages');
            $isAdmin = $pageModel->isAdmin($my->id, $page->id);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();
            
            if (!$isAdmin && !$isSuperAdmin) {
                $errorMsg = $my->authoriseErrorMsg();
                if ($errorMsg == 'blockUnregister') {
                    return $this->blockUnregister();
                } else {
                    echo $errorMsg;
                }
                return;
            }

            $rating = JTable::getInstance('Rating', 'CTable');
            $isRated = $rating->isRated('pages', $pageid, $userid);
            
            //initialize default value
            $review = JTable::getInstance('Rating', 'CTable');
            
            if ($isRated) {
                $rating = $isRated;
            } else {
                $review->userid = $userid;
                $review->title = $jinput->post->get('title', '', 'STRING');
                $review->review = $jinput->post->get('review', '', 'STRING');
                $review->rating = $jinput->post->get('rating', '', 'INT');
                $review->params = '';
            }

            $tmpl = new CTemplate();

            $tmpl->set('config', $config)
                ->set('page', $page)
                ->set('pageCreated', $totalPage)
                ->set('pageCreationLimit', $config->get('pagecreatelimit'))
                ->set('params', $page->getParams())
                ->set('rating', $rating)
                ->set('isNew', false);

            echo $tmpl->fetch('pages.reviewforms');
        }

        public function createreview($data) {
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $model = CFactory::getModel('pages');
            $totalPage = $model->getPagesCreationCount($my->id);

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            
            $pageid = $jinput->request->getInt('pageid');
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageid);

            $pageModel = CFactory::getModel('pages');
            $isMember = $pageModel->isMember($my->id, $page->id);

            if (!$isMember) {
                $errorMsg = $my->authoriseErrorMsg();
                if ($errorMsg == 'blockUnregister') {
                    return $this->blockUnregister();
                } else {
                    echo $errorMsg;
                }
                return;
            }

            $rating = JTable::getInstance('Rating', 'CTable');
            $isRated = $rating->isRated('pages', $pageid, $my->id);
            
            //initialize default value
            $review = JTable::getInstance('Rating', 'CTable');
            
            if ($isRated) {
                $rating = $isRated;
            } else {
                $review->userid = $my->id;
                $review->title = $jinput->post->get('title', '', 'STRING');
                $review->review = $jinput->post->get('review', '', 'STRING');
                $review->rating = $jinput->post->get('rating', '', 'INT');
                $review->params = '';
            }

            $tmpl = new CTemplate();

            $tmpl->set('config', $config)
                ->set('page', $page)
                ->set('pageCreated', $totalPage)
                ->set('pageCreationLimit', $config->get('pagecreatelimit'))
                ->set('params', $page->getParams())
                ->set('rating', $rating)
                ->set('isNew', (!$isRated) ? true : false);

            echo $tmpl->fetch('pages.reviewforms');
        }

        public function edit() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_EDIT_TITLE'));

            $config = CFactory::getConfig();
            $jConfig = JFactory::getConfig();

            $mainframe = JFactory::getApplication();
            
            $this->showSubmenu();
            
            $jinput = JFactory::getApplication()->input;
            $pageid = $jinput->request->getInt('pageid');
            $pageModel = CFactory::getModel('Pages');
            $categories = $pageModel->getCategories();
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($pageid);

            // @rule: Test if the page is unpublished, don't display it at all.
            if (!$page->published) {
                $this->_redirectUnpublishPage();
                return;
            }

            $this->_addPageInPathway($page->id);
            $this->addPathway(JText::_('COM_COMMUNITY_PAGES_EDIT_TITLE'));

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-pages-forms'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            // Load category tree
            $cTree = CCategoryHelper::getCategories($categories);
            $lists['categoryid'] = CCategoryHelper::getSelectList('pages', $cTree, $page->categoryid, true);

            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';
            $editor = new CEditor($editorType);

            $params = $page->getParams();
            $photopermission = ($params->get('photopermission') == PAGE_PHOTO_PERMISSION_ADMINS || $params->get('photopermission') == PAGE_PHOTO_PERMISSION_ALL ) ? 1 : 0;
            $videopermission = ($params->get('videopermission') == PAGE_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == PAGE_VIDEO_PERMISSION_ADMINS ) ? 1 : 0;
            $eventpermission = ($params->get('eventpermission') == PAGE_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ADMINS ) ? 1 : 0;

            $page->discussordering = 0;
            $page->pagerecentphotos = $jinput->post->getInt('pagerecentphotos', $params->get('pagerecentphotos', PAGE_PHOTO_RECENT_LIMIT));
            $page->pagerecentvideos = $jinput->post->getInt('pagerecentvideos', $params->get('pagerecentvideos', PAGE_VIDEO_RECENT_LIMIT));
            $page->photopermission = $jinput->post->getInt('photopermission-admin', $photopermission);
            $page->videopermission = $jinput->post->getInt('videopermission-admin', $videopermission);

            if ($mainframe->get('sef')) {
                $juriRoot = JURI::root(false);
                $juriPathOnly = JURI::root(true);
                $juriPathOnly = rtrim($juriPathOnly, '/');
                $pageURL = rtrim(str_replace($juriPathOnly, '', $juriRoot), '/');

                $pageURL .= CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false);

                if (!$page->alias) {
                    $alias = JFilterOutput::stringURLSafe($page->name);
                } else {
                    $alias = JFilterOutput::stringURLSafe($page->alias);
                }

                $inputHTML = '<input id="alias" name="alias" type="text" class="joms-input" value="' . $alias . '" />';
                $prefixURL = str_replace($alias, $inputHTML, $pageURL);

                if ($prefixURL == $pageURL) {
                    $prefixURL = CString::str_ireplace($alias, $inputHTML, $pageURL);
                }
            }

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('config', $config)
                    ->set('jConfig', $jConfig)
                    ->set('prefixURL', $prefixURL)
                    ->set('lists', $lists)
                    ->set('categories', $categories)
                    ->set('page', $page)
                    ->set('params', $page->getParams())
                    ->set('isNew', false)
                    ->set('editor', $editor)
                    ->fetch('pages.forms');
        }
        
        public function mypages($userid) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $user = CFactory::getUser($userid);
            $my = CFactory::getUser();

            if(!$user->_userid){
                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages'));
            }

            // Respect profile privacy setting.
            if (!CPrivacy::isAccessAllowed($my->id, $user->id, 'user', 'privacyPagesView')) {
                echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
                return;
            }

            $title = ($my->id == $user->id) ? JText::_('COM_COMMUNITY_PAGES_MY_PAGES') : JText::sprintf('COM_COMMUNITY_PAGES_USER_TITLE', $user->getDisplayName());
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $title);

            // Add the miniheader if necessary
            if ($my->id != $user->id) {
                $this->attachMiniHeaderUser($user->id);
            }

            // Load required filterbar library that will be used to display the filtering and sorting.

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway(JText::_('COM_COMMUNITY_PAGES_MY_PAGES'), '');

            $uri = JURI::base();

            //@todo: make mypages page to contain several admin tools for owner?
            $config = CFactory::getConfig();
            $defaultSortOrder = $config->get('page_default_sort_order', 'latest');

            $pagesModel = CFactory::getModel('pages');
            $avatarModel = CFactory::getModel('avatar');
            $wallsModel = CFactory::getModel('wall');
            $activityModel = CFactory::getModel('activities');
            $discussionModel = CFactory::getModel('discussions');
            $sorted = $jinput->get->get('sort', $defaultSortOrder, 'STRING');
            // @todo: proper check with CError::assertion
            // Make sure the sort value is not other than the array keys

            $pages = $pagesModel->getPages($user->id, $sorted);
            $pagination = $pagesModel->getPagination(count($pages));

            require_once( JPATH_COMPONENT . '/libraries/activities.php');
            $act = new CActivityStream();

            // Attach additional properties that the v might have
            $pageIds = '';
            if ($pages) {
                foreach ($pages as $page) {
                    $pageIds = (empty($pageIds)) ? $page->id : $pageIds . ',' . $page->id;
                }
            }

            // Get the template for the page lists
            $pagesHTML = $this->_getPagesHTML($pages, $pagination);
            
            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_PAGES_SORT_LATEST'),
                'oldest' => JText::_('COM_COMMUNITY_PAGES_SORT_OLDEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostwalls' => JText::_('COM_COMMUNITY_PAGES_SORT_MOST_ACTIVE')
            );

            if(CFactory::getConfig()->get('show_featured')){
                $sortItems['featured'] = JText::_('COM_COMMUNITY_SORT_FEATURED');
            }

            $tmpl = new CTemplate();
            echo $tmpl->set('pagesHTML', $pagesHTML)
                    ->set('pagination', $pagination)
                    ->set('isMyPages', true)
                    ->set('my', $my)
                    ->set('user', $user)
                    ->set('title', $title)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, $defaultSortOrder))
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('pages/base');
        }

        /**
         * Method to display page creation form
         * */
        public function create($data) {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_CREATE_NEW_PAGE'));

            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $model = CFactory::getModel('pages');
            $totalPage = $model->getPagesCreationCount($my->id);

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            if (!$my->authorise('community.create', 'pages')) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_DISABLE'),'');
                return;
            }

            //initialize default value
            $page = JTable::getInstance('Page', 'CTable');
            $page->approvals = $jinput->get('approvals', '', 'INT');
            $page->unlisted = $jinput->get('unlisted', '', 'INT');
            $page->name = $jinput->post->get('name', '', 'STRING');
            $page->summary = $jinput->post->get('summary', '', 'STRING');
            $page->description = $jinput->post->get('description', '', 'RAW');
            $page->email = $jinput->post->get('email', '', 'STRING');
            $page->website = $jinput->post->get('website', '', 'STRING');
            $page->categoryid = $jinput->get('categoryid', '', 'INT');

            $params = $page->getParams();

            $photopermission = ($params->get('photopermission') == PAGE_PHOTO_PERMISSION_ADMINS || $params->get('photopermission') == PAGE_PHOTO_PERMISSION_ALL || $params->get('photopermission') == '') ? 1 : 0;
            $videopermission = ($params->get('videopermission') == PAGE_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == PAGE_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == '') ? 1 : 0;
            $eventpermission = ($params->get('eventpermission') == PAGE_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == PAGE_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == '') ? 1 : 0;

            $page->discussordering = 0;
            $page->pagerecentphotos = $jinput->post->getInt('pagerecentphotos', $params->get('pagerecentphotos', PAGE_PHOTO_RECENT_LIMIT));
            $page->pagerecentvideos = $jinput->post->getInt('pagerecentvideos', $params->get('pagerecentvideos', PAGE_VIDEO_RECENT_LIMIT));
            $page->pagerecentevents = $jinput->post->getInt('pagerecentevents', $params->get('pagerecentevents', PAGE_EVENT_RECENT_LIMIT));
            $page->photopermission = $jinput->post->getInt('photopermission-admin', $photopermission);
            $page->videopermission = $jinput->post->getInt('videopermission-admin', $videopermission);
            $page->eventpermission = $jinput->post->getInt('eventpermission-admin', $eventpermission);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-pages-form'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            // Load category tree
            $cTree = CCategoryHelper::getCategories($data->categories);
            $lists['categoryid'] = CCategoryHelper::getSelectList('pages', $cTree, $page->categoryid, true);

            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';
            $editor = new CEditor($editorType);
            $tmpl = new CTemplate();

            $jConfig = JFactory::getConfig();

            $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('config', $config)
                ->set('jConfig', $jConfig)
                ->set('lists', $lists)
                ->set('categories', $data->categories)
                ->set('page', $page)
                ->set('pageCreated', $totalPage)
                ->set('pageCreationLimit', $config->get('pagecreatelimit'))
                ->set('params', $page->getParams())
                ->set('isNew', true)
                ->set('editor', $editor);

            if ($config->get('pagecreatelimit') != 0 && ($totalPage / $config->get('pagecreatelimit') >= COMMUNITY_SHOW_LIMIT)) {
                echo $tmpl->fetch('pages.forms.limit');
            } else {
                echo $tmpl->fetch('pages.forms');
            }    
        }

        public function created() {
            $jinput = JFactory::getApplication()->input;
            $pageid = $jinput->get('pageid', 0);
            $mainframe	= JFactory::getApplication();
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $pageid, false));
        }

        /**
         * Method to display output after saving page
         *
         * @param	JTable	Page JTable object
         * */
        public function save($page) {
            $mainframe = JFactory::getApplication();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_AVATAR_UPLOAD'));

            // Load submenus
            $this->showSubmenu();

            if (!$page->id) {
                $this->addWarning('COM_COMMUNITY_PAGES_SAVE_ERROR');
                return;
            }
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_PAGES_NEW_MESSAGE', $page->name));

            $tmpl = new CTemplate();
            echo $tmpl->set('page', $page)
                    ->fetch('pages.save');
        }

        /**
         * Method to display listing of pages from the site
         * */
        public function display($data = NULL) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();

            $avatarModel = CFactory::getModel('avatar');
            $wallsModel = CFactory::getModel('wall');

            // Get category id from the query string if there are any.
            $categoryId = $jinput->getInt('categoryid', 0);
            $category = JTable::getInstance('PageCategory', 'CTable');
            $category->load($categoryId);
            
            if ($categoryId != 0) {
                $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_CATEGORIES') . ' : ' . str_replace('&amp;', '&', JText::_($this->escape($category->name))));
            } else {
                $this->addPathway(JText::_('COM_COMMUNITY_PAGES'));
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_BROWSE_TITLE'));
            }

            // If we are browing by category, add additional breadcrumb and add
            // category name in the page title
            /* begin: UNLIMITED LEVEL BREADCRUMBS PROCESSING */
            if ($category->parent == COMMUNITY_NO_PARENT) {
                $this->addPathway(JText::_($this->escape($category->name)), CRoute::_('index.php?option=com_community&view=pages&categoryid=' . $category->id));
            } else {
                // Parent Category
                $parentsInArray = array();
                $n = 0;
                $parentId = $category->id;

                $parent = JTable::getInstance('PageCategory', 'CTable');

                do {
                    $parent->load($parentId);
                    $parentId = $parent->parent;

                    $parentsInArray[$n]['id'] = $parent->id;
                    $parentsInArray[$n]['parent'] = $parent->parent;
                    $parentsInArray[$n]['name'] = JText::_($this->escape($parent->name));

                    $n++;
                } while ($parent->parent > COMMUNITY_NO_PARENT);

                for ($i = count($parentsInArray) - 1; $i >= 0; $i--) {
                    $this->addPathway($parentsInArray[$i]['name'], CRoute::_('index.php?option=com_community&view=pages&categoryid=' . $parentsInArray[$i]['id']));
                }
            }
            /* end: UNLIMITED LEVEL BREADCRUMBS PROCESSING */


            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $uri = JURI::base();

            $data = new stdClass();
            $defaultSortOrder = $config->get('page_default_sort_order', 'latest');
            $sorted = $jinput->get->get('sort', $defaultSortOrder, 'STRING');
            $limitstart = $jinput->get('limitstart', 0, 'INT');
            //cache pages categories
            $data->categories = $this->_cachedCall('getPagesCategories', array($category->id), '', array(COMMUNITY_CACHE_TAG_PAGES_CAT));

            // cache pages list.
            $user = CFactory::getUser();
            $username = $user->get('username');
            $featured = (!is_null($username) ) ? true : false;

            $pagesData = $this->_cachedCall('getShowAllPages', array($category->id, $sorted, $featured), COwnerHelper::isCommunityAdmin($my->id), array(COMMUNITY_CACHE_TAG_PAGES));
            $pagesHTML = $pagesData['HTML'];

            $tmpl = new CTemplate($this);
            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_PAGES_SORT_LATEST'),
                'oldest' => JText::_('COM_COMMUNITY_PAGES_SORT_OLDEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostwalls' => JText::_('COM_COMMUNITY_PAGES_SORT_MOST_ACTIVE')
            );

            if($config->get('show_featured')){
                $sortItems['featured'] = JText::_('COM_COMMUNITY_PAGE_SORT_FEATURED');
            }

            echo $tmpl->set('index', true)
                    ->set('categories', $data->categories)
                    ->set('availableCategories', $this->getFullPagesCategories())
                    ->set('pagesHTML', $pagesHTML)
                    ->set('config', $config)
                    ->set('category', $category)
                    ->set('categoryId', $categoryId)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, $defaultSortOrder))
                    ->set('sorted', $sorted)
                    ->set('my', $my)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('pages/base');
        }
        
        /**
         * Displays specific pages
         * */
        public function viewpage($page) {
            CWindow::load();

            $config = CFactory::getConfig();
            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            // Load appropriate models
            $pageModel = CFactory::getModel('pages');
            $wallModel = CFactory::getModel('wall');
            $userModel = CFactory::getModel('user');
            $photosModel = CFactory::getModel('photos');
            $activityModel = CFactory::getModel('activities');

            $editPage = $jinput->get->get('edit', FALSE, 'NONE');
            $editPage = ($editPage == 1) ? true : false;
            $params = $page->getParams();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', CStringHelper::escape($page->name), CStringHelper::escape(strip_tags($page->description)));
            $document->addCustomTag('<link rel="image_src" href="' . JURI::root(true) .'/'. $page->thumb . '" />');

            // @rule: Test if the page is unpublished, don't display it at all.
            if (!$page->published) {
                $this->_redirectUnpublishPage();
                return;
            }

            $page->hit();

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway(JText::sprintf('COM_COMMUNITY_PAGES_NAME_TITLE', $page->name), '');

            // Load the current browsers data
            $my = CFactory::getUser();

            // If user are invited
            $isInvited = $pageModel->isInvited($my->id, $page->id);

            // Get members list for display
            $limit = CFactory::getConfig()->get('page_sidebar_members_show_total',12);
            $approvedMembers = $pageModel->getMembers($page->id, $limit, true, false, true);
            CError::assert($approvedMembers, 'array', 'istype', __FILE__, __LINE__);

            // Is there any my friend is the member of this page?
            $join = '';
            $friendsCount = 0;
            if ($isInvited) {
                // Get the invitors
                $invitors = $pageModel->getInvitors($my->id, $page->id);

                if (count($invitors) == 1) {
                    $user = CFactory::getUser($invitors[0]->creator);
                    $join = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                } else {
                    for ($i = 0; $i < count($invitors); $i++) {
                        $user = CFactory::getUser($invitors[$i]->creator);

                        if (($i + 1 ) == count($invitors)) {
                            $join .= ' ' . JText::_('COM_COMMUNITY_AND') . ' ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                        } else {
                            $join .= ', ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                        }
                    }
                }

                // Get users friends in this page
                $friendsCount = $pageModel->getFriendsCount($my->id, $page->id);
            }

            // Get list of unapproved members
            $unapproved = $pageModel->getMembers($page->id, null, false);
            $unapproved = count($unapproved);

            // Test if the current user is admin
            $isAdmin = $pageModel->isAdmin($my->id, $page->id);

            // Test if the current browser is a member of the page
            $isMember = $pageModel->isMember($my->id, $page->id);
            $waitingApproval = false;

            // Test if the current user is banned from this page
            $isBanned = $page->isBanned($my->id);

            // Attach avatar of the member
            // Pre-load multiple users at once
            $userids = array();
            $limitloop = $limit;
            foreach ($approvedMembers as $uid) {
                if ($limitloop-- < 1){
                    break;
                }
                $userids[] = $uid->id;
            }
            CFactory::loadUsers($userids);

            $limitloop = $limit;
            for ($i = 0; ($i < count($approvedMembers)); $i++) {
                if ($limitloop-- < 1){
                    break;
                }
                $row = $approvedMembers[$i];
                $approvedMembers[$i] = CFactory::getUser($row->id);
            }
            
            $membersCount = $pageModel->getMembersCount($page->id);

            if ($isBanned) {
                $mainframe = JFactory::getApplication();
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_MEMBER_BANNED'), 'error');
                return;
            }

            // If I have tried to join this page, but not yet approved, display a notice
            if ($pageModel->isWaitingAuthorization($my->id, $page->id)) {
                $waitingApproval = true;
            }

            // Get like
            $likes = new CLike();
            $isUserLiked = false;

            if ($isLikeEnabled = $likes->enabled('pages')) {
                $isUserLiked = $likes->userLiked('pages', $page->id, $my->id);
            }

            $totalLikes = $likes->getLikeCount('pages', $page->id);

            // Get album data
            $albumData = $this->_cachedCall('_getAlbums', array($params, $page->id), $page->id, array(COMMUNITY_CACHE_TAG_PAGES_DETAIL));
            $albums = $albumData['data'];
            $totalAlbums = $albumData['total'];

            // Get video data
            $videoData = $this->_getVideos($params, $page->id);
            $videos = $videoData['data'];
            $totalVideos = $videoData['total'];

            // Get poll data
            $pollData = $this->_getPolls($params, $page->id);
            $polls = $pollData['data'];
            $totalPolls = $pollData['total'];

            $tmpl = new CTemplate();

            $isMine = ($my->id == $page->ownerid);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            if ($page->approvals == '1' && !$isMine && !$isMember && !CFactory::getUser()->authorise('community.pageeditstate', 'com_community') && !CFactory::getUser()->authorise('community.pageedit', 'com_community') && !CFactory::getUser()->authorise('community.pagedelete', 'com_community')) {
                $this->addWarning(JText::_('COM_COMMUNITY_PAGES_PRIVATE_NOTICE'));
            }

            $eventsModel = CFactory::getModel('Events');
            $tmpEvents = $eventsModel->getPageEvents($page->id, $params->get('pagerecentevents', PAGE_EVENT_RECENT_LIMIT));
            $totalEvents = $eventsModel->getTotalPageEvents($page->id);

            $events = array();
            foreach ($tmpEvents as $event) {
                $table = JTable::getInstance('Event', 'CTable');
                $table->bind($event);
                $events[] = $table;
            }

            $allowCreateEvent = CPageHelper::allowCreateEvent($my->id, $page->id);

            // Upgrade wall to stream @since 2.5
            if (!$params->get('stream', FALSE)) {
                $page->upgradeWallToStream();
            }

            $page->getAvatar();
            $page->defaultAvatar = empty($page->avatar);

            // Find avatar album.
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $album->isAvatarAlbumExists($page->id, 'page');
            $page->avatarAlbum = $albumId ? $albumId : false;

            // Check if default cover is used.
            $page->defaultCover = empty($page->cover) ? true : false;

            // Cover position.
            $page->coverPostion = $params->get('coverPosition', '');
            if ( strpos( $page->coverPostion, '%' ) === false )
                $page->coverPostion = 0;

            // Find cover album and photo.
            $page->coverAlbum = false;
            $page->coverPhoto = false;
            $album = JTable::getInstance('Album', 'CTable');
            $albumId = $album->isCoverExist('page', $page->id);
            if ($albumId) {
                $album->load($albumId);
                $page->coverAlbum = $albumId;
                $page->coverPhoto = $album->photoid;
            }

            // Add custom stream
            $activities = new CActivities();
            $streamHTML = $activities->getOlderStream(1000000000, 'active-page', $page->id);
            $totalStream = $activityModel->getTotalActivities(array("`pageid` = '{$page->id}'"));

            $creators = array();
            $creators[] = CUserStatusCreator::getMessageInstance();
            if (( ($isAdmin || $isSuperAdmin) && $params->get('photopermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('photopermission') == 2) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getPhotoInstance();
            if (( ($isAdmin || $isSuperAdmin) && $params->get('videopermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('videopermission') == 2) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getVideoInstance();
            if (($allowCreateEvent || $isSuperAdmin ) && $config->get('page_events') && $config->get('enableevents') && ($config->get('createevents') ) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getEventInstance();
            
            $status = new CUserStatus($page->id, 'pages', $creators);

            // Get Event Admins
            $pageAdmins = $page->getAdmins(12, CC_RANDOMIZE);
            $adminsInArray = array();

            // Attach avatar of the admin
            for ($i = 0; ($i < count($pageAdmins)); $i++) {
                $row = $pageAdmins[$i];
                $admin = CFactory::getUser($row->id);
                array_push($adminsInArray, '<a href="' . CUrlHelper::userLink($admin->id) . '">' . $admin->getDisplayName() . '</a>');
            }

            $totalPhotos = 0;

            $allAlbumData = $this->_cachedCall('_getAlbums', array($params, $page->id, true), $page->id, array(COMMUNITY_CACHE_TAG_PAGES_DETAIL));

            foreach ($allAlbumData['data'] as $album) {
                $albumParams = new CParameter($album->params);
                $totalPhotos = $totalPhotos + $albumParams->get('count');
            }

            $adminsList = ltrim(implode(', ', $adminsInArray), ',');

            $showMoreActivity = ($totalStream <= $config->get('maxactivities')) ? false : true;

            $pagesModel = CFactory::getModel('pages');
            $bannedMembers = $pagesModel->getBannedMembers($page->id);

            /* Opengraph */
            CHeadHelper::addOpengraph('og:image', $page->getAvatar('avatar'), true);
            CHeadHelper::addOpengraph('og:image', $page->getCover(), true);

            $featured = new CFeatured(FEATURED_PAGES);
            $featuredList = $featured->getItemIds();

            $page->title = $page->name;
            
            $reviews = JTable::getInstance('Rating', 'CTable');
            $reviewsCount = $reviews->getUserRatingCount('pages', $page->id);
            $ratingValue = $reviews->getRatingResult('pages', $page->id);

            echo $tmpl->setMetaTags('page', $page)
                    ->set('streamHTML', $streamHTML)
                    ->set('showMoreActivity', $showMoreActivity)
                    ->set('status', $status)
                    ->set('events', $events)
                    ->set('totalEvents', $totalEvents)
                    ->set('showEvents', $config->get('page_events') && $config->get('enableevents') && $params->get('eventpermission',1) >= 1)
                    ->set('showPhotos', ( $params->get('photopermission') != -1 ) && $config->get('enablephotos') && $config->get('pagephotos'))
                    ->set('showVideos', ( $params->get('videopermission') != -1 ) && $config->get('enablevideos') && $config->get('pagevideos'))
                    ->set('showPolls', ( $params->get('pollspermission') != -1 ) && $config->get('enablepolls') && $config->get('page_polls'))
                    ->set('eventPermission', $params->get('eventpermission'))
                    ->set('photoPermission', $params->get('photopermission'))
                    ->set('videoPermission', $params->get('videopermission'))
                    ->set('pollspermission', $params->get('pollspermission'))
                    ->set('videos', $videos)
                    ->set('totalVideos', $totalVideos)
                    ->set('albums', $albums)
                    ->set('editPage', $editPage)
                    ->set('waitingApproval', $waitingApproval)
                    ->set('config', $config)
                    ->set('isMine', $isMine)
                    ->set('isAdmin', $isAdmin)
                    ->set('isSuperAdmin', $isSuperAdmin)
                    ->set('isMember', $isMember)
                    ->set('isInvited', $isInvited)
                    ->set('friendsCount', $friendsCount)
                    ->set('join', $join)
                    ->set('unapproved', $unapproved)
                    ->set('membersCount', $membersCount)
                    ->set('ratingValue', $ratingValue)
                    ->set('reviewsCount', $reviewsCount)
                    ->set('page', $page)
                    ->set('totalVideos', $totalVideos)
                    ->set('members', $approvedMembers)
                    ->set('isBanned', $isBanned)
                    ->set('totalBannedMembers', count($bannedMembers) )
                    ->set('isPrivate', $page->approvals)
                    ->set('limit', $limit)
                    ->set('adminsList', $adminsList)
                    /* Set notification counts */
                    ->set('alertNewStream', $my->count('page_activity_' . $page->id) != $totalStream)
                    ->set('isUserLiked', $isUserLiked)
                    ->set('totalLikes', $totalLikes)
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('totalPhotos', $totalPhotos)
                    ->set('totalAlbums', $totalAlbums)
                    ->set('totalPolls', $totalPolls)
                    ->set('profile', $my)
                    ->set('featuredList', $featuredList)
                    ->fetch('pages/single');

            // Update stream count cache, can only set this after we've set the alert aove
            if($my->id){
                $my->setCount('page_activity_' . $page->id, $totalStream);
            }
        }

        public function getPagesCategories($category) {

            $model = CFactory::getModel('pages');
            $categories = $model->getCategoriesCount();

            $categories = CCategoryHelper::getParentCount($categories, $category);

            return $categories;
        }

        /**
         * List all the category including the children and format it
         */
        public function getFullPagesCategories($id = 0, $level = 0, $categoryList = array()){
            $model = CFactory::getModel('pages');
            $mainCategories = $model->getCategories($id);

            if(count($mainCategories) > 0){
                foreach($mainCategories as $category){
                    $prefix = '';
                    for($i = 0; $i < $level; $i++){
                        $prefix = $prefix.'-'; // this will add the - in front of the category name
                    }

                    $category->name = $prefix.' '.JText::_($category->name);
                    $categoryList[] = $category;
                    $categoryList = $this->getFullPagesCategories($category->id, $level+1, $categoryList);
                }
            }

            return $categoryList;
        }

        public function getShowAllPages($category, $sorted) {
            $model = CFactory::getModel('pages');

            // Get page in category and it's children.
            $categories = $model->getAllCategories();
            $categoryIds = CCategoryHelper::getCategoryChilds($categories, $category);
            if ((int) $category > 0) {
                $categoryIds[] = (int) $category;
            }

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $data = new StdClass;
            $data->pages = $model->getAllPages($categoryIds, $sorted);

            // Get pagination object
            $data->pagination = $model->getPagination();

            // Get the template for the page lists
            $pagesHTML['HTML'] = $this->_getPagesHTML($data->pages, $data->pagination);

            return $pagesHTML;
        }

        public function _getPagesHTML($tmpPages, $tmpPagination = NULL) {
            $config = CFactory::getConfig();
            $tmpl = new CTemplate();
            $featured = new CFeatured(FEATURED_PAGES);
            $featuredList = $featured->getItemIds();

            $pages = array();

            if ($tmpPages) {
                foreach ($tmpPages as $row) {
                    $page = JTable::getInstance('Page', 'CTable');
                    $page->bind($row);
                    $page->updateStats(); //ensure that stats are up-to-date
                    $page->description = CStringHelper::clean(JHTML::_('string.truncate', $page->description, $config->get('tips_desc_length')));
                    $pages[] = $page;
                }
                unset($tmpPages);
            }

            $pagesHTML = $tmpl->set('showFeatured', $config->get('show_featured'))
                    ->set('featuredList', $featuredList)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('pages', $pages)
                    ->set('pagination', $tmpPagination)
                    ->fetch('pages/list');

            return $pagesHTML;
        }

        /**
         * Return the video list for viewPage display
         */
        protected function _getVideos($params, $pageid) {
            $result = array();
            $videoModel = CFactory::getModel('videos');
            $tmpVideos = $videoModel->getPageVideos($pageid, '', 0, '');
            $videos = array();

            if ($tmpVideos) {
                foreach ($tmpVideos as $videoEntry) {
                    $video = JTable::getInstance('Video', 'CTable');
                    $video->bind($videoEntry);
                    $videos[] = $video;
                }
            }

            $totalVideos = $videoModel->total ? $videoModel->total : 0;
            $result['total'] = $totalVideos;
            $result['data'] = $videos;
            return $result;
        }
        
        /**
         * Return the poll list for viewPage display
         */
        protected function _getPolls($params, $pageid) {
            $result = array();
            $pollModel = CFactory::getModel('polls');
            $tmpPolls = $pollModel->getAllPolls(null, null, null, null, false, true, null, null, $pageid);
            
            $polls = array();
            $totalPolls = 0; 

            if ($tmpPolls) {
                foreach ($tmpPolls as $pollEntry) {
                    $poll = JTable::getInstance('Poll', 'CTable');
                    $poll->bind($pollEntry);
                    $polls[] = $poll;

                    $totalPolls++;
                }
            }

            $result['total'] = $totalPolls;
            $result['data'] = $polls;
            return $result;
        }

        protected function _getAlbums($params, $pageid, $ignoreRecentPhotos = false) {
            $result = array();

            $photosModel = CFactory::getModel('photos');

            if(!$ignoreRecentPhotos){
                $albums = $photosModel->getPageAlbums($pageid, true, false, $params->get('pagerecentphotos', PAGE_PHOTO_RECENT_LIMIT), false, '', array('page.avatar', 'page.Cover'));
            }else{
                $albums = $photosModel->getPageAlbums($pageid, false, false);
            }

            $db = JFactory::getDBO();
            $where = 'WHERE a.' . $db->quoteName('pageid') . ' = ' . $db->quote($pageid);
            $where .= ' AND a.' . $db->quoteName('type') . ' != ' . $db->quote('page.avatar');
            $where .= ' AND a.' . $db->quoteName('type') . ' != ' . $db->quote('page.Cover');

            $totalAlbums = $photosModel->getAlbumCount($where);

            $result['total'] = $totalAlbums;
            $result['data'] = $albums;

            return $result;
        }

        public function banlist($data) {
            $this->viewmembers($data);
        }

        public function viewmembers($data) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $pagesModel = CFactory::getModel('pages');
            $friendsModel = CFactory::getModel('friends');
            $userModel = CFactory::getModel('user');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $type = $jinput->get->get('approve', '', 'NONE');
            $page = JTable::getInstance('Page', 'CTable');
            $list = $jinput->get->get('list', '', 'NONE');

            if (!$page->load($data->id)) {
                echo CSystemHelper::showErrorPage();
                return;
            }

            // @rule: Test if the page is unpublished, don't display it at all.
            if (!$page->published) {
                $this->_redirectUnpublishPage();
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PAGES_MEMBERS_TITLE', $page->name));

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway($page->name, CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id));
            $this->addPathway(JText::_('COM_COMMUNITY_MEMBERS'));


            $isSuperAdmin = COwnerHelper::isCommunityAdmin();
            $isAdmin = $pagesModel->isAdmin($my->id, $page->id);
            $isMember = $page->isMember($my->id);
            $isMine = ($my->id == $page->ownerid);
            $isBanned = $page->isBanned($my->id);

            if ($page->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin) {
                $this->noAccess(JText::_('COM_COMMUNITY_PAGES_PRIVATE_NOTICE'));
                return;
            }

            switch ($list) {
                case COMMUNITY_PAGE_ADMIN :
                    $members = $pagesModel->getAdmins($data->id);
                    $title = JText::_('COM_COMMUNITY_PAGE_MEMBERS');
                    break;
                case COMMUNITY_PAGE_BANNED :
                    $members = $pagesModel->getBannedMembers($data->id);
                    $title = JText::_('COM_COMMUNITY_PAGES_BANNED_MEMBERS');
                    break;
                default :
                    $title = JText::_('COM_COMMUNITY_PAGE_MEMBERS');
                    if (!empty($type) && ( $type == '1' )) {
                        $members = $pagesModel->getMembers($data->id, 0, false);
                    } else {
                        $members = $pagesModel->getMembers($data->id, 0, true, false, SHOW_PAGE_ADMIN);
                    }
            }

            if($type == 1){
                $title = JTEXT::_('COM_COMMUNITY_PAGES_MEMBERS_PENDING_APPROVAL_TITLE');
            }

            // Attach avatar of the member
            // Pre-load multiple users at once
            $userids = array();
            foreach ($members as $uid) {
                $userids[] = $uid->id;
            }
            CFactory::loadUsers($userids);

            $membersList = array();
            foreach ($members as $member) {
                $user = CFactory::getUser($member->id);

                $user->friendsCount = $user->getFriendCount();
                $user->approved = $member->approved;
                $user->isMe = ( $my->id == $member->id ) ? true : false;
                $user->isAdmin = $pagesModel->isAdmin($user->id, $page->id);
                $user->isOwner = ( $member->id == $page->ownerid ) ? true : false;

                // Check user's permission
                $pagemember = JTable::getInstance('PageMembers', 'CTable');
                $keys['pageId'] = $page->id;
                $keys['memberId'] = $member->id;
                $pagemember->load($keys);
                $user->isBanned = ( $pagemember->permissions == COMMUNITY_PAGE_BANNED ) ? true : false;

                $membersList[] = $user;
            }
            // Featured
            $featured = new CFeatured(FEATURED_USERS);
            $featuredList = $featured->getItemIds();

            $pagination = $pagesModel->getPagination();

            $tmpl = new CTemplate();
            echo $tmpl->set('members', $membersList)
                    ->set('list', $list)
                    ->set('type', $type)
                    ->set('title', $title)
                    ->set('isMine', $pagesModel->isCreator($my->id, $page->id))
                    ->set('isAdmin', $isAdmin)
                    ->set('isMember', $isMember)
                    ->set('isSuperAdmin', $isSuperAdmin)
                    ->set('pagination', $pagination)
                    ->set('pageid', $page->id)
                    ->set('my', $my)
                    ->set('config', $config)
                    ->set('page', $page)
                    ->set('submenu', $this->showSubmenu(false))
                    ->set('featuredList', $featuredList)
                    ->fetch('pages.viewmembers');
        }
        
        public function viewreviews($data) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $pagesModel = CFactory::getModel('pages');
            $friendsModel = CFactory::getModel('friends');
            $userModel = CFactory::getModel('user');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $type = $jinput->get->get('approve', '', 'NONE');
            $page = JTable::getInstance('Page', 'CTable');
            $list = $jinput->get->get('list', '', 'NONE');

            if (!$page->load($data->id)) {
                echo CSystemHelper::showErrorPage();
                return;
            }

            // @rule: Test if the page is unpublished, don't display it at all.
            if (!$page->published) {
                $this->_redirectUnpublishPage();
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_PAGES_MEMBERS_TITLE', $page->name));

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway($page->name, CRoute::_('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id));
            $this->addPathway(JText::_('COM_COMMUNITY_MEMBERS'));


            $isSuperAdmin = COwnerHelper::isCommunityAdmin();
            $isAdmin = $pagesModel->isAdmin($my->id, $page->id);
            $isMember = $page->isMember($my->id);
            $isMine = ($my->id == $page->ownerid);
            $isBanned = $page->isBanned($my->id);

            if ($page->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin) {
                $this->noAccess(JText::_('COM_COMMUNITY_PAGES_PRIVATE_NOTICE'));
                return;
            }

            $reviews = $pagesModel->getReviews($data->id);

            $title = JTEXT::_('COM_COMMUNITY_PAGES_REVIEWS');

            // Attach avatar of the member
            // Pre-load multiple users at once
            $userids = array();
            foreach ($reviews as $uid) {
                $userids[] = $uid->userid;
            }
            CFactory::loadUsers($userids);

            $reviewsList = array();
            foreach ($reviews as $review) {
                $user = CFactory::getUser($review->userid);
                $user->isMe = ($my->id == $review->userid) ? true : false;
                $user->isAdmin = $pagesModel->isAdmin($user->id, $page->id);
                $user->isOwner = ($review->userid == $page->ownerid) ? true : false;
                $user->userid = $review->userid;
                $user->title = $review->title;
                $user->review = $review->review;
                $user->rating = $review->rating;
                $user->created = $review->created;
                $user->reviewid = $review->id;

                $reviewsList[] = $user;
            }

            $pagination = $pagesModel->getPagination();
            
            $rating = JTable::getInstance('Rating', 'CTable');
            $isRated = $rating->isRated('pages', $page->id, $my->id);

            $tmpl = new CTemplate();
            echo $tmpl->set('reviews', $reviewsList)
                    ->set('list', $list)
                    ->set('type', $type)
                    ->set('title', $title)
                    ->set('isMine', $pagesModel->isCreator($my->id, $page->id))
                    ->set('isAdmin', $isAdmin)
                    ->set('isMember', $isMember)
                    ->set('isSuperAdmin', $isSuperAdmin)
                    ->set('isBanned', $isBanned)
                    ->set('pagination', $pagination)
                    ->set('pageid', $page->id)
                    ->set('my', $my)
                    ->set('config', $config)
                    ->set('page', $page)
                    ->set('isRated', $isRated)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('pages.viewreviews');
        }

        public function singleActivity($activity)
        {
            // we will determine all the user settings based on the activity viewed
            $my = CFactory::getUser();
            $userId = $activity->actor;

            if($activity->id == 0 || empty($activity->id)){
                //redirect this to error : no activity found
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'warning');
            }

            echo CMiniHeader::showPageMiniHeader($activity->pageid);

            $document = JFactory::getDocument();
            $document->setTitle(JHTML::_('string.truncate', $activity->title, 75));

            CHeadHelper::setDescription(JHTML::_('string.truncate', $activity->title, 300, true));
            //see if the user has blocked each other
            $getBlockStatus = new blockUser();
            $blocked = $getBlockStatus->isUserBlocked($userId, 'profile');
            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_ERROR_ACTIVITY_NOT_FOUND'), 'warning');
            }

            //everything is fine, lets get to the activity
            echo $this->_getNewsfeedHTML();
        }

        private function _getNewsfeedHTML() {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userId = $jinput->get('userid', $my->id, 'INT');

            return CActivities::getActivitiesByFilter('active-profile', $userId, 'profile', true, array('show_featured'=>true));
        }

        public function search() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_SEARCH_TITLE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway(JText::_("COM_COMMUNITY_SEARCH"), '');

            $search = $jinput->get('search', '', 'STRING');
            $catId = $jinput->get('catid', '', 'INT');
            $pages = '';
            $pagination = null;
            $posted = false;
            $count = 0;

            $model = CFactory::getModel('pages');

            $categories = $model->getCategories();

            // Test if there are any post requests made
            if ((!empty($search) || !empty($catId))) {
                JSession::checkToken('get') or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-pages-search'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $posted = true;

                    $pages = $model->getAllPages($catId, null, $search);
                    $pagination = $model->getPagination();
                    $count = count($pages);
                }
            }

            // Get the template for the group lists
            $pagesHTML = $this->_getPagesHTML($pages, $pagination);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-pages-search'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $searchLinks = parent::getAppSearchLinks('pages');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('posted', $posted)
                    ->set('pagesCount', $count)
                    ->set('pagesHTML', $pagesHTML)
                    ->set('search', $search)
                    ->set('categories', $categories)
                    ->set('catId', $catId)
                    ->set('searchLinks', $searchLinks)
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('pages.search');
        }

        public function myinvites() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $userId = $jinput->get('userid', '', 'INT');

            $config = CFactory::getConfig();
            // Load required filterbar library that will be used to display the filtering and sorting.
            $document = JFactory::getDocument();

            $this->addPathway(JText::_('COM_COMMUNITY_PAGES'), CRoute::_('index.php?option=com_community&view=pages'));
            $this->addPathway(JText::_('COM_COMMUNITY_PAGES_PENDING_INVITES'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PAGES_PENDING_INVITES'));

            $feedLink = CRoute::_('index.php?option=com_community&view=pages&task=mypages&userid=' . $userId . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_PENDING_INVITATIONS_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $my = CFactory::getUser();
            $model = CFactory::getModel('pages');
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');

            $rows = $model->getPageInvites($my->id);
            $pagination = $model->getPagination(count($rows));
            $pages = array();
            $ids = '';

            if ($rows) {
                foreach ($rows as $row) {
                    $table = JTable::getInstance('Page', 'CTable');
                    $table->load($row->pageid);
                    $table->description = CStringHelper::clean(JHTML::_('string.truncate', $table->description, $config->get('tips_desc_length')));
                    $pages[] = $table;
                    $ids = (empty($ids)) ? $table->id : $ids . ',' . $table->id;
                }
            }
            
            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_PAGES_SORT_LATEST'),
                'oldest' => JText::_('COM_COMMUNITY_PAGES_SORT_OLDEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostwalls' => JText::_('COM_COMMUNITY_PAGES_SORT_MOST_ACTIVE')
            );

            $defaultSortOrder = $config->get('page_default_sort_order', 'latest');

            $tmpl = new CTemplate();
            echo $tmpl->set('pages', $pages)
                    ->set('showFeatured', $config->get('show_featured'))
                    ->set('pagination', $pagination)
                    ->set('count', $pagination->total)
                    ->set('my', $my)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, $defaultSortOrder))
                    ->set('submenu', $this->showSubmenu(false))
                    ->fetch('pages.myinvites');
        }
    }
}
