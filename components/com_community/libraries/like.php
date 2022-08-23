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

class CLike {
    public function addLike($element, $itemId, $reactId, $userid = null) {
        if ($userid) {
            $my = CFactory::getUser($userid);
        } else {
            $my = CFactory::getUser();
        }
        
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);
        $reactions = [1,2,3,4,5,6];

        $like->element = $element;
        $like->uid = $itemId;
        $like->dislike = '';

        // Check if user already like
        $likesInArray = $like->like ? explode(',', trim($like->like, ',')) : array();
        $reactionsInArray = $like->reaction_ids ? explode(',', trim($like->reaction_ids, ',')) : array();
        
        // set default reaction ID
        if (!$reactId) $reactId = 1;

        if (!in_array($reactId, $reactions)) {
            return false;
        }

        $liked = array_combine($likesInArray, $reactionsInArray);

        /* Like once time */
        if(isset($liked[$my->id]) && $liked[$my->id] == $reactId) {
            return false;
        } else {
            $liked[$my->id] = $reactId;

            $likesInArray = array_keys($liked);
            $reactionsInArray = array_values($liked);

            $like->like = implode(',', $likesInArray);
            $like->reaction_ids = implode(',', $reactionsInArray);
        }

        // Check if the user already dislike
        $dislikesInArray = explode(',', trim($like->dislike, ','));
        if (in_array($my->id, $dislikesInArray)) {
            // Remove user dislike from array
            $key = array_search($my->id, $dislikesInArray);
            unset($dislikesInArray [$key]);

            $like->dislike = implode(',', $dislikesInArray);
        }
        
        switch ($element) {
            case 'comment':
                //get the instance of the wall
                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($itemId);

                if(!$wall->id){
                    break;
                };

                if($wall->type =="profile.status"){
                    $wall->type="profile";
                }

                //load the stream id from activity stream
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load(array('comment_id' => $wall->contentid, 'app'=>$wall->type));
                
                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id.'#activity-stream-container';

                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('comment', JText::_('COM_COMMUNITY_SINGULAR_COMMENT'));
                    $params->set('comment_url', $url);
                    $params->set('actor',$my->getDisplayName());

                    //add to notifications
                    CNotificationLibrary::add('comments_like', $my->id, $wall->post_by, JText::sprintf('COM_COMMUNITY_PROFILE_WALL_LIKE_EMAIL_SUBJECT'), '', 'comments.like', $params);
                } elseif ($wall->type == 'albums' && $wall->contentid) {
                    //this will link to the user albums instead
                    $album = JTable::getInstance('Album', 'CTable');
                    $album->load($wall->contentid);
                    $url = $album->getURI();

                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('album', JText::_('COM_COMMUNITY_SINGULAR_ALBUM'));
                    $params->set('album_url', $url);
                    $params->set('actor',$my->getDisplayName());

                    //add to notifications
                    CNotificationLibrary::add('comments_like', $my->id, $wall->post_by, JText::sprintf('COM_COMMUNITY_ALBUM_WALL_LIKE_EMAIL_SUBJECT'), '', 'comments.like', $params);
                } elseif ($wall->type == 'videos' && $wall->contentid) {
                    $video = JTable::getInstance('Video', 'CTable');
                    $video->load($wall->contentid);
                    if ($video->id) {
                        if ($video->groupid) {
                            $url = 'index.php?option=com_community&view=videos&task=video&groupid=' . $video->groupid . '&videoid=' . $video->id;
                        } else {
                            $url = 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id;
                        }
                        $params = new CParameter('');
                        $params->set('url', $url);
                        $params->set('video', JText::_('COM_COMMUNITY_SINGULAR_VIDEO'));
                        $params->set('video_url', $url);
                        $params->set('actor',$my->getDisplayName());

                        //add to notifications
                        CNotificationLibrary::add('comments_like', $my->id, $wall->post_by, JText::sprintf('COM_COMMUNITY_VIDEO_WALL_LIKE_EMAIL_SUBJECT'), '', 'comments.like', $params);
                    }                    
                } else {
                    //this can be a comment on third party wall since it doesnt have the stream id attached
                    $wallType = $wall->type;

                    $db = JFactory::getDbo();
                    $query = "SELECT * FROM " . $db->quoteName('#__community_thirdparty_wall_options') . " WHERE "
                        . $db->quoteName('name') . '=' . $db->quote($wall->type);
                    $db->setQuery($query);
                    $wallOptions = $db->loadObject();

                    //if found, this is a third party ones
                    if ($wallOptions) {

                        //check if there is any links in object name url
                        $wallParams = new CParameter($wallOptions->params);

                        if ($wallParams->get('object_title')) $objectTitle = '<a href="'.$wallParams->get('object_url').'">'.$wallParams->get('object_title').'</a>';
                        else $objectTitle = '<a href="'.$wallParams->get('object_url').'">'.$wallOptions->object_name.'</a>';
                        $params = new CParameter('');
                        $params->set('url', $wallParams->get('object_url'));
                        $params->set('comment', JText::_('COM_COMMUNITY_SINGULAR_COMMENT'));
                        $params->set('comment_url', $wallParams->get('object_url'));
                        $params->set('actor', $my->getDisplayName());
                        $params->set('object_title', $objectTitle);
                        $params->set('title', strip_tags($objectTitle));

                        //add to notifications
                        CNotificationLibrary::add($wallOptions->notification_cmd.'_like', $my->id, $wall->post_by, JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_LIKE_EMAIL_SUBJECT'), JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_LIKE_EMAIL_BODY'), 'comments.like.thirdparty', $params);
                    }

                }
                break;
            case 'photo':
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($itemId);
                if ($photo->id) {
                    $url = $photo->getRawPhotoURI();
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('photo', JText::_('COM_COMMUNITY_SINGULAR_PHOTO'));
                    $params->set('photo_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $photo->creator, JText::sprintf('COM_COMMUNITY_PHOTO_LIKE_EMAIL_SUBJECT'), '', 'photos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('photo.like');

                    //@since 4.1 when a profile is liked, dump the data into photo stats
                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addPhotoStats($photo->id, 'like');
                }
                break;
            case 'album':
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($itemId);
                if ($album->id) {
                    if ($album->groupid) {
                        $url = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $album->groupid;
                    } else {
                        $url = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id;
                    }

                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('album', $album->name);
                    $params->set('album_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $album->creator, JText::sprintf('COM_COMMUNITY_ALBUM_LIKE_EMAIL_SUBJECT'), '', 'album.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('album.like');
                }
                break;
            case 'videos':
                $video = JTable::getInstance('Video', 'CTable');
                $video->load($itemId);
                if ($video->id) {
                    if ($video->groupid) {
                        $url = 'index.php?option=com_community&view=videos&task=video&groupid=' . $video->groupid . '&videoid=' . $video->id;
                    } else {
                        $url = 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id;
                    }
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('video', $video->title);
                    $params->set('video_url', $url);

                    CNotificationLibrary::add('videos_like', $my->id, $video->creator, JText::sprintf('COM_COMMUNITY_VIDEO_LIKE_EMAIL_SUBJECT'), '', 'videos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('videos.like');

                    //@since 4.1 when a profile is liked, dump the data into photo stats
                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addVideoStats($video->id, 'like');
                }
                break;
            case 'profile':
                $profile = CFactory::getUser($itemId);
                if ($profile->id) {
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('profile', strtolower(JText::_('COM_COMMUNITY_NOTIFICATIONGROUP_PROFILE')) );
                    $params->set('profile_url', $url);

                    CNotificationLibrary::add('profile_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_LIKE_EMAIL_SUBJECT'), '', 'profile.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.like');

                    //@since 4.1 when a profile is liked, dump the data into profile stats
                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addProfileStats($profile->id, 'like');
                }
                break;
            case 'pages.wall':
            case 'groups.wall':
            case 'profile.status':
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($itemId);

                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                    $params->set('stream_url', $url);

                    CNotificationLibrary::add('profile_stream_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_STREAM_LIKE_EMAIL_SUBJECT'), '', 'profile.stream.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.stream.like');
                }
                break;
            case 'cover.upload':
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load(CPhotosHelper::getPhotoOfStream($itemId));

                if ($photo->id) {
                    $url = $photo->getRawPhotoURI();
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('photo', JText::_('COM_COMMUNITY_SINGULAR_PHOTO'));
                    $params->set('photo_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $photo->creator, JText::sprintf('COM_COMMUNITY_COVER_LIKE_EMAIL_SUBJECT'), '', 'photos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('photos.like');
                }
                break;
            case 'profile.avatar.upload':
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($itemId);

                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                    $params->set('stream_url', $url);

                    CNotificationLibrary::add('profile_stream_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_AVATAR_LIKE_EMAIL_SUBJECT'), '', 'profile.stream.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.stream.like');
                }
                break;
            case 'groups':
                /* Adding user points */
                CUserPoints::assignPoint('groups.like');
                break;
            case 'pages':
                /* Adding user points */
                $page = JTable::getInstance('Page', 'CTable');
                $page->load($itemId);
                
                if ($page->id) {
                    $url = $page->getRawPageURI();
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('page', $page->name);
                    $params->set('page_url', $url);

                    CNotificationLibrary::add('pages_like', $my->id, $page->ownerid, JText::sprintf('COM_COMMUNITY_PAGE_LIKE_EMAIL_SUBJECT'), '', 'pages.like', $params);
                     
                    /* Adding user points */
                    CUserPoints::assignPoint('pages.like');
                }

                break;
            case 'discussion':
                /* Adding user points */
                CUserPoints::assignPoint('groups.discussion.like');
                break;
            case 'events':
                /* Adding user points */
                CUserPoints::assignPoint('events.like');
                break;
            case 'album.self.share':
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($itemId);

                $profile = CFactory::getUser($stream->actor);
                //get total photo(s) uploaded and determine the string
                $actParam = new CParameter($stream->params);
                if($actParam->get('batchcount') > 1){
                    $content = JText::sprintf('COM_COMMUNITY_ACTIVITY_ALBUM_PICTURES_LIKE_SUBJECT');
                }else{
                    $content = JText::sprintf('COM_COMMUNITY_ACTIVITY_ALBUM_PICTURE_LIKE_SUBJECT');
                }
                $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                $params = new CParameter('');
                $params->set('url', $url);
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                CNotificationLibrary::add('profile_stream_like', $my->id, $profile->id, $content, '', 'profile.stream.like', $params);

            default:
                CUserPoints::assignPoint($element . '.like');
        }

		// Log user engagement
		CEngagement::log($element . '.like', $my->id);

        $like->store();
        return true;
    }

    public function addDislike($element, $itemId) {
        $my = CFactory::getUser();

        $dislike = JTable::getInstance('Like', 'CTable');
        $dislike->loadInfo($element, $itemId);

        $dislike->element = $element;
        $dislike->uid = $itemId;

        $dislikesInArray = explode(',', $dislike->dislike);
        array_push($dislikesInArray, $my->id);
        $dislikesInArray = array_unique($dislikesInArray);
        $dislike->dislike = ltrim(implode(',', $dislikesInArray), ',');

        // Check if the user already like
        $likesInArray = explode(',', $dislike->like);
        if (in_array($my->id, $likesInArray)) {
            // Remove user like from array
            $key = array_search($my->id, $likesInArray);
            unset($likesInArray[$key]);

            $dislike->like = implode(',', $likesInArray);
        }

        $dislike->store();
    }

    public function unlike($element, $itemId, $userid = null) {
        if ($userid) {
            $my = CFactory::getUser($userid);
        } else {
            $my = CFactory::getUser();
        }

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);
        
        if (!$like->id) {
            return false;
        }

        $reactionsInArray = explode(',', $like->reaction_ids);

        // Check if the user already like
        $likesInArray = explode(',', $like->like);
        if (in_array($my->id, $likesInArray)) {
            // Remove user like from array
            $key = array_search($my->id, $likesInArray);
            unset($likesInArray[$key]);
            unset($reactionsInArray[$key]);

            $like->like = implode(',', $likesInArray);
            $like->reaction_ids = implode(',', $reactionsInArray);
        } else {
            return false;
        }

        // Check if the user already dislike
        $dislikesInArray = explode(',', $like->dislike);
        if (in_array($my->id, $dislikesInArray)) {
            // Remove user dislike from array
            $key = array_search($my->id, $dislikesInArray);
            unset($dislikesInArray[$key]);

            $like->dislike = implode(',', $dislikesInArray);
        }

        //for user points
        switch($element){
            case 'photo':
                /* Decrease user points */
                CUserPoints::assignPoint('photo.unlike');
                break;
            case 'album':
                /* Decrease user points */
                CUserPoints::assignPoint('album.unlike');
                break;
            case 'videos':
                /* Decrease user points */
                CUserPoints::assignPoint('videos.unlike');
                break;
            case 'profile':
                /* Decrease user points */
                CUserPoints::assignPoint('profile.unlike');
                break;
            case 'profile.status':
                /* Decrease user points */
                CUserPoints::assignPoint('profile.stream.unlike');
                break;
            case 'groups':
                /* Decrease user points */
                CUserPoints::assignPoint('groups.unlike');
                break;
            case 'events':
                /* Decrease user points */
                CUserPoints::assignPoint('events.unlike');
                break;
            case 'photo':
                break;
        }

        $like->store();
        return true;
    }

    // Check if the user like this
    // Returns:
    // -1	- Unlike
    // 1	- Like
    // 0	- Dislike
    public function userLiked($element, $itemId, $userId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        // Check if user already like
        $likesInArray = explode(',', trim($like->like, ','));

        if (in_array($userId, $likesInArray)) {
            // Return 1, the user is liked
            return COMMUNITY_LIKE;
        }

        // Check if user already dislike
        $dislikesInArray = explode(',', trim($like->dislike, ','));

        if (in_array($userId, $dislikesInArray)) {
            // Return 0, the user is disliked
            return COMMUNITY_DISLIKE;
        }

        // Return -1 as neutral
        return COMMUNITY_UNLIKE;
    }

    /**
     * Can current $my user 'like' an item ?
     * - rule: friend can like friend's item (photos/vidoes/event)
     * @return bool
     */
    public function canLike() {
        $my = CFactory::getInstance();

        return ( $my->id != 0 );
    }

    public function getReactedId($element, $itemId, $userId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        if ($userId && $like->like && $like->reaction_ids) {
            $users = explode(',', $like->like);
            $values = explode(',', $like->reaction_ids);
            
            $index = array_search($userId, $users);
            if ($index !== false && isset($values[$index])) {
                return $values[$index];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Return number of likes
     */
    public function getLikeCount($element, $itemId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);
        $count = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $count = count($likesInArray);
        }

        return $count;
    }

    /**
     * Return an array of user who likes the element
     * @return CUser objects
     */
    public function getWhoLikes($element, $itemId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $users = array();
        $likesInArray = array();

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
        }

        foreach ($likesInArray as $row) {
            $user = CFactory::getUser($row);
            $users[] = $user;
        }

        return $users;
    }

    /**
     *
     * @return bool True if element can be liked
     */
    public function enabled($element) {
        $config = CFactory::getConfig();
        
        if ($element == 'pages') return true;

        // Element can also contain sub-element. eg:// photos.album
        // for enable/disable configuration, we only check the first component
        $elements = explode('.', $element);
        return ( $config->get('likes_' . $elements[0]) );
    }

    /**
     *
     * @return string
     */
    public function getHTML($element, $itemId, $userId) {
        if ($userId == 0) {
            return false;
        }
        // @rule: Only display likes html codes when likes is allowed.
        $config = CFactory::getConfig();

        if (!$this->enabled($element)) {
            return;
        }

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $userLiked = COMMUNITY_UNLIKE;
        $likesInArray = array();
        $dislikesInArray = array();
        $likes = 0;
        $dislikes = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $likes = count($likesInArray);
        }

        if (!empty($like->dislike)) {
            $dislikesInArray = explode(',', trim($like->dislike, ','));
            $dislikes = count($dislikesInArray);
        }

        $userLiked = $this->userLiked($element, $itemId, $userId);

        $tmpl = new CTemplate();

        // For rendering, we need to replace . with _ since it is not
        // a valid id
        $element = str_replace('.', '_', $element);
        $tmpl->set('likeId', 'like' . '-' . $element . '-' . $itemId);
        $tmpl->set('likes', $likes);
        $tmpl->set('dislikes', $dislikes);
        $tmpl->set('userLiked', $userLiked);

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->getHtmlPublic($element, $itemId);
        } else {
            return $tmpl->fetch('like.html');
        }
    }

    /**
     * Display like/dislike for public
     * @return string
     */
    public function getHtmlPublic($element, $itemId) {
        $config = CFactory::getConfig();

        if (!$config->get('likes_' . $element)) {
            return;
        }

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $likesInArray = array();
        $dislikesInArray = array();
        $likes = 0;
        $dislikes = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $likes = count($likesInArray);
        }

        if (!empty($like->dislike)) {
            $dislikesInArray = explode(',', trim($like->dislike, ','));
            $dislikes = count($dislikesInArray);
        }

        $tmpl = new CTemplate();
        $tmpl->set('likes', $likes);
        $tmpl->set('dislikes', $dislikes);

        if ($config->get('show_like_public')) {
            return $tmpl->fetch('like.public');
        }
    }

    public function showWhoReacts($element, $itemId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        if (!$like->like || !$like->reaction_ids) {
            return '';
        }

        $enabledUsers = $this->getEnabledUsers( $like->like );

        $keys = explode(',', $like->reaction_ids);
        $values = explode(',', $like->like);

        $reactions = $this->getReactions($like, $enabledUsers);
        
        if ($element === 'comment') {
            $text = $this->getLikeCount($element, $itemId);
        } else {
            $text = $this->getStreamReactionText( $enabledUsers );
        }
        $tmpl = new CTemplate();
        $tmpl->set('uid', $itemId)
            ->set('element', $element)
            ->set('reactions', $reactions)
            ->set('reactionText', $text);

        return $tmpl->fetch('stream/reaction-result');
    }

    public function getReactions($data, $enabledUsers) {
        $keys = explode(',', $data->reaction_ids);
        $values = explode(',', $data->like);

        $reactions = array();
        foreach ($keys as $k => $key) {
            $existed = array_filter( $reactions, function($item) use ($key) {
                return $item->reaction_id == $key;
            });

            if (count($existed)) {
                $tmp = array_slice($existed, 0, 1);
                $obj = $tmp[0];
            } else {
                $obj = new stdClass;
                $obj->reaction_id = $key;
                $obj->userids = array();
            }

            if (isset($values[$k]) && in_array($values[$k], $enabledUsers)) {
                $obj->userids[] = $values[$k];
                if (!count($existed)) {
                    $reactions[] = $obj;
                }
            }
        }

        // add count
        $reactions = array_map( function($item) {
            $item->count = count( $item->userids );
            return $item;
        }, $reactions);

        // order reactions, most reacted go first
        uasort($reactions, function( $a, $b ) {
            if ($a->count === $b->count) {
                return $a->reaction_id < $b->reaction_id ? -1 : 1;
            } else {
                return $a->count > $b->count ? -1 : 1;
            }
        });

        return $reactions;
    }

    protected function getEnabledUsers($userids) {
        $db = JFactory::getDbo();
        $sql = "SELECT `id` FROM `#__users`
                WHERE `id` IN ( $userids )
                AND block = 0";
        
        $ids = $db->setQuery($sql)->loadColumn();
        return $ids;
    }

    protected function getStreamReactionText($reactedUsers = array()) {
        $my = CFactory::getUser();
        $displayUsers = array();

        if (in_array($my->id, $reactedUsers)) {
            $displayUsers[] = JText::_('COM_COMMUNITY_CHAT_YOU');
        }

        foreach ($reactedUsers as $userid) {
            if (count($displayUsers) < 2 && $my->id != $userid) {
                $user = CFactory::getUser($userid);
                $displayUsers[] = $user->getName();
            }
        }

        $othersCount = count($reactedUsers) - count($displayUsers);

        $text = '';
        $and = JText::_('COM_COMMUNITY_AND');

        if (count($displayUsers) === 1) {
            $text = $displayUsers[0];
        }

        if (count($displayUsers) === 2 && $othersCount < 1) {
            $text = implode(" $and ", $displayUsers); 
        } else {
            $text = implode(', ', $displayUsers);
        }

        if ($othersCount > 0) {
            if (count($displayUsers)) {
                $other = ' ' . JText::sprintf('COM_COMMUNITY_REACTION_OTHER', $othersCount);
                $others = ' ' . JText::sprintf('COM_COMMUNITY_REACTION_OTHERS', $othersCount);
                
                $text .= $othersCount > 1 ? $others : $other;
            } else {
                $text .= $othersCount;
            }
        }

        return $text;
    }

}