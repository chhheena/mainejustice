<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\PageCache;

use Exception;
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\Core\SystemUri;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;
use _JchOptimizeVendor\Laminas\Cache\Pattern\CaptureCache as LaminasCaptureCache;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use function defined;
use function gzencode;
use function strpos;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function file_exists;
use function file_get_contents;
defined('_JCH_EXEC') or die('Restricted access');
class CaptureCache extends \JchOptimize\Core\PageCache\PageCache
{
    /**
     * @var LaminasCaptureCache
     */
    private $captureCache;
    /**
     * @var string
     */
    private $captureCacheId;
    /**
     * @var bool
     */
    protected $isCaptureCache = \true;
    /**
     * @var string
     */
    private $startHtaccessLine = '## BEGIN CAPTURE CACHE - JCH OPTIMIZE ##';
    /**
     * @var string
     */
    private $endHtaccessLine = '## END CAPTURE CACHE - JCH OPTIMIZE ##';
    public function __construct(Registry $params, Input $input, StorageInterface $pageCache, TaggableInterface $taggableCache, LaminasCaptureCache $captureCache)
    {
        parent::__construct($params, $input, $pageCache, $taggableCache);
        $this->captureCache = $captureCache;
        if ($this->params->get('pro_cache_platform', '0')) {
            $this->isCaptureCache = \false;
        }
        //Better not to cache for index.php to avoid confusion with CMS redirection to index.php
        $uri = new Uri($this->getCurrentPage());
        if ($uri->getPath() == SystemUri::basePath() . 'index.php' && empty($uri->getQuery())) {
            $this->isCaptureCache = \false;
        }
    }
    private function getCaptureCacheIdFromPage(string $page = '') : string
    {
        if ($page === '') {
            $page = $this->getCurrentPage();
        }
        $uri = new Uri($page);
        $id = $uri->getScheme() . '/' . $uri->getHost() . '/' . $uri->getPath() . '/' . $uri->getQuery();
        $id .= '/index.html';
        return $id;
    }
    public function getCaptureCacheIdFromPageCacheId(string $id) : string
    {
        $cache = $this->getIterableTaggableCache();
        //Temporarily set ttl to 0, so we can still get tags if cache expired
        $ttl = $cache->getOptions()->getTtl();
        $cache->getOptions()->setTtl(0);
        $tags = $cache->getTags($id);
        $cache->getOptions()->setTtl($ttl);
        if (!empty($tags[1])) {
            return $this->getCaptureCacheIdFromPage($tags[1]);
        }
        return '';
    }
    public function getItems() : array
    {
        $items = parent::getItems();
        $filteredItems = [];
        //set http-request tag if a cache file exists for this item
        foreach ($items as $item) {
            $captureCacheId = $this->getCaptureCacheIdFromPage($item['url']);
            $item['http-request'] = $this->captureCache->has($captureCacheId) ? 'yes' : 'no';
            //filter by HTTP Requests
            if (!empty($this->filters['filter_http-request'])) {
                if ($item['http-request'] != $this->filters['filter_http-request']) {
                    continue;
                }
            }
            $filteredItems[] = $item;
        }
        //If we're sorting by http-request we'll need to re-sort
        if (strpos($this->lists['list_fullordering'], 'http-request') === 0) {
            $this->sortItems($filteredItems, $this->lists['list_fullordering']);
        }
        return $filteredItems;
    }
    public function initialize()
    {
        $this->captureCacheId = $this->getCaptureCacheIdFromPage();
        //If user is logged in we'll need to set a cookie, so they won't see pages cached by another user
        if (!Utility::isGuest() && !$this->input->cookie->get('jch_optimize_no_cache_user_state') == 'user_logged_in') {
            $options = ['httponly' => \true];
            $this->input->cookie->set('jch_optimize_no_cache_user_state', 'user_logged_in', $options);
        } elseif (Utility::isGuest() && $this->input->cookie->get('jch_optimize_no_cache_user_state') == 'user_logged_in') {
            $options = ['expires' => 1];
            $this->input->cookie->set('jch_optimize_no_cache_user_state', '', $options);
        }
        if ($this->input->server->get('REQUEST_METHOD') == 'POST') {
            $this->deleteCaptureCacheDir();
        }
        parent::initialize();
    }
    public function store(string $html) : string
    {
        $html = parent::store($html);
        $this->setCaptureCache($html);
        return $html;
    }
    protected function setCaptureCache(string $html)
    {
        if ($this->enabled and $this->isCaptureCacheEnabled()) {
            try {
                $html = $this->tagCaptureCacheHtml($html);
                $this->captureCache->set($html, $this->captureCacheId);
                //Gzip
                $html = preg_replace('#and served using HTTP Request#', '\\0 (Gzipped)', $html);
                $htmlGz = gzencode($html, 9);
                $this->captureCache->set($htmlGz, $this->getGzippedCaptureCacheId($this->captureCacheId));
            } catch (Exception $e) {
                //Ignore
            }
        }
    }
    public function deleteItemById(string $id) : bool
    {
        try {
            $captureCacheId = $this->getCaptureCacheIdFromPageCacheId($id);
            $this->captureCache->remove($captureCacheId);
            $this->captureCache->remove($this->getGzippedCaptureCacheId($captureCacheId));
            return parent::deleteItemById($id);
        } catch (Exception $e) {
            $this->logger->error('Error deleting CaptureCache item by id:' . $e->getMessage());
            return \false;
        }
    }
    public function deleteItemsByIds(array $ids) : bool
    {
        try {
            foreach ($ids as $id) {
                $captureCacheId = $this->getCaptureCacheIdFromPageCacheId($id);
                $this->captureCache->remove($captureCacheId);
                $this->captureCache->remove($this->getGzippedCaptureCacheId($captureCacheId));
            }
            return parent::deleteItemsByIds($ids);
        } catch (Exception $e) {
            $this->logger->error('Error deleting CaptureCache items by ids: ' . $e->getMessage());
            return \false;
        }
    }
    private function getGzippedCaptureCacheId($id) : string
    {
        return $id . '.gz';
    }
    public function deleteAllItems() : bool
    {
        $this->deleteCaptureCacheDir();
        return parent::deleteAllItems();
    }
    public function updateHtaccess($negateState = \false)
    {
        $pluginState = (bool) (!Utility::isPageCacheEnabled($this->params, \true));
        //$negateState might be set to true if the plugin's state was just toggled by Ajax action so not yet registered
        //in the current state
        $state = $negateState ? !$pluginState : $pluginState;
        //If Capture Cache not enabled just clean htaccess and leave
        if ($state || !$this->params->get('pro_capture_cache_enable', '0') || $this->params->get('pro_cache_platform', '0') || !$this->isCaptureCache) {
            $this->cleanHtaccess();
            return;
        }
        $captureCacheDir = Paths::captureCacheDir();
        $relCaptureCacheDir = Paths::captureCacheDir(\true);
        $jchVersion = JCH_VERSION;
        $htaccessContents = <<<APACHECONFIG

{$this->startHtaccessLine}
<IfModule mod_headers.c>
\tHeader set X-Cached-By: "JCH Optimize v{$jchVersion}"
</IfModule>

<IfModule mod_rewrite.c>
\tRewriteEngine On
\t
\tRewriteRule "\\.html\\.gz\$" "-" [T=text/html,E=no-gzip:1,E=no-brotli:1,L]
\t
\t<IfModule mod_headers.c>
\t\t<FilesMatch "\\.html\\.gz\$" >
\t\t\tHeader append Content-Encoding gzip
\t\t\tHeader append Vary Accept-Encoding
\t\t</FilesMatch>
\t\t
\t\tRewriteRule .* - [E=JCH_GZIP_ENABLED:yes]
\t</IfModule>
\t
\t<IfModule !mod_headers.c>
\t\t<IfModule mod_mime.c>
\t\t \tAddEncoding gzip .gz
\t\t</IfModule>
\t\t
\t\tRewriteRule .* - [E=JCH_GZIP_ENABLED:yes]
\t</IfModule>
\t
\tRewriteCond %{ENV:JCH_GZIP_ENABLED} ^yes\$
\tRewriteCond %{HTTP:Accept-Encoding} gzip
\tRewriteRule .* - [E=JCH_GZIP:.gz]
\t
\tRewriteRule .* - [E=JCH_SCHEME:http]
\t
\tRewriteCond %{HTTPS} on [OR]
\tRewriteCond %{SERVER_PORT} ^443\$
\tRewriteRule .* - [E=JCH_SCHEME:https]
    
\tRewriteCond %{REQUEST_METHOD} ^GET 
\tRewriteCond %{HTTP_COOKIE} !jch_optimize_no_cache
\tRewriteCond "{$captureCacheDir}/%{ENV:JCH_SCHEME}/%{HTTP_HOST}%{REQUEST_URI}/%{QUERY_STRING}/index\\.html%{ENV:JCH_GZIP}" -f
\tRewriteRule .* "{$relCaptureCacheDir}/%{ENV:JCH_SCHEME}/%{HTTP_HOST}%{REQUEST_URI}/%{QUERY_STRING}/index.html%{ENV:JCH_GZIP}" [L]
</IfModule>
{$this->endHtaccessLine}

APACHECONFIG;
        $contents = $this->cleanHtaccess(\true);
        $endHtaccessLineRegex = preg_quote(\rtrim(Tasks::$endHtaccessLine, "# \n\r\t\v\x00"), '#') . '[^\\r\\n]*[\\r\\n]*';
        if (preg_match('#' . $endHtaccessLineRegex . '#', $contents)) {
            $updatedContents = preg_replace('#' . $endHtaccessLineRegex . '#', '\\0' . \PHP_EOL . $htaccessContents . \PHP_EOL, $contents);
        } else {
            $updatedContents = $htaccessContents . \PHP_EOL . $contents;
        }
        File::write($this->getHtaccessFile(), $updatedContents);
    }
    public function cleanHtaccess($returnContents = \false)
    {
        $htaccess = $this->getHtaccessFile();
        if (file_exists($htaccess)) {
            $contents = file_get_contents($htaccess);
            $endHtaccessLineRegex = preg_quote(\rtrim($this->endHtaccessLine, "# \n\r\t\v\x00")) . '[^\\r\\n]*[\\r\\n]*';
            $htaccessRegex = '@[\\r\\n]*' . $this->startHtaccessLine . '.*?' . $endHtaccessLineRegex . '@s';
            $cleanContents = preg_replace($htaccessRegex, \PHP_EOL, $contents, -1, $count);
            if ($returnContents) {
                return $cleanContents;
            }
            if ($count > 0) {
                File::write($htaccess, $cleanContents);
            }
        }
        $this->deleteCaptureCacheDir();
    }
    private function getHtaccessFile() : string
    {
        return Paths::rootPath() . '/.htaccess';
    }
    private function deleteCaptureCacheDir()
    {
        try {
            if (file_exists(Paths::captureCacheDir())) {
                Folder::delete(Paths::captureCacheDir());
            }
        } catch (Exception $e) {
            $this->logger->error('Error trying to delete Capture Cache dir: ' . $e->getMessage());
        }
    }
    private function tagCaptureCacheHtml($content)
    {
        return preg_replace('#Cached by JCH Optimize on .*? GMT#', '\\0 and served using HTTP Request', $content);
    }
}
