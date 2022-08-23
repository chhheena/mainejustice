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

class CommunityFollowersController extends CommunityBaseController 
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
        
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $my = CFactory::getUser();

        $userid = $jinput->get('userid', $my->id);

        // Check privacy setting
        if (!$my->authorise('community.view', 'friends.' . $userid)) {

            if ($my->id == 0) {
                $this->blockUnregister();
            }
            echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
            return;
        }

        $this->renderView(__FUNCTION__);
    }

    public function following($cacheable = false, $urlparams = false)
    {   
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $my = CFactory::getUser();

        $userid = $jinput->get('userid', $my->id);

        // Check privacy setting
        if (!$my->authorise('community.view', 'friends.' . $userid)) {

            if ($my->id == 0) {
                $this->blockUnregister();
            }
            echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
            return;
        }
        
        $this->renderView(__FUNCTION__);
    }

    public function ajaxConfirmFollow($userId)
    {
        // Block unregistered users.
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');

        //@todo filter paramater
        $model = CFactory::getModel('friends');
        $blockModel = $this->getModel('block');

        $my = CFactory::getUser();
        $user = CFactory::getUser($userId);


        $blockUser = new blockUser();
        $config = CFactory::getConfig();

        // Block blocked users
        if ($blockModel->getBlockStatus($my->id, $userId) && !COwnerHelper::isCommunityAdmin()) {
            //$blockUser->ajaxBlockMessage();
        }

        // Warn owner that the user has been blocked, cannot add as friend
        if ($blockModel->getBlockStatus($userId, $my->id)) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FOLLOWING'),
                'error' => JText::_('COM_COMMUNITY_YOU_HAVE_BEEN_BLOCKED_BY_THIS_USER')
            );

            die( json_encode($json) );
        }

        $html = '';
        $actions = '';

        if ($my->id == $userId) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FOLLOWING'),
                'error' => JText::_('COM_COMMUNITY_FOLLOWING_CANNOT_ADD_SELF')
            );
        } elseif ($user->isBlocked()) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_FOLLOWING'),
                'error' => JText::_('COM_COMMUNITY_FRIENDS_CANNOT_ADD_INACTIVE_USER')
            );
        } else {
            $json = array(
                'title'     => JText::_('COM_COMMUNITY_FOLLOWING'),
                'avatar'    => $user->getThumbAvatar(),
                'desc'      => JText::sprintf('COM_COMMUNITY_CONFIRM_ADD_FOLLOWING', $user->getDisplayName()),
                'btnAdd'    => JText::_('COM_COMMUNITY_FOLLOW'),
                'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
            );
        }

        die(json_encode($json));
    }

    public function ajaxSaveFollow($postVars)
    {
        $filter = JFilterInput::getInstance();
        $postVars = $filter->clean($postVars, 'array');

        $model = CFactory::getModel('followers');
        $my = CFactory::getUser();

        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        $postVars = CAjaxHelper::toArray($postVars);
        $id = $postVars['userid'];
        $data = CFactory::getUser($id);

        $connection = $model->isFollowing($my->id, $id);
        
        if ($connection || !$my->authorise('community.request', 'friends.' . $id)) {
            $json = array('message' => JText::sprintf('COM_COMMUNITY_FOLLOWING_IS_ALREADY_FOLLOW', $data->getDisplayName()));
        } else if (count($postVars) > 0) {
            $model->addFollowing($my->id, $id);

            // User points
            CUserPoints::assignPoint('followers.add');
            
            $json = array('message' => JText::sprintf('COM_COMMUNITY_FOLLOWING_ADDED_SUCCESS', $data->getDisplayName()));

            $url = 'index.php?option=com_community&view=profile&userid=' . $my->id;

            $params = new CParameter('');
            $params->set('url', $url);
            $params->set('profile', strtolower(JText::_('COM_COMMUNITY_NOTIFICATIONGROUP_PROFILE')) );
            $params->set('profile_url', $url);
            $params->set('actor',$my->getDisplayName());

            CNotificationLibrary::add('new_follower', $my->id, $id, JText::sprintf('COM_COMMUNITY_NEW_FOLLOWER_EMAIL_SUBJECT'), '', 'profile.follow', $params);
        }

        die(json_encode($json));
    }

    public function ajaxConfirmUnfollow($userId)
    {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        $following = CFactory::getUser($userId);
        $html = '';

        $html .= '<p>' . JText::sprintf('COM_COMMUNITY_FOLLOWING_UNFOLLOW', $following->getDisplayName()) . '</p>';

        $json = array(
            'title'  => JText::_('COM_COMMUNITY_UNFOLLOW'),
            'html'   => $html,
            'btnYes' => JText::_('COM_COMMUNITY_YES_BUTTON'),
            'btnNo'  => JText::_('COM_COMMUNITY_NO_BUTTON')
        );

        die(json_encode($json));
    }

    public function ajaxUnfollowSave($userId) {
        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        $json = array();

        $my = CFactory::getUser();

        $model = CFactory::getModel('followers');
        $unfollow = $model->unFollow($my->id, $userId);

        if ($unfollow) {
            $following = CFactory::getUser($userId);

            // User points
            CUserPoints::assignPoint('followers.remove');

            $json['success'] = true;
            $json['message'] = JText::sprintf('COM_COMMUNITY_FOLLOWING_UNFOLLOWED', $following->getDisplayName());

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_FOLLOWING_UNFOLLOWED_ERROR');
        }

        die(json_encode($json));
    }
}