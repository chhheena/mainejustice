<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerPolls extends CommunityController
{
    public function __construct()
    {
        parent::__construct();

        $this->registerTask('publish', 'savePublish');
        $this->registerTask('unpublish', 'savePublish');
    }

    public function display($cachable = false, $urlparams = array())
    {
        $jinput = JFactory::getApplication()->input;
        $viewName = $jinput->get('view' , 'community');
        $layout = $jinput->get('layout' , 'default');
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView($viewName , $viewType);
        $model = $this->getModel( $viewName ,'CommunityAdminModel' );

        if ($model) {
            $view->setModel($model, $viewName);
        }

        $view->setLayout($layout);
        $view->display();
    }

    public function ajaxTogglePublish($id, $type, $viewName = false)
    {   
        // Send email notification to owner when a poll is published.
        $config = CFactory::getConfig();
        $poll  = JTable::getInstance('Poll' , 'CTable');
        $poll->load($id);

        if ($type == 'published' && $poll->published == 0 && $config->get('moderatepollcreation')) {
           $this->notificationApproval($poll);

            $db = JFactory::getDbo();

            $query = "UPDATE ".$db->quoteName('#__community_activities')
                ." SET ".$db->quoteName('archived')."=".$db->quote(0)
                ." WHERE ".$db->quoteName('cid')."=".$db->quote($id)
                ." AND ".$db->quoteName('app')."=".$db->quote('polls');
            $db->setQuery($query);

            $db->execute();
        }

        return parent::ajaxTogglePublish($id, $type, 'polls');
    }

    public function deletePoll()
    {   
        require_once(JPATH_ROOT . '/components/com_community/libraries/featured.php');
        require_once(JPATH_ROOT . '/components/com_community/defines.community.php');

        $db = JFactory::getDbo();
        $pollWithError = array();
        $poll = JTable::getInstance('Poll', 'CTable');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $ids = $jinput->get('cid', '', 'NONE');

        if (empty($ids)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
        }

        foreach($ids as $id) {
            $poll->load($id);
            $pollData = $poll;

            if (!$poll->delete($id)) {
                array_push($pollWithError, $id . ': ' . $pollData->title);
            } else {
                $query = "DELETE FROM ".$db->quoteName('#__community_polls_items')
                ." WHERE ".$db->quoteName('poll_id')."=".$db->quote($id);
                $db->setQuery($query);
                $db->execute();

                $query = "DELETE FROM ".$db->quoteName('#__community_polls_users')
                    ." WHERE ".$db->quoteName('poll_id')."=".$db->quote($id);
                $db->setQuery($query);
                $db->execute();

                $query = "DELETE FROM ".$db->quoteName('#__community_activities')
                    ." WHERE ".$db->quoteName('cid')."=".$db->quote($id)
                    ." AND ".$db->quoteName('app')."=".$db->quote('polls');
                $db->setQuery($query);
                $db->execute();
            }
        }

        $message = '';
        if (empty($error)) {
            $message = JText::_('COM_COMMUNITY_POLL_DELETED');
        } else {
            $error = implode(',', $groupWithError);
            $message    = JText::sprintf('COM_COMMUNITY_POLLS_DELETE_POLL_ERROR' , $error);
        }

        $mainframe  = JFactory::getApplication();
        $mainframe->enqueueMessage($message ,'message');
        $mainframe->redirect('index.php?option=com_community&view=polls');
    }

    public function notificationApproval($poll)
    {
        $lang = JFactory::getLanguage();
        $lang->load('com_community', JPATH_ROOT);

        $my = CFactory::getUser();

        // Send notification email to owner
        $params = new CParameter( '' );
        $params->set('url', 'index.php?option=com_community&view=polls');
        $params->set('pollTitle', $poll->title);
        $params->set('poll', $poll->title);
        $params->set('poll_url', 'index.php?option=com_community&view=polls');

        CNotificationLibrary::add('polls_notify_creator', $my->id, $poll->creator, JText::_('COM_COMMUNITY_POLLS_PUBLISHED_MAIL_SUBJECT'), '', 'polls.notifycreator', $params);

    }
}