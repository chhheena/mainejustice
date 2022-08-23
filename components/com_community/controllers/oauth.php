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

class CommunityOauthController extends CommunityBaseController {

    public function callback() {
        $mainframe = JFactory::getApplication();
        $session = JFactory::getSession();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $denied = $jinput->get('denied', '', 'NONE');
        $app = $jinput->get('app', '', 'STRING');
        $oauth_verifier = $jinput->get('oauth_verifier', '', 'STRING');
        $oauth_token = $jinput->get('oauth_token', '', 'STRING');
        $verify = $jinput->get('verify', '', 'NONE');
        $isLogin = $jinput->get('login', '', 'NONE');

        if ($app == 'google') {
            $data = array(
                'id' => $jinput->get('googleid', '', 'STRING'),
                'name' => urldecode($jinput->get('googlename', '', 'STRING')),
                'email' => urldecode($jinput->get('googleemail', '', 'STRING')),
                'profile' => urldecode($jinput->get('googlepic', '', 'STRING'))
            );

            $session->set('google_data', $data);

            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage&googleid=' . $jinput->get('googleid', '', 'STRING'), false));
        }

        if ($isLogin && $oauth_verifier && $oauth_token) {
            $session->set('twitter_oauth_verifier', $oauth_verifier);
            $session->set('twitter_oauth_token', $oauth_token);

            $url = CRoute::_('index.php?option=com_community&view=frontpage&oauth_token=' . $jinput->request->get('oauth_token') . '&oauth_verifier=' . $jinput->request->get('oauth_verifier'), false);

            $mainframe->redirect($url . '&twitterlogin=true');
        } else if ($isLogin && !empty($denied)) {
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage', false));
        }

        $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
        $consumer = plgCommunityTwitter::getConsumer();

        if ($oauth_verifier && empty($verify) && $session->get('access_token') == '') {
            $consumer->config['user_token'] = $session->get('oauth')['oauth_token'];
            $consumer->config['user_secret'] = $session->get('oauth')['oauth_token_secret'];

            $code = $consumer->request(
                    'POST', $consumer->url('oauth/access_token', ''), array(
                    'oauth_verifier' => $jinput->request->get('oauth_verifier')
                    )
            );

            if ($code == 200) {
                $session->set('access_token',$consumer->extract_params($consumer->response['response']));
                //$session->clear('oauth');

                $instance = JURI::getInstance();
                $url = JURI::getInstance()->toString();
                $mainframe->redirect($url . '&verify=true');
            } else {
                echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
                return;
            }
        } elseif ($session->get('access_token')){

        }

        if (empty($app)) {
            echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
            return;
        }

        if ($my->id == 0) {
            echo JText::_('COM_COMMUNITY_INVALID_ACCESS');
            return;
        }

        if (!empty($denied)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_OAUTH_APPLICATION_ACCESS_DENIED_WARNING'));
            $mainframe->redirect($url);
        }

        $oauth = JTable::getInstance('Oauth', 'CTable');
        if ($oauth->load($my->id, $app)) {
            $oauth->userid = $my->id;
            $oauth->app = $app;

            try {
                $oauth->accesstoken = serialize($session->get('access_token'));
                $session->clear('accesstoken');
            } catch (Exception $error) {
                $mainframe->enqueueMessage($error->getMessage(), 'error');
                $mainframe->redirect($url);
            }

            if (!empty($oauth->accesstoken)) {
                $oauth->store();
            }
            $msg = JText::_('COM_COMMUNITY_OAUTH_AUTHENTICATION_SUCCESS');
            $mainframe->enqueueMessage($msg);
            $mainframe->redirect($url);
        }
    }

    public function remove() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $app = $jinput->get('app', '', 'NONE');

        if (empty($app)) {
            echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
            return;
        }

        if ($my->id == 0) {
            echo JText::_('COM_COMMUNITY_INVALID_ACCESS');
            return;
        }
        $oauth = JTable::getInstance('Oauth', 'CTable');
        if (!$oauth->load($my->id, $app)) {
            $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_OAUTH_LOAD_APPLICATION_ERROR'));
            $mainframe->redirect($url);
        }

        $oauth->delete();
        $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
        $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_OAUTH_DEAUTHORIZED_APPLICATION_SUCCESS'));
        $mainframe->redirect($url);
    }

}
