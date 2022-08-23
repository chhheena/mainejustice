<?php
/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class mxYouTuber_Model_Data extends mxYouTuber_Model_Base{

    const CACHE_KEY = 'plg_system_youtuber';
    private $_cache = null;

    public function getVideo($id) {
        if(is_array($id) && count($id)==0){
            return array();
        }
        $response = $this->getData('videos', array(
            'part' => 'snippet,statistics,contentDetails',
            'maxResults' => (is_array($id) ? count($id) : 1),
            'id' => (is_array($id) ? implode(',', $id) : $id)
        ));

        if (!isset($response['items'][0])) {
            if (is_array($id)) {
                throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_VIDEOS_IDS_NOT_FOUND'), implode(', ', $id)));
            } else {
                throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_VIDEO_ID_NOT_FOUND'), $id));
            }
        }
        
        foreach($response['items'] as $k=>$item){
            if(empty($item['snippet']['resourceId']['videoId'])){
                $response['items'][$k]['snippet']['resourceId']['videoId'] = $item['id'];
            }
            
        }

        return (is_array($id) ? $response['items'] : $response['items'][0]);
    }

    public function getChannelIDByUser($user) {
        $response = $this->getData('channels', array(
            'part' => 'id',
            'forUsername' => $user
        ));
        if (!isset($response['items'][0])) {
            throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_CHANNEL_FOR_USER_NOT_FOUND'), $user));
        }

        return $response['items'][0]['id'];
    }

    public function getChannel($id) {
        $response = $this->getData('channels', array(
            'part' => 'snippet,contentDetails,brandingSettings,statistics',
            'id' => $id
        ));

        if (!isset($response['items'][0])) {
            throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_CHANNEL_ID_NOT_FOUND'), $id));
        }

        return $response['items'][0];
    }
    
    public function getPlaylistModel($playListID){
        if(!class_exists('mxYouTuber_Model_Data_Playlist')){
            require_once(JPATH_ROOT . '/plugins/system/youtuber/models/data_playlist.php');
        }
        return new mxYouTuber_Model_Data_Playlist($playListID, $this);
    }

    public function getPlaylistItems($id, $start, $length, $exceptVideos=null) {
        $model = $this->getPlaylistModel($id);
        return $model->getItems($start, $length, $exceptVideos);
    }

    public function getPlaylists($channelID) {
        $params = array(
            'part' => 'snippet',
            'channelId' => $channelID,
            'maxResults' => 50
        );
        $response = $this->getData('playlists', $params);

        if (isset($response['items']) && is_array($response['items']) && count($response['items']) == 0) {
            return array();
        }
        if (!isset($response['items'][0])) {
            throw new Exception(sprintf(JText::_('PLG_SYSTEM_YOUTUBER_PLAYLIST_FOR_CHANNEL_ID_NOT_FOUND'), $channelID));
        }

        return $response['items'];
    }

    private function getRequestURI($type, $data) {
        $data['key'] = $this->getConfig()->get('googleBrowserKey','');
        if(empty($data['key'])){
            throw new Exception(JText::_('PLG_SYSTEM_YOUTUBER_SET_OAUTH_ID'));
        }
        return $this->getApiData('URI', 'api') . $type . '?' . http_build_query($data);
    }

    public function getData($type, $data) {
        $URI = $this->getRequestURI($type, $data);
        $qID = md5($URI);
        $cache = $this->getCache();
        $cached = $cache->get($qID);
        $jURI = JUri::getInstance();

        if ($this->getConfig()->get('caching',true) && $cached) {
            return json_decode($cached, true);
        }
        
        $httpOption = new Registry;
        
        if (class_exists('\Joomla\CMS\Http\HttpFactory')) {
            $httpFactory = new \Joomla\CMS\Http\HttpFactory;
        } else if (class_exists('JHttpFactory')) {
            $httpFactory = new JHttpFactory;
        } else {
            $httpFactory = new HttpFactory;
        }
        $http = $httpFactory::getHttp($httpOption);
            
        try{    
            $responce = $http->get($URI, array(
                'Accept' => 'application/json',
                'Referer' => $jURI->getHost()
                    ), 10);
        }
        catch(\Exception $e){
            if($e->getMessage()=='SSL certificate problem: certificate has expired'){
                $http = $httpFactory::getHttp($httpOption, 'Socket');
                $responce = $http->get($URI, array(
                    'Accept' => 'application/json',
                    'Referer' => $jURI->getHost()
                        ), 10);
            }
            else{
                throw new \Exception($e->getMessage());
            }
        }
        
        if ((int) $responce->code != 200) {
            throw new Exception(JText::_('PLG_SYSTEM_YOUTUBER_SERVER_RESPONCE_ERROR') . (!empty($responce->body) ? '<pre>' . print_r($responce->body, true) . '</pre>' : ''));
        }
        $result = $responce->body;

        if ($this->getConfig()->get('caching',true)) {
            $cache->store($result, $qID);
        } else {
            $cache->remove($qID);
        }

        return json_decode($result, true);
    }
    
    private function getApiData($type, $_data='') {
        static $_result;
        if (!is_array($_result)) {
            $_result = array();
        }
        $key = 'api_data_'.md5($type.$_data);
        if (isset($_result[$key])) {
            return $_result[$key];
        }
        $cache = $this->getCache(604800);//one week in seconds
        $cacheID = $key;
        $cached = $cache->get($cacheID);
        if($cached){
            return $cached;
        }
        $config = $this->getConfig();
        $URI = JUri::getInstance();

        $httpOption = new Registry;
        $httpOption->set('userAgent', 'YouTubeR');
        $httpOption->set('transport.curl', [CURLOPT_SSL_VERIFYPEER=>0]);

        if (class_exists('\Joomla\CMS\Http\HttpFactory')) {
            $httpFactory = new \Joomla\CMS\Http\HttpFactory;
        } else if (class_exists('JHttpFactory')) {
            $httpFactory = new JHttpFactory;
        } else {
            $httpFactory = new HttpFactory;
        }
        $http = $httpFactory::getHttp($httpOption);
        
        try{
            $response = $http->post('http://api.allforjoomla.com/youtuber/getUrl/?scope='.$_data, array(
                'domain' => $URI->getHost(),
                'license_key' => $config->get('purchase_code', ''),
            ), array(
                'Accept' => 'application/json',
                'Referer' => JURI::base(false)
                    ), 20);
        }
        catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
        
        
        if ((int)$response->code != 200) {
            throw new Exception('API responce error: ' . print_r($response, true));
        }
        
        if(strpos($response->body, '{"code":') !== false && strpos($response->body, '{"code":') !== 0) {
            $response->body = utf8_substr($response->body, strpos($response->body, '{"code":'));
        }
        
        $result = json_decode($response->body, true);
        if (!is_array($result) || !isset($result['code'])) {
            throw new Exception('API responce error: ' . json_last_error_msg());
        }
        if ((int) $result['code'] != 200) {
            throw new Exception('API error: ' . $result['body']);
        }
        $_result[$key] = $result['body'];
        $cache->store($result['body'], $cacheID);
        return $result['body'];
    }
    
    public function getCache($lifetime=0){
        if($lifetime==0){
            $lifetime = $this->getConfig()->get('cache_lifetime',3600);
        }
        if(is_null($this->_cache)){
            $this->_cache = [];
        }
        if(!isset($this->_cache[$lifetime])){
            $cacheLifetime = round($lifetime/60);
            $conf = JFactory::getConfig();
            $lang = JFactory::getLanguage();
            $this->_cache[$lifetime] = JCache::getInstance('', array(
                'defaultgroup' => self::CACHE_KEY,
                'cachebase' => $conf->get('cache_path', JPATH_ROOT . '/cache'),
                'language' => $lang->getTag(),
                'lifetime' => max($cacheLifetime, 60),
                'caching' => (bool)$this->getConfig()->get('caching',true)
            ));
        }
        return $this->_cache[$lifetime];
    }
    
    

}
