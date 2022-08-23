<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class RssFactoryHelper
{
    protected static $extension = 'com_rssfactory';
    private static $sqlMode;

    public static function addSubmenu($vName)
    {
        if (4 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
            return;
        }

        JLoader::register('FactoryTextRss', JPATH_ADMINISTRATOR . '/components/' . self::$extension . '/helpers/factory/textRss.php');

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_feeds'),
            'index.php?option=' . self::$extension . '&view=feeds',
            $vName == 'feeds'
        );

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_comments'),
            'index.php?option=' . self::$extension . '&view=comments',
            $vName == 'comments'
        );

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_ads'),
            'index.php?option=' . self::$extension . '&view=ads',
            $vName == 'ads'
        );

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_submitted_feeds'),
            'index.php?option=' . self::$extension . '&view=submittedfeeds',
            $vName == 'submittedfeeds'
        );

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_categories'),
            'index.php?option=com_categories&extension=' . self::$extension,
            $vName == 'categories'
        );

        if (RssFactoryHelper::isUserAuthorised('backend.settings')) {
            JHtmlSidebar::addEntry(
                FactoryTextRss::_('submenu_configuration'),
                'index.php?option=' . self::$extension . '&view=configuration',
                $vName == 'configuration'
            );
        }

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_backup'),
            'index.php?option=' . self::$extension . '&view=backup',
            $vName == 'backup'
        );

        JHtmlSidebar::addEntry(
            FactoryTextRss::_('submenu_about'),
            'index.php?option=' . self::$extension . '&view=about',
            $vName == 'about'
        );
    }

    public static function getSiteIcon($id, $url)
    {
        if (!$url) {
            return false;
        }

        $ico_path = JPATH_SITE . '/media/com_rssfactory/icos';
        $ico_name = 'ico_' . md5($id);

        $host = parse_url($url);
        $picurl = 'http://' . $host['host'] . '/favicon.ico';

        try {
            $loader = new Elphin\IcoFileLoader\IcoFileService;
            $im = $loader->extractIcon($picurl, 32, 32);
            imagepng($im, $ico_path . DS . $ico_name . '.png');
        }
        catch (Exception $exception) {
            return false;
        }

        return true;
    }

    public static function remoteReadUrl($uri, $use_http_headers = true, $returnInfo = false)
    {
        $ret = false;

        if (function_exists('curl_init')) {
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $uri);
            curl_setopt($handle, CURLOPT_MAXREDIRS, 20);
            curl_setopt($handle, CURLOPT_AUTOREFERER, true);
            curl_setopt($handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)');
            curl_setopt($handle, CURLOPT_ENCODING, '');
            curl_setopt($handle, CURLOPT_HTTPHEADER,
                array(
                    'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
                    'Accept-Language: en,de-de;q=0.8,de;q=0.5,en-us;q=0.3',
                    //'Accept-Encoding:deflate',
                    'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                    'Keep-Alive: 300',
                    'Connection: keep-alive',
                    'Pragma: no-cache',
                    'Cache-Control: no-cache')
            );

            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handle, CURLOPT_TIMEOUT, 20);

            $cookie = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/cookie.txt';
            $temp = '';
            file_put_contents($cookie, $temp);
            curl_setopt($handle, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt($handle, CURLOPT_COOKIEFILE, $cookie);

            $buffer = curl_exec($handle);

            $ret = $buffer;
            if ($returnInfo) {
                $ret = array('info' => curl_getinfo($handle), 'buffer' => $buffer);
            }
            curl_close($handle);
        } else if (ini_get('allow_url_fopen')) {
            $fp = @fopen($uri, 'r');
            if (!$fp)
                return false;
            stream_set_timeout($fp, 20);
            $linea = '';
            while ($remote_read = fread($fp, 4096)) {
                $linea .= $remote_read;
            }

            $info = stream_get_meta_data($fp);
            fclose($fp);

            if ($info['timed_out']) {
                return false;
            }

            $ret = $linea;
            if ($returnInfo) {
                $redirectUrls = array();
                foreach ($info['wrapper_data'] as $inf) {
                    if (preg_match('#^Location: (.*?)$#i', $inf, $m)) {
                        $redirectUrls[] = $m[1];
                    }
                }
                $redirectUrls = array_reverse($redirectUrls);

                if (strpos($redirectUrls[0], 'http') !== 0) {
                    $relativeUrl = $redirectUrls[0];

                    while (strpos(reset($redirectUrls), 'http') !== 0) {
                        array_shift($redirectUrls);
                    }

                    $uri = JURI::getInstance(reset($redirectUrls));
                    $siteUri = $uri->toString(array('scheme', 'host'));

                    $url = $siteUri . $relativeUrl;
                } else {
                    $url = $redirectUrls[0];
                }

                $ret = array('info' => array('url' => $url), 'buffer' => $linea);
            }
        }

        return $ret;
    }

    public static function parseFullArticle($url, $rules, $debug = false)
    {
        JLoader::register('RssFactoryRule', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/rules/rule.php');

        $url = trim($url);

        $page = self::readUrlAndConvertUtf8($url);
        $content = array();

        // Remove inline javascript.
        $page = preg_replace('#<script.*?>.*?</script>#is', '', $page);
        $page = preg_replace('#<script.*?/>#is', '', $page);

        // Remove inline css.
        $page = preg_replace('#<style.*?>.*?</style>#is', '', $page);

        foreach ($rules as $rule) {
            if (is_object($rule)) {
                $rule = (array)$rule;
            }

            if (!isset($rule['enabled']) || !$rule['enabled']) {
                continue;
            }

            $params = isset($rule['params']) ? $rule['params'] : '';

            $instance = RssFactoryRule::getInstance($rule['type']);
            $content[] = $instance->getParsedOutput($params, $page, $content, $debug);
        }

        return implode("\n", $content);
    }

    public static function isUserAuthorised($action)
    {
        $user = JFactory::getUser();

        $notPublic = array('frontend.favorites');
        if ($user->guest && in_array($action, $notPublic)) {
            return false;
        }

        return $user->authorise($action, self::$extension);
    }

    public static function getPseudoCronHtml()
    {
        $password = md5(time() . mt_rand(0, 99999));
        $session = JFactory::getSession();
        $session->set('com_rssfactory.pseudocron.key', $password);

        $url = JUri::root() . '/components/com_rssfactory/helpers/refresh.php?type=pseudocron&password=' . $password;
        $name = 'com_rssfactory_pseudo_refresh';
        $attribs = 'style="width:0; height:0" frameborder="0" width="0" height="0"';

        return JHTML::iframe($url, $name, $attribs);
    }

    protected static function readUrlAndConvertUtf8($url)
    {
        $header = array(
            'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5',
            'Accept-Language: en,de-de;q=0.8,de;q=0.5,en-us;q=0.3',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Keep-Alive: 300',
            'Connection: keep-alive',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)';

        $cookie = JPATH_COMPONENT_ADMINISTRATOR . '/helpers/cookie.txt';
        $temp = '';
        file_put_contents($cookie, $temp);

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_USERAGENT      => $agent,
            CURLOPT_ENCODING       => '',
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_COOKIEJAR      => $cookie,
            CURLOPT_COOKIEFILE     => $cookie,
        );

        $ch = curl_init();

        curl_setopt_array($ch, $options);
        $data = self::curl_exec_utf8($ch, 20);

        if ($error = curl_errno($ch)) {
            throw new Exception(curl_error($ch), $error);
        }

        curl_close($ch);

        return $data;
    }

    protected static function curl_exec_utf8($ch, $redirects = 20)
    {
        $data = self::curl_exec_follow($ch, $redirects);

        if (!is_string($data)) {
            return $data;
        }

        unset($charset);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        /* 1: HTTP Content-Type: header */
        preg_match('@([\w/+]+)(;\s*charset=(\S+))?@i', $content_type, $matches);
        if (isset($matches[3]))
            $charset = $matches[3];

        /* 2: <meta> element in the page */
        if (!isset($charset)) {
            preg_match('@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i', $data, $matches);
            if (isset($matches[3]))
                $charset = $matches[3];
        }

        /* 3: <xml> element in the page */
        if (!isset($charset)) {
            preg_match('@<\?xml.+encoding="([^\s"]+)@si', $data, $matches);
            if (isset($matches[1]))
                $charset = $matches[1];
        }

        /* 4: PHP's heuristic detection */
        if (!isset($charset)) {
            $encoding = mb_detect_encoding($data);
            if ($encoding)
                $charset = $encoding;
        }

        /* 5: Default for HTML */
        if (!isset($charset)) {
            if (strstr($content_type, "text/html") === 0)
                $charset = "ISO 8859-1";
        }

        /* Convert it if it is anything but UTF-8 */
        /* You can change "UTF-8"  to "UTF-8//IGNORE" to
           ignore conversion errors and still output something reasonable */
        if (isset($charset) && strtoupper($charset) != "UTF-8")
            $data = iconv($charset, 'UTF-8', $data);

        return $data;
    }

    protected static function curl_exec_follow($ch, &$maxredirect = null)
    {
        $mr = $maxredirect === null ? 5 : intval($maxredirect);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            if ($mr > 0) {
                $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

                $rch = curl_copy_handle($ch);
                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                do {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if ($code == 301 || $code == 302) {
                            preg_match('/Location:(.*?)\n/', $header, $matches);
                            $newurl = trim(array_pop($matches));
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);
                curl_close($rch);
                if (!$mr) {
                    if ($maxredirect === null) {
                        trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                    } else {
                        $maxredirect = 0;
                    }
                    return false;
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
            }
        }
        return curl_exec($ch);
    }

    public static function setSqlMode()
    {
        $dbo = \Joomla\CMS\Factory::getDbo();
        $query = $dbo->setQuery('SELECT @@sql_mode;');
        self::$sqlMode = $query->loadResult();
        $exploded = explode(',', self::$sqlMode);

        foreach ($exploded as $i => $value) {
            if (in_array($value, ['ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES'])) {
                unset($exploded[$i]);
            }
        }

        $query = $dbo->setQuery('SET sql_mode="' . implode(',', $exploded) . '"');
        $query->execute();
    }

    public static function resetSqlMode()
    {
        $dbo = \Joomla\CMS\Factory::getDbo();

        $query = $dbo->setQuery('SET sql_mode="' . self::$sqlMode . '"');
        $query->execute();
    }
}
