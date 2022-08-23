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

class CLikesHelper
{
    /**
     * Generate HTML for like
     * @param  [object] $obj [description]
     * @return [string]      [description]
     */
    static public function generateHTML($obj, &$likedContent)
    {   
        $data = self::generateHTMLData($obj);
        $data->users = self::getActor($obj->params);
        $data->userCount = count($data->users);
        $actorsHTML = array();
        $slice = 2;
        $others = '';

        if ( isset ( $data->likedContent) ) {
            if(isset($data->urlLink)){
                $likedContent = (object)array_merge((array)$data->likedContent,array('url_link' => $data->urlLink));
            }else{
                $likedContent = $data->likedContent;
            }
        }else {
            $likedContent = null;
        }

        if($data->userCount > 2)
        {
            $slice = 1;
            $others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $data->userCount-1,'onClick="joms.api.streamShowOthers('.$obj->id.');return false;"');
        }

        $users = array_slice($data->users,0,$slice);

        foreach($users as $actor) {
            $user = CFactory::getUser($actor);
            $actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName(false, true).'</a>';
        }

        $jtext =($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR';
        $connecting = '';
        /**
         * Get owner of item
         * @todo Need improve this later
         */
        if ( $data->userCount == 1 ) {
            $me = CFactory::getUser();
            $firstChar = strtolower(substr(JText::_($data->element),0,-(strlen(JText::_($data->element))-1)));
            $vowl = array('a','e','i','o','u');
            if(in_array($firstChar, $vowl)){
                $connecting = JText::_('COM_COMMUNITY_AN');
            } else {
                $connecting = JText::_('COM_COMMUNITY_A');
            }
        }

        switch ( $obj->app ) {
            case 'photo.like':
                $jtext = ($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL_PHOTO' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR_PHOTO';

                $reactionData = CStringHelper::getReactionData();
                $like = new CLike();
                $reactList = array();

                foreach($users as $actor) {
                    $user = CFactory::getUser($actor);
                    $reactId = $like->getReactedId('photo', $obj->cid, $user->id);

                    $reacted = array_filter($reactionData, function($item) use ($reactId) {
                        return $item->id == $reactId;
                    });

                    $reacted = array_shift($reacted);
                    
                    $reactName = 'like';

                    if (isset($reacted->name)) {
                        $reactName = $reacted->name;
                    }
                    
                    $reactList[] = '<span class="joms-stream__reactions read-only"><span class="joms-reactions__item reaction-'.$reactName.'"></span></span>';
                }
                
                /* Get photo record to know who's owner */
                $table = JTable::getInstance('Photo','CTable');
                $table->load($obj->cid);
                
                if ($table) {
                    $targetOwner = CFactory::getUser($table->creator);
                    $ownerText = JText::sprintf('COM_COMMUNITY_NOGENDER_OWNER', $targetOwner->getDisplayName());

                    if (count($reactList) == 1) {
                        if ($data->userCount > 2) {
                            $others = JText::sprintf('COM_COMMUNITY_STREAM_LIKES_OTHERS_PHOTO' , $data->userCount-1,'onClick="joms.api.streamShowOthers('.$obj->id.');return false;"');

                            foreach ($actorsHTML as $key => $actorHTML) {
                                $actorsHTML[$key] = $actorHTML . ' ' . $reactList[$key];
                            }

                            $reactList[0] = '';
                        }

                        return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML)
                        . $others . JText::sprintf($jtext,
                            CUrlHelper::userLink($targetOwner->id),
                            $ownerText,
                            $data->urlLink,
                            JText::_($data->element),
                            $reactList[0]
                        );
                    } else {
                        foreach ($actorsHTML as $key => $actorHTML) {
                            $actorsHTML[$key] = $actorHTML . ' ' . $reactList[$key];
                        }

                        return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML)
                        . JText::sprintf($jtext,
                            CUrlHelper::userLink($targetOwner->id),
                            $ownerText,
                            $data->urlLink,
                            JText::_($data->element),
                            ''
                        );
                    }
                }

                break;
            case 'videos.like':
                $jtext = ($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL_VIDEO' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR_VIDEO';

                $reactionData = CStringHelper::getReactionData();
                $like = new CLike();
                $reactList = array();

                foreach($users as $actor) {
                    $user = CFactory::getUser($actor);
                    $reactId = $like->getReactedId('videos', $obj->cid, $user->id);

                    $reacted = array_filter($reactionData, function($item) use ($reactId) {
                        return $item->id == $reactId;
                    });

                    $reacted = array_shift($reacted);

                    $reactName = 'like';

                    if (isset($reacted->name)) {
                        $reactName = $reacted->name;
                    }

                    $reactList[] = '<span class="joms-stream__reactions read-only"><span class="joms-reactions__item reaction-'.$reactName.'"></span></span>';
                }

                /* Get video record to know who's owner */
                $table = JTable::getInstance('Video','CTable');
                $table->load($obj->cid);

                if ($table) {
                    $targetOwner = CFactory::getUser($table->creator);
                    $ownerText = JText::sprintf('COM_COMMUNITY_NOGENDER_OWNER', $targetOwner->getDisplayName());

                    if (count($reactList) == 1) {
                        if ($data->userCount > 2) {
                            $others = JText::sprintf('COM_COMMUNITY_STREAM_LIKES_OTHERS_VIDEO' , $data->userCount-1,'onClick="joms.api.streamShowOthers('.$obj->id.');return false;"');

                            foreach ($actorsHTML as $key => $actorHTML) {
                                $actorsHTML[$key] = $actorHTML . ' ' . $reactList[$key];
                            }

                            $reactList[0] = '';
                        }

                        return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML)
                        . $others . JText::sprintf($jtext,
                            CUrlHelper::userLink($targetOwner->id),
                            $ownerText,
                            $data->urlLink,
                            JText::_($data->element),
                            $reactList[0]
                        );
                    } else {
                        foreach ($actorsHTML as $key => $actorHTML) {
                            $actorsHTML[$key] = $actorHTML . ' ' . $reactList[$key];
                        }

                        return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML)
                        . JText::sprintf($jtext,
                            CUrlHelper::userLink($targetOwner->id),
                            $ownerText,
                            $data->urlLink,
                            JText::_($data->element),
                            ''
                        );
                    }
                }

                break;
        }

        //special case for profile like
        if($obj->app == 'profile.like'){
            $jtext =($data->userCount > 1) ? 'COM_COMMUNITY_STREAM_LIKES_PROFILE_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_PROFILE_SINGULAR';
            return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$data->urlLink,$data->name,JText::_($data->element));
        }

        return implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$data->urlLink,JText::_($data->element),$connecting);
    }
    /**
     * Get Actor value
     * @param  [JRegistry] $params [description]
     * @return [array]         [description]
     */
    static public function getActor($params) {

        $users = $params->get('actors');

        //some are store in array
        if(is_array($users)){
            return $users; //since 3.3 array already sorted from latest at the front
        }
        return array_reverse(explode(',', $users));
    }

    /**
     * Generate like string
     * @param  [object] $obj [description]
     * @return [object]      [description]
     */
    static public function generateHTMLData($obj) {
        $dataObj = new stdClass();
    
        switch($obj->app){
            case 'profile.like':
                $cid        = CFactory::getUser($obj->cid);

                $dataObj->urlLink   = CUrlHelper::userLink($cid->id);
                $dataObj->name      = $cid->getDisplayName();
                $dataObj->element   = 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PROFILE';

                /* Do prepare content for liked item
                * @since 3.2
                * @todo Need check if load success
                */
                $likedItem = new stdClass();
                $likedItem->title = $cid->name;
                $likedItem->content = $cid->get('description');
                $likedItem->thumb = $cid->getAvatar();
                $dataObj->likedContent = $likedItem;
                break;
            
            case 'pages.like':
                $cid = JTable::getInstance('Page', 'CTable');
                $cid->load($obj->pageid);

                $dataObj->urlLink   = $cid->getLink();
                $dataObj->name      = $cid->name;
                $dataObj->element   = 'COM_COMMUNITY_SINGULAR_PAGE';

                break;

            case 'groups.like':
                $cid = JTable::getInstance('Group', 'CTable');
                $cid->load($obj->groupid);

                $dataObj->urlLink   = $cid->getLink();
                $dataObj->name      = $cid->name;
                $dataObj->element   = 'COM_COMMUNITY_SINGULAR_GROUP';

                break;

            case 'discussion.like':
                $cid = JTable::getInstance('Discussion', 'CTable');
                $cid->load($obj->cid);

                $dataObj->urlLink   = $cid->getLink();
                $dataObj->name    = $cid->title;
                $dataObj->element = 'COM_COMMUNITY_SINGULAR_DISCUSSION';

                break;

            case 'events.like':
                $cid = JTable::getInstance('Event','CTable');
                $cid->load($obj->eventid);

                $dataObj->urlLink   = $cid->getLink();
                $dataObj->name      = $cid->title;
                $dataObj->element   = 'COM_COMMUNITY_SINGULAR_EVENT';
                /* Do prepare content for liked item
                * @since 3.2
                * @todo Need check if load success
                */
                $likedItem = new stdClass();
                $likedItem->title = $cid->title;
                $likedItem->content = $cid->description;
                $likedItem->thumb = $cid->getAvatar();
                $dataObj->likedContent = $likedItem;
                break;

            case 'photo.like':
                $cid = JTable::getInstance('Photo','CTable');
                $cid->load($obj->cid);

                $config = CFactory::getConfig();
                $isPhotoModal = $config->get('album_mode') == 1;

                $dataObj->urlLink   = $cid->getPhotoLink();
                $dataObj->urlLink   = $isPhotoModal ? ( 'javascript:" onclick="joms.api.photoOpen(\'' . $cid->albumid . '\', \'' . $cid->id . '\');' ) : $dataObj->urlLink;
                $dataObj->name      = $cid->caption;
                $dataObj->element   = 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PHOTO_SINGLE';
                /* Do prepare content for liked item
                * @since 3.2
                * @todo Need check if load success
                */
                $likedItem = new stdClass();
                $likedItem->title = $cid->caption;
                $likedItem->content = '';
                $likedItem->thumb = $cid->getImageURI();
                $dataObj->likedContent = $likedItem;
                break;

            case 'videos.like':
                $cid = JTable::getInstance('Video','CTable');
                $cid->load($obj->cid);

                $config = CFactory::getConfig();
                $isVideoModal = $config->get('video_mode') == 1;

                $dataObj->urlLink   = $cid->getViewURI();
                $dataObj->urlLink   = $isVideoModal ? ( 'javascript:" onclick="joms.api.videoOpen(\'' . $cid->id . '\');' ) : $dataObj->urlLink;
                $dataObj->name      = $cid->getTitle();
                $dataObj->element   = 'COM_COMMUNITY_SINGULAR_VIDEO';
                /* Do prepare content for liked item
                * @since 3.2
                * @todo Need check if load success
                */
                $likedItem = new stdClass();
                $likedItem->title = $cid->title;
                $likedItem->content = $cid->description;
                $likedItem->thumb = $cid->getThumbnail();
                $likedItem->media = $cid->getPlayerHTML();
                $dataObj->likedContent = $likedItem;
                break;

            case 'album.like':
                $cid = JTable::getInstance('Album','CTable');
                $cid->load($obj->cid);

                $config = CFactory::getConfig();
                $isPhotoModal = $config->get('album_mode') == 1;

                $dataObj->urlLink   = $cid->getURI();
                $dataObj->urlLink   = $isPhotoModal ? ( 'javascript:" onclick="joms.api.photoOpen(\'' . $cid->id . '\', \'\');' ) : $dataObj->urlLink;

                $dataObj->name      = $cid->name;
                $dataObj->element   = 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_ALBUM';
                /* Do prepare content for liked item
                * @since 3.2
                * @todo Need check if load success
                */
                $likedItem = new stdClass();
                $likedItem->title = $cid->name;
                $likedItem->content = $cid->description;
                $likedItem->thumb = $cid->getCoverThumbPath();
                $dataObj->likedContent = $likedItem;
                break;
        }
        return $dataObj;
    }

    static public function streamShowLikes($actid = 0)
    {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        // Pull the activity record
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 0) {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && $act->app == 'videos') {
            $act->like_type = 'videos.self.share';
            $act->like_id = $act->id;
        }

        $my = CFactory::getUser();
        $like = new CLike();

        $likes = $like->getWhoLikes($act->like_type, $act->like_id);

        $canUnlike = false;
        $likeHTML = '';
        $likeUsers = array();

        foreach ($likes as $user) {
            $likeUsers[] = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
            if ($my->id == $user->id)
                $canUnlike = true;
        }

        if (count($likeUsers) != 0) {
            $likeHTML .= implode(", ", $likeUsers);
            $likeHTML = CStringHelper::isPlural(count($likeUsers)) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY_LIST', $likeHTML) : JText::sprintf('COM_COMMUNITY_LIKE_THIS_LIST', $likeHTML);
        }

        return $likeHTML;
    }

    static public function streamRenderReactionStatus($actid = 0) {
        // Pull the activity record
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 0) {

            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && ($act->app === 'videos' || $act->app === 'videos.linking')) {
            $act->like_type = 'videos';
            $act->like_id = $act->cid;
        }

        $my = CFactory::getUser();
        $like = new CLike();

        $html = $like->showWhoReacts($act->like_type, $act->like_id);

        return $html;
    }

    static public function commentRenderReactionStatus($commentid = 0) {
        $like = new CLike();
        return $like->showWhoReacts('comment', $commentid);
    } 

    static public function getReactId($element, $uId, $userId) {
        $uId = (int) $uId;
        $userId = (int) $userId;

        $db = JFactory::getDbo();
        $sql = "SELECT `reaction_ids`,`like` FROM `#__community_likes`
                WHERE `element` = '$element'
                AND `uid` = $uId";

        $result = $db->setQuery($sql)->loadObject();
        if ($result) {
            $keys = explode(',', $result->like);
            $values = explode(',', $result->reaction_ids);
            $users = array_combine($keys, $values);

            return isset($users[$userId]) ? $users[$userId] : false;
        } else {
            return false;
        }
    }

    static public function renderReactionButton($type, $element, $uId, $reactId = false) {
        if ($reactId) {
            $reactionData = CStringHelper::getReactionData();
            $reacted = array_filter($reactionData, function($item) use ($reactId) {
                return $item->id == $reactId;
            });

            $reacted = array_shift($reacted);
            $text = $reacted->text;
            $class = 'reaction-btn--' . $reacted->name;
            $onclick = 'joms.view.'.$type.'.unreact(' . $uId . ', '.$reactId.')';
        } else {
            $text = JText::_('COM_COMMUNITY_REACTION_LIKE');
            $class = '';
            $onclick = 'joms.view.'.$type.'.react('.$uId.', 1)';
        }

        $html = '<a href="javascript:"
            class="joms-button--reaction '.$class.'"
            data-type="'.$type.'"
            data-element="'.$element.'"
            data-lang-like="'.JText::_('COM_COMMUNITY_REACTION_LIKE').'"
            data-uid="'.$uId.'"
            data-reactid="'. ($reactId ? $reactId : 1) .'"
            data-action="'. ($reactId ? 'unreact' : 'react') .'"
            onclick="'.$onclick.'">

            '.$text.'
        </a>';

        return $html;
    }
}
