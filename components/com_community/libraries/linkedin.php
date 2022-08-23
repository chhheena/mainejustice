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


// Core file is required since we need to use CFactory
require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

// check if FB library already available or not
if (!class_exists('Linkedin')) {
    // Need to include Facebook's PHP API library so we can utilize them.
    require_once( JPATH_ROOT . '/components/com_community/libraries/composer/autoload.phar' );
}

/**
 * Wrapper class for Linkedin API.
 * */
class CLinkedin {
    
    var $_name = 'Linkedin';

    /**
     * Gets the html content of the LinkedIn login
     *
     * @return String the html data
     */
    static public function getLoginHTML()
    {
        $my = CFactory::getUser();
        $session = JFactory::getSession();

        if ($my->id != 0) {
            return '';
        }

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $code = $jinput->get('code', '', 'NONE');
        $state = $jinput->get('state', '', 'NONE');

        JFactory::getLanguage()->load('com_community');
        $config = CFactory::getConfig();

        $client_id = $config->get('linkedinclientid');
        $client_secret = $config->get('linkedinsecret');

        $callback = JURI::root().'index.php?option=com_community&view=frontpage&login=1';

        $linkedIn = new League\OAuth2\Client\Provider\LinkedIn([
            'clientId'          => $client_id,
            'clientSecret'      => $client_secret,
            'redirectUri'       => $callback,
        ]);

        $authurl = $linkedIn->getAuthorizationUrl();
        
        if ($code) {
            try {
                $token = $linkedIn->getAccessToken('authorization_code', [
                    'code' => $code
                ]);

                $user = $linkedIn->getResourceOwner($token);

                $data = array(
                    'id' => $user->getAttribute('id'),
                    'name' => $user->getAttribute('localizedFirstName') . ' ' . $user->getAttribute('localizedLastName'),
                    'email' => $user->getEmail(),
                    'profile' => $user->getAttribute('profilePicture')['displayImage~']['elements'][0]['identifiers'][0]['identifier']
                );

                $session->set('linkedin_data', $data);

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage&linkedincode=' . $code, false));

            } catch (Exception $e) {
                $session->clear('linkedin_data');

                $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage', false));
            }
        }

        $tmpl = new CTemplate();
        $tmpl->set('authurl', $authurl);
        $tmpl->set('config', $config);

        return $tmpl->fetch('linkedin.button');
    }

    static public function mapAvatar($avatarUrl = '', $joomlaUserId = 0, $linkedinId = 0, $addWaterMark = false) {
        $image = '';

        if (!empty($avatarUrl)) {
            // Make sure user is properly added into the database table first
            $user = CFactory::getUser($joomlaUserId);

            // Store image on a temporary folder.
            $tmpPath = JPATH_ROOT . '/images/originalphotos/linkedin_connect_' . $linkedinId;

            // Need to extract the non-https version since it will cause
            // certificate issue
            //$avatarUrl = str_replace('https://', 'http://', $avatarUrl);

            $source = CRemoteHelper::getContent($avatarUrl, true);
            list( $headers, $source ) = explode("\r\n\r\n", $source, 2);
            JFile::write($tmpPath, $source);
            
            // @todo: configurable width?
            $imageMaxWidth = 160;

            // Get a hash for the file name.
            $fileName = JApplicationHelper::getHash($linkedinId . time());
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
                list( $watermarkWidth, $watermarkHeight ) = getimagesize(LINKEDIN_FAVICON);
                list( $imageWidth, $imageHeight ) = getimagesize($storageImage);
                list( $thumbWidth, $thumbHeight ) = getimagesize($storageThumbnail);

                CImageHelper::addWatermark($storageImage, $storageImage, $type, LINKEDIN_FAVICON, ( $imageWidth - $watermarkWidth), ( $imageHeight - $watermarkHeight));
                CImageHelper::addWatermark($storageThumbnail, $storageThumbnail, $type, LINKEDIN_FAVICON, ( $thumbWidth - $watermarkWidth), ( $thumbHeight - $watermarkHeight));
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
}