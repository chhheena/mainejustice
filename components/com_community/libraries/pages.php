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

require_once(JPATH_ROOT .'/components/com_community/libraries/core.php');

class CPages implements
	CCommentInterface, CStreamable
{
	static public function getActivityTitleHTML($act)
	{
		return "PAGE";
	}

	static public function getActivityContentHTML($act)
	{   
		// Ok, the activity could be an upload OR a wall comment. In the future, the content should
		// indicate which is which
		$html = '';
		$param = new CParameter($act->params);
		$action = $param->get('action' , false);

		$config = CFactory::getConfig();

		$pageModel	= CFactory::getModel('pages');

		if ($action == CPagesAction::DISCUSSION_CREATE) {
			// Old discussion might not have 'action', and we can't display their
			// discussion summary
			$topicId = $param->get('topic_id', false);
			if ($topicId) {
				$page = JTable::getInstance('Page', 'CTable');
				$discussion	= JTable::getInstance('Discussion', 'CTable');

				$page->load($act->cid);
				$discussion->load($topicId);

				$discussion->message = strip_tags($discussion->message);
				$topic = CStringHelper::escape($discussion->message);
				$tmpl = new CTemplate();
				$tmpl->set('comment' , CStringHelper::substr($topic, 0, $config->getInt('streamcontentlength')));
				$html = $tmpl->fetch('activity.pages.discussion.create');
			}
			return $html;
		} else if ($action == CPagesAction::WALLPOST_CREATE) {
			// a new wall post for page
			// @since 1.8
			$page	= JTable::getInstance('Page' , 'CTable');
			$page->load($act->cid);

			$wallModel	= CFactory::getModel('Wall');
			$wall		= JTable::getInstance('Wall' , 'CTable');
			$my			= CFactory::getUser();

			// make sure the page is a public page or current use is
			// a member of the page
			if(($page->approvals == 0) || $page->isMember($my->id)) {
				//CFactory::load('libraries' , 'comment');
				$wall->load($param->get('wallid'));
				$comment	= strip_tags($wall->comment , '<comment>');
				$comment	= CComment::stripCommentData($comment);
				$tmpl	= new CTemplate();
				$tmpl->set('comment' , CStringHelper::substr($comment, 0, $config->getInt('streamcontentlength')));
				$html	= $tmpl->fetch('activity.pages.wall.create');
			}
			return $html;
		} else if ($action == CPagesAction::DISCUSSION_REPLY) {
			// @since 1.8
			$page	= JTable::getInstance('Page' , 'CTable');
			$page->load($act->cid);

			$wallModel	= CFactory::getModel('Wall');
			$wall		= JTable::getInstance('Wall' , 'CTable');
			$my			= CFactory::getUser();

			// make sure the page is a public page or current use is
			// a member of the page
			if(($page->approvals == 0) || $page->isMember($my->id))
			{
				$wallid = $param->get('wallid');
				//CFactory::load('libraries' , 'wall');
				$html = CWallLibrary::getWallContentSummary($wallid);
			}
			return $html;
		} else if ($action == CPagesAction::CREATE) {
			$page	= JTable::getInstance('Page' , 'CTable');
			$page->load($act->cid);
            
			$tmpl	= new CTemplate();
			$tmpl->set('page' , $page);
			$html	= $tmpl->fetch('activity.pages.create');
		}

		return $html;
	}

	/**
	 * Return an array of valid 'app' code to fetch from the stream
	 * @return array
	 */
	static public function getStreamAppCode(){
		return array('pages.wall', 'pages.attend', 'events.wall', 'videos',
			'pages.discussion', 'pages.discussion.reply', 'pages.bulletin',
				'photos', 'events');
	}


	static public function sendCommentNotification(CTableWall $wall, $message)
	{
		$my	= CFactory::getUser();
		$targetUser	= CFactory::getUser($wall->post_by);
		$url = 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $wall->contentid;
		$params = $targetUser->getParams();

		$params	= new CParameter('');
		$params->set('url', $url);
		$params->set('message', $message);

		CNotificationLibrary::add('pages_submit_wall_comment', $my->id, $targetUser->id, JText::sprintf('PLG_WALLS_WALL_COMMENT_EMAIL_SUBJECT', $my->getDisplayName()), '', 'pages.wallcomment', $params);

		return true;
	}

	/**
	 *
	 */
	static public function joinApproved($pageId, $userid)
	{
		$page = JTable::getInstance('Page', 'CTable');
		$member = JTable::getInstance('PageMembers', 'CTable');

		$page->load($pageId);

		$act = new stdClass();
		$act->cmd  = 'page.join';
		$act->actor = $userid;
		$act->target = 0;
		$act->title	 = '';
		$act->content = '';
		$act->app = 'pages.join';
		$act->cid = $page->id;
		$act->pageid = $page->id;

		$params = new CParameter('');
		$params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
		$params->set('action', 'page.join');
        
        $like = new CLike();
		$like->addLike('pages', $page->id, 1, $userid);

		// Add logging
        if(CUserPoints::assignPoint('page.join')){
            CActivityStream::addActor($act, $params->toString());
        }

		// Store the page and update stats
		$page->updateStats();
		$page->store();
	}


	/**
	 * Return HTML formatted stream for pages
	 * @param object $page
	 * @deprecated use activities library instead
	 */
	public function getStreamHTML($page, $filters = array())
	{
		$activities = new CActivities();
		$streamHTML = $activities->getOlderStream(1000000000, 'active-page', $page->id, null, $filters);

		return $streamHTML;
	}

	/**
	 * Return true is the user can post to the stream
	 **/
	public function isAllowStreamPost($userid, $option)
	{
		// Guest cannot post.
		if($userid == 0){
			return false;
		}

		// Admin can comment on any post
		if (COwnerHelper::isCommunityAdmin()) {
			return true;
		}

		// if the pageid not specified, obviously stream comment is not allowed
		if (empty($option['pageid'])) {
			return false;
		}

		$page = JTable::getInstance('Page' , 'CTable');
		$page->load($option['pageid']);
		return $page->isMember($userid);
	}

	/**
	 * Return true is the user is a page admin
	 **/
	public function isAdmin($userid, $pageid)
	{
		$page = JTable::getInstance('Page' , 'CTable');
		$page->load($pageid);
		return $page->isAdmin($userid);
	}
}

class CPagesAction
{
	const DISCUSSION_CREATE	= 'page.discussion.create';
	const DISCUSSION_REPLY = 'page.discussion.reply';
	const WALLPOST_CREATE = 'page.wall.create';
	const CREATE = 'page.create';
}