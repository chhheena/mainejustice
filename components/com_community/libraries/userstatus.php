<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');


class CUserStatus
{

    private $creators = null;
    public $target = '';
    private $type = '';

    /**
     *
     * @param type $target
     * @param type $type
     * @param type $creators
     */
    public function __construct($target = '', $type = 'profile', $creators = null)
    {
        $my = CFactory::getUser();
        $this->type = $type;
        $this->target  = (empty($target)) ? $my->id : $target;

        if (is_array($creators)) {
            foreach ($creators as $row) {
                $this->addCreator($row);
            }
        }
    }

    public function addCreator($creator)
    {
        $this->creators[] =  $creator;

        return $creator;
    }

    public function render($return = false)
    {
        // Access check: we dont even need to render the user status if this is a guest or not allowed by ACL
        if (!CFactory::getUser()->id || !CFactory::getUser()->authorise('community.postcommentcreate', 'com_community')) {
            return false;
        }

        $my        = CFactory::getUser();
        $albumModel = CFactory::getModel('Photos');

        $excludedAlbumType = array(
            'profile.avatar', 'page.avatar', 'group.avatar', 'event.avatar',
            'page.Cover', 'group.Cover', 'profile.Cover', 'event.Cover', 'profile.gif', 'event.gif', 'page.gif', 'group.gif', 'profile.chat'
        );

        if ($this->type == 'pages') {
            $album = $albumModel->getPageAlbums($this->target, false, false, '', false, '', $excludedAlbumType);
        } elseif ($this->type == 'groups') {
            $album = $albumModel->getGroupAlbums($this->target, false, false, '', false, '', $excludedAlbumType);
        } elseif ($this->type == 'events') {
            $album = $albumModel->getEventAlbums($this->target, false, false, '', false, '', $excludedAlbumType);
        } else {
            $album = $albumModel->getProfileAlbums($my->id, false, true);
            if (!$album) {
                $albumTable = JTable::getInstance('Album', 'CTable');
                $now = new JDate();
                $albumTable->creator = $my->id;
                $albumTable->created = $now->toSql();
                $albumTable->type = 'user';
                $albumTable->default = '1';
                $albumTable->pageid = 0;
                $albumTable->groupid = 0;
                $albumTable->eventid = 0;
                $albumTable->name = JText::sprintf('COM_COMMUNITY_PROFILE_DEFAULT_ALBUM_NAME');

                require_once JPATH_ROOT . '/components/com_community/controllers/photos.php';
                $handler = new CommunityControllerPhotoUserHandler();
                $albumPath = $handler->getAlbumPath($albumTable->id);
                $albumPath = CString::str_ireplace(JPATH_ROOT . '/', '', $albumPath);
                $albumPath = CString::str_ireplace('\\', '/', $albumPath);

                $albumTable->path = $albumPath;
                $albumTable->store();
            }

            $album = $albumModel->getProfileAlbums($my->id, false, true);
        }

        $this->permission = new stdClass();

        $this->permission->enablefiles = false;
        $this->permission->enablepolls = false;

        $this->permission->enablephotos = (CFactory::getConfig()->get("enablephotos") && CFactory::getUser()->authorise('community.photocreate', 'com_community')) ? 1 : 0;

        $this->permission->enablevideos = (CFactory::getConfig()->get("enablevideos") && CFactory::getUser()->authorise('community.videocreate', 'com_community')) ? 1 : 0;

        $this->permission->enablevideosupload = (CFactory::getConfig()->get("enablevideosupload") && CFactory::getUser()->authorise('community.videocreate', 'com_community')) ? 1 : 0;

        $this->permission->enableevents = (CFactory::getConfig()->get("enableevents") && $my->canCreateEvents());

        if ($this->type == 'pages') {
            $page = JTable::getInstance('Page', 'CTable');
            $page->load($this->target);
            $params = $page->getParams();
            $pageModel = CFactory::getModel('pages');
            $isAdmin = $pageModel->isAdmin($my->id, $this->target);
            $isMember = $pageModel->isMember($my->id, $this->target);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            if ((($isAdmin || $isSuperAdmin) && $params->get('filesharingpermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('filesharingpermission') == 2)) {
                if (CFactory::getUser()->authorise('community.filesharingcreate', 'com_community')) {
                    $this->permission->enablefiles = CFactory::getConfig()->get("file_sharing_page", "0");
                }
            }

            if ((($isAdmin || $isSuperAdmin) && $params->get('pollspermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('pollspermission') == 2)) {
                if (CFactory::getUser()->authorise('community.pollcreate', 'com_community')) {
                    $this->permission->enablepolls = CFactory::getConfig()->get("page_polls", "0");
                }
            }
        } elseif ($this->type == 'groups') {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($this->target);
            $params = $group->getParams();
            $groupModel = CFactory::getModel('groups');
            $isAdmin = $groupModel->isAdmin($my->id, $this->target);
            $isMember = $groupModel->isMember($my->id, $this->target);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            if ((($isAdmin || $isSuperAdmin) && $params->get('filesharingpermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('filesharingpermission') == 2)) {
                if (CFactory::getUser()->authorise('community.filesharingcreate', 'com_community')) {
                    $this->permission->enablefiles = CFactory::getConfig()->get("file_sharing_group", "0");
                }
            }

            if ((($isAdmin || $isSuperAdmin) && $params->get('pollspermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('pollspermission') == 2)) {
                if (CFactory::getUser()->authorise('community.pollcreate', 'com_community')) {
                    $this->permission->enablepolls = CFactory::getConfig()->get("group_polls", "0");
                }
            }
        } elseif ($this->type == 'events') {
            $event = JTable::getInstance('Event', 'CTable');
            $event->load($this->target);
            $params = new CParameter($event->params);
            $isAdmin = $event->isAdmin($my->id);
            $isMember = $event->isMember($my->id);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            if ((($isAdmin || $isSuperAdmin) && $params->get('filesharingpermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('filesharingpermission') == 2)) {
                if (CFactory::getUser()->authorise('community.filesharingcreate', 'com_community')) {
                    $this->permission->enablefiles = CFactory::getConfig()->get("file_sharing_event", "0");
                }
            }

            if ((($isAdmin || $isSuperAdmin) && $params->get('pollspermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('pollspermission') == 2)) {
                if (CFactory::getUser()->authorise('community.pollcreate', 'com_community')) {
                    $this->permission->enablepolls = CFactory::getConfig()->get("event_polls", "0");
                }
            }
        } else {
            if (CFactory::getUser()->authorise('community.filesharingcreate', 'com_community')) {
                $this->permission->enablefiles = CFactory::getConfig()->get("file_sharing_activity", "0");
            }

            $this->permission->enablepolls = (CFactory::getConfig()->get("enablepolls") && $my->canCreatePolls());
        }

        if ($this->type == 'profile' && $this->target != $my->id) {
            $this->permission->enableevents = false;
            $this->permission->enablepolls = false;
        }

        $moodsModel = CFactory::getModel('Moods');
        $moods = $moodsModel->getMoods();
        $publishedMoods = array();

        if (count($moods) > 0) {
            foreach ($moods as $key => $mood) {
                if ($mood->published) {
                    $publishedMoods[$key] = $mood;
                }
            }
        }

        $backgroundsModel = CFactory::getModel('Backgrounds');
        $backgrounds = $backgroundsModel->getBackgrounds();
        $publishedBackgrounds = array();

        if (count($backgrounds) > 0) {
            foreach ($backgrounds as $key => $background) {
                if ($background->published) {
                    $publishedBackgrounds[$key] = $background;
                }
            }
        }

        if ($my->id && is_array($this->creators)) {
            $tmpl = new CTemplate();
            $html = $tmpl->set('my', $my)
                ->set('target', $this->target)
                ->set('type', $this->type)
                ->set('creators', $this->creators)
                ->set('album', $album)
                ->set('permission', $this->permission)
                ->set('moods', $publishedMoods)
                ->set('backgrounds', $publishedBackgrounds)
                ->fetch('status.form');

            // Some of the creator might need custom url replacement
            // Take a look at status.photo.php template for example
            $group_url = ($this->type == 'groups') ? CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component&groupid=' . $this->target) : CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component');
            $html = str_replace('{url}', $group_url, $html);
            if ($return) return $html;
            echo $html;

            $tmpl = new CTemplate();
            $html = $tmpl->set('my', $my)
                ->set('target', $this->target)
                ->set('type', $this->type)
                ->set('creators', $this->creators)
                ->set('album', $album)
                ->set('permission', $this->permission)
                ->set('moods', $publishedMoods)
                ->set('backgrounds', $publishedBackgrounds)
                ->fetch('status.form.new');

            // Some of the creator might need custom url replacement
            // Take a look at status.photo.php template for example
            $group_url = ($this->type == 'groups') ? CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component&groupid=' . $this->target) : CRoute::_('index.php?option=com_community&view=photos&task=ajaxPreview&no_html=1&tmpl=component');
            $html = str_replace('{url}', $group_url, $html);
            if ($return) return $html;
            echo $html;
        }
    }
}

class CUserStatusCreator
{

    public $type = '';
    public $class = '';
    public $title = '';
    public $html = '';

    public function __construct($type = null)
    {
        $this->type = $type;
        $this->class = 'type-' . $type;
    }

    static function getPhotoInstance($groupid = null)
    {
        $template    =   new CTemplate();
        $creator        = new CUserStatusCreator('photo');
        $creator->title = JText::_('COM_COMMUNITY_PHOTOS');
        $template->set('groupid', $groupid);
        $creator->html  = $template->fetch('status.photo');
        return $creator;
    }

    static function getVideoInstance()
    {
        $template    =   new CTemplate();
        $creator        = new CUserStatusCreator('video');
        $creator->title = JText::_('COM_COMMUNITY_VIDEOS');
        $creator->html  = $template->fetch('status.video');
        return $creator;
    }


    static function getMessageInstance()
    {
        $template    =   new CTemplate();
        $creator        =   new CUserStatusCreator('message');
        $creator->title =   JText::_('COM_COMMUNITY_MESSAGE');
        $creator->html  =   $template->fetch('status.message');
        return $creator;
    }

    static function getEventInstance()
    {
        $template    =   new CTemplate();

        $my     = CFactory::getUser();

        //CFactory::load( 'helpers' , 'event' );
        $dateSelection = CEventHelper::getDateSelection();

        $model        = CFactory::getModel('events');
        $categories    = $model->getCategories();

        // Load category tree

        $cTree    = CCategoryHelper::getCategories($categories);
        $lists['categoryid']    =   CCategoryHelper::getSelectList('events', $cTree);

        $template->set('startDate', $dateSelection->startDate);
        $template->set('endDate', $dateSelection->endDate);
        $template->set('startHourSelect', $dateSelection->startHour);
        $template->set('endHourSelect', $dateSelection->endHour);
        $template->set('startMinSelect', $dateSelection->startMin);
        $template->set('endMinSelect', $dateSelection->endMin);
        $template->set('startAmPmSelect', $dateSelection->startAmPm);
        $template->set('endAmPmSelect', $dateSelection->endAmPm);
        $template->set('repeatEnd', $dateSelection->endDate);
        $template->set('enableRepeat', $my->authorise('community.view', 'events.repeat'));
        $template->set('lists', $lists);


        $creator  = new CUserStatusCreator('event');
        $creator->title = JText::_('COM_COMMUNITY_EVENTS');
        $creator->html  = $template->fetch('status.event');

        return $creator;
    }
}
