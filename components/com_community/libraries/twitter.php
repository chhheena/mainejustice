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
defined('_JEXEC') or die( 'Restricted access' );

class CTwitter
{
	var	$_name = 'Twitter';

    private $_fields = array(
        'gender' => 'FIELD_GENDER',
        'birthday' => 'FIELD_BIRTHDATE',
        'description' => 'FIELD_ABOUTME',
        'url' => 'FIELD_WEBSITE'
    );

	static public function getOAuthRequest()
	{

		if(!JPluginHelper::importPlugin('community' , 'twitter' ) )
		{
		    return JText::sprintf('COM_COMMUNITY_PLUGIN_FAIL_LOAD', 'Twitter' );
		}

	    $my         = CFactory::getUser();
	    $consumer   = plgCommunityTwitter::getConsumer();
	    $oauth    	= JTable::getInstance( 'Oauth' , 'CTable' );

	    ob_start();

		if( !$oauth->load( $my->id , 'twitter') || empty($oauth->accesstoken) )
		{
			$callback      = JURI::root().'index.php?option=com_community&view=oauth&task=callback&app=twitter';

		    $oauth->userid        = $my->id;
		    $oauth->app             = 'twitter';
		    $code = $consumer->request(
			    'POST',
			    $consumer->url('oauth/request_token', ''),
			    array(
			      'oauth_callback' => $callback
			    )
			  );

			if ($code == 200) {
				$session = JFactory::getSession();
			    $session->set('oauth',$consumer->extract_params($consumer->response['response']));
			    $temp_credentials = $session->get('oauth')['oauth_token'];
			    $authurl = $consumer->url("oauth/authorize", '') .  "?oauth_token={$session->get('oauth')['oauth_token']}";
			  } else {
			  	$temp_credentials = null;
			  	$authurl = null;
			    //echo 'false;';//outputError($tmhOAuth);
			  }

		    //$temp_credentials = $consumer->getRequestToken($callback);
			$oauth->requesttoken	= serialize( $temp_credentials );

			$oauth->store();
		?>
		<?php if($code==200){?>
		<div><?php echo JText::_('COM_COMMUNITY_TWITTER_LOGIN');?></div>
            <a href="<?php echo $authurl;?>"><img src="<?php echo JURI::root(true);?>/components/com_community/assets/twitter.png" border="0" alt="here" /></a>
		<?php }else{?>
		<div><?php echo JText::_('COM_COMMUNITY_TWITTER_FAILED_REQUEST_TOKEN');?></div>
		<?php }?>
		<?php
		}
		else
		{
		    //User is already authenticated and we have the proper tokens to fetch data.
		    $url    = CRoute::_( 'index.php?option=com_community&view=oauth&task=remove&app=twitter' );
		?>
		    <div><?php echo JText::sprintf('COM_COMMUNITY_TWITTER_REMOVE_ACCESS' , $url );?></div>
		<?php
		}
		$html   = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
     * Gets the html content of the Twitter login
     *
     * @return String the html data
     */
    static public function getLoginHTML() {
        $my = CFactory::getUser();
        $session = JFactory::getSession();

        if ($my->id != 0) {
            return '';
        }

        JFactory::getLanguage()->load('com_community');
        $config = CFactory::getConfig();
        $consumer_key = $config->get('twitterconnectkey');
        $consumer_secret = $config->get('twitterconnectsecret');

        $configuration = array(
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
            'bearer'          => base64_encode($consumer_key.':'.$consumer_secret),
            'curl_ssl_verifypeer' => true
        );

        $consumer = new tmhOAuth($configuration);
        $callback = JUri::root() . 'index.php/component/community/oauth/callback/twitter/login';
		$code = $consumer->request(
			'POST',
			$consumer->url('oauth/request_token', ''),
			array(
				'oauth_callback' => $callback
			)
		);

		if ($code == 200) {
			$session->set('twitter_oauth',$consumer->extract_params($consumer->response['response']));
			$temp_credentials = $session->get('twitter_oauth')['oauth_token'];
			$authurl = $consumer->url("oauth/authorize", '') .  "?oauth_token={$session->get('twitter_oauth')['oauth_token']}";
		} else {
			$temp_credentials = null;
			$authurl = null;
			//echo 'false;';//outputError($tmhOAuth);
		}

        $tmpl = new CTemplate();
        $tmpl->set('authurl', $authurl);
        $tmpl->set('code', $code);
        $tmpl->set('config', $config);

        return $tmpl->fetch('twitter.button');
    }

    static public function getUserInfo() {
        $config = CFactory::getConfig();
        $session = JFactory::getSession();
        
        if ($session->get('twitter_userinfo')) {
            return $session->get('twitter_userinfo');
        }

        if ($session->get('twitter_oauth')) {
            $consumer_key = $config->get('twitterconnectkey');
            $consumer_secret = $config->get('twitterconnectsecret');
            
            $configuration = array(
                'consumer_key' => $consumer_key,
                'consumer_secret' => $consumer_secret,
                'user_token' => $session->get('twitter_oauth')['oauth_token'],
                'user_secret' => $session->get('twitter_oauth')['oauth_token_secret'],
                'bearer' => base64_encode($consumer_key.':'.$consumer_secret),
                'curl_ssl_verifypeer' => true
            );

            $consumer = new tmhOAuth($configuration);

            $consumer->config['user_token'] = $session->get('twitter_oauth_token');
            $consumer->config['user_secret'] = $session->get('twitter_oauth')['oauth_token_secret'];

            $code = $consumer->request(
                    'POST', $consumer->url('oauth/access_token', ''), array(
                    'oauth_verifier' => $session->get('twitter_oauth_verifier')
                )
            );

            if ($code == 200) {
                $user = $consumer->extract_params($consumer->response['response']);

                $consumer->config['user_token'] = $user['oauth_token'];
                $consumer->config['user_secret'] = $user['oauth_token_secret'];
                
                $params = array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true');

                $code = $consumer->request('GET', $consumer->url('1.1/account/verify_credentials.json'), $params);

                if ($code == 200) {
                    $respond = json_decode($consumer->response['response']);
                    $userInfo = (array)$respond;
                    $session->set('twitter_userinfo', $userInfo);
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    public function mapAvatar($avatarUrl = '', $joomlaUserId = 0, $twitterId = 0, $addWaterMark = false) {
        $image = '';

        if (!empty($avatarUrl)) {
            // Make sure user is properly added into the database table first
            $user = CFactory::getUser($joomlaUserId);

            // Store image on a temporary folder.
            $tmpPath = JPATH_ROOT . '/images/originalphotos/twitter_connect_' . $twitterId;

            // Need to extract the non-https version since it will cause
            // certificate issue
            //$avatarUrl = str_replace('https://', 'http://', $avatarUrl);

            $source = CRemoteHelper::getContent($avatarUrl, true);
            list( $headers, $source ) = explode("\r\n\r\n", $source, 2);
            JFile::write($tmpPath, $source);
            
            // @todo: configurable width?
            $imageMaxWidth = 160;

            // Get a hash for the file name.
            $fileName = JApplicationHelper::getHash($twitterId . time());
            $hashFileName = CStringHelper::substr($fileName, 0, 24);

            // $uri_parts = explode('?',$avatarUrl, 2);
            // $extension = CStringHelper::substr($uri_parts[0], CStringHelper::strrpos($uri_parts[0], '.'));
            // get mime type
            $type = 'image/jpg';
            if (preg_match("/content-type\s*:\s*(\w+.?\w+)/i", $headers, $match) !== false) {
                $type = $match[1];
            }

            if ($type == 'image/jpg' || $type == 'image/jpeg') {
                $extension = '.jpg';
            } else if ($type == 'image/png') {
                $extension = '.png';
            } else if ($type == 'image/gif') {
                $extension = '.gif';
            } else {
                $extension = '.jpg';
            }

            //@todo: configurable path for avatar storage?
            $config = CFactory::getConfig();
            $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';
            $storageImage = $storage . '/' . $hashFileName . $extension;
            $storageThumbnail = $storage . '/thumb_' . $hashFileName . $extension;
            $image = $config->getString('imagefolder') . '/avatar/' . $hashFileName . $extension;
            $thumbnail = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . $extension;

            $userModel = CFactory::getModel('user');

            // Only resize when the width exceeds the max.
            CImageHelper::resizeProportional($tmpPath, $storageImage, $type, $imageMaxWidth);
            CImageHelper::createThumb($tmpPath, $storageThumbnail, $type);

            if ($addWaterMark) {
                // Get the width and height so we can calculate where to place the watermark.
                list( $watermarkWidth, $watermarkHeight ) = getimagesize(TWITTER_FAVICON);
                list( $imageWidth, $imageHeight ) = getimagesize($storageImage);
                list( $thumbWidth, $thumbHeight ) = getimagesize($storageThumbnail);

                CImageHelper::addWatermark($storageImage, $storageImage, $type, TWITTER_FAVICON, ( $imageWidth - $watermarkWidth), ( $imageHeight - $watermarkHeight));
                CImageHelper::addWatermark($storageThumbnail, $storageThumbnail, $type, TWITTER_FAVICON, ( $thumbWidth - $watermarkWidth), ( $thumbHeight - $watermarkHeight));
            }
        
            // Update the CUser object with the correct avatar.
            $user->set('_thumb', $thumbnail);
            $user->set('_avatar', $image);

            // @rule: once user changes their profile picture, storage method should always be file.
            $user->set('_storage', 'file');

            $userModel->setImage($joomlaUserId, $image, 'avatar');
            $userModel->setImage($joomlaUserId, $thumbnail, 'thumb');

            $user->save();
        }
    }

    /**
     * Maps a user profile with JomSocial's default custom values
     *
     *  @param  Array   User values
     * */
    public function mapProfile($values, $userId) {
        $profileModel = CFactory::getModel('Profile');

        foreach ($this->_fields as $field => $fieldCodes) {
            // Test if value really exists and it isn't empty.
            if (isset($values[$field]) && !empty($values[$field])) {
                switch ($field) {
                    case 'birthday':
                        $date = JDate::getInstance($values[$field]->format('Y-m-d'));

                        $profileModel->updateUserData($fieldCodes, $userId, $date->toSql());

                        break;
                    case 'gender':
                        $gender = 'COM_COMMUNITY_'.strtoupper($values[$field]);
                        if (!empty($gender)) {
                            $profileModel->updateUserData($fieldCodes, $userId, $gender);
                        }
                        break;
                    default:
                        if (is_array($fieldCodes)) {
                            // Facebook library returns an array of values for certain fields so we need to manipulate them differently.
                            foreach ($fieldCodes as $fieldData => $fieldCode) {
                                if (isset($values[$field][$fieldData])) {
                                    $profileModel->updateUserData($fieldCode, $userId, $values[$field][$fieldData]);
                                }
                            }
                        } else {
                            if (!empty($values[$field])) {
                                $profileModel->updateUserData($fieldCodes, $userId, $values[$field]);
                            }
                        }
                        break;
                }
            }
        }
        return false;
    }
}
