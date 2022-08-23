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

\defined('_JCH_EXEC') or die('Restricted access');
use Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\SystemUri;
use JchOptimize\Platform\Hooks;
use JchOptimize\Platform\Utility;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\IterableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
class PageCache implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    /**
     * @var Registry
     */
    protected $params;
    /**
     * @var StorageInterface
     */
    protected $pageCacheStorage;
    /**
     * Cache id
     *
     * @var string
     */
    protected $cacheId;
    /**
     * Files system cache adapter used to store tags when another adapter is being used that isn't taggable and iterable
     *
     * @var TaggableInterface
     */
    protected $taggableCache;
    /**
     * Name of currently used cache adapter
     *
     * @var string
     */
    protected $adapter;
    /**
     * Indicates whether CaptureCache is used to store cache
     *
     * @var string
     */
    protected $isCaptureCache = \false;
    /**
     * @var array
     */
    protected $filters = [];
    /**
     * @var array
     */
    protected $lists = ['list_fullordering' => 'mtime ASC'];
    /**
     * @var bool
     */
    protected $enabled = \true;
    /**
     * @var bool
     */
    protected $isCachingSet = \false;
    /**
     * @var Input
     */
    protected $input;
    /**
     * Constructor
     *
     * @param   Registry           $params
     * @param   Input              $input
     * @param   StorageInterface   $pageCache
     * @param   TaggableInterface  $taggableCache
     */
    public function __construct(Registry $params, Input $input, StorageInterface $pageCacheStorage, TaggableInterface $taggableCache)
    {
        $this->params = $params;
        $this->input = $input;
        $this->pageCacheStorage = $pageCacheStorage;
        $this->taggableCache = $taggableCache;
        $reflection = new \ReflectionClass($this->pageCacheStorage);
        $this->adapter = $reflection->getShortName();
    }
    public function setFilter($key, $filter)
    {
        $this->filters[$key] = $filter;
    }
    public function setList($key, $list)
    {
        $this->lists[$key] = $list;
    }
    public function getItems() : array
    {
        $items = [];
        $iterableTaggableCache = $this->getIterableTaggableCache();
        //Temporarily set ttl to 0
        $existingTtl = $iterableTaggableCache->getOptions()->getTtl();
        $iterableTaggableCache->getOptions()->setTtl(0);
        try {
            foreach ($iterableTaggableCache->getIterator() as $cacheItem) {
                $tags = $iterableTaggableCache->getTags($cacheItem);
                $metaData = $iterableTaggableCache->getMetadata($cacheItem);
                if (empty($tags)) {
                    continue;
                }
                if ($tags[0] != 'pagecache') {
                    continue;
                }
                $url = $tags[1];
                $mtime = $metaData['mtime'];
                //Filter bu Time 1
                if (!empty($this->filters['filter_time-1'])) {
                    if (\time() < $mtime + (int) $this->filters['filter_time-1']) {
                        continue;
                    }
                }
                //Filter by Time 2
                if (!empty($this->filters['filter_time-2'])) {
                    if (\time() >= $mtime + (int) $this->filters['filter_time-2']) {
                        continue;
                    }
                }
                //Filter by URL
                if (!empty($this->filters['filter_search'])) {
                    if (\strpos($url, $this->filters['filter_search']) === \false) {
                        continue;
                    }
                }
                //Filter by device
                if (!empty($this->filters['filter_device'])) {
                    if ($tags[2] != $this->filters['filter_device']) {
                        continue;
                    }
                }
                //Filter by adapter
                if (!empty($this->filters['filter_adapter'])) {
                    if ($tags[3] != $this->filters['filter_adapter']) {
                        continue;
                    }
                }
                //Filter by HTTP Requests if we're not using Capture Cache
                if (!$this->isCaptureCache && !empty($this->filters['filter_http-request'])) {
                    if ($tags[4] != $this->filters['filter_http-request']) {
                        continue;
                    }
                }
                $item = [];
                $item['id'] = $cacheItem;
                $item['url'] = $tags[1];
                $item['device'] = $tags[2];
                $item['adapter'] = $tags[3];
                $item['http-request'] = 'no';
                $item['mtime'] = $metaData['mtime'];
                $items[] = $item;
            }
            $this->sortItems($items, $this->lists['list_fullordering']);
            if (!empty($this->lists['list_limit'])) {
                $items = \array_slice($items, 0, $this->lists['list_limit']);
            }
        } catch (ExceptionInterface|\Exception $e) {
            $this->logger->error('Error getting Page Cache items: ' . $e->getMessage());
        }
        //Reset configured ttl
        $iterableTaggableCache->getOptions()->setTtl($existingTtl);
        return $items;
    }
    public function disableCaching()
    {
        $this->enabled = \false;
        $this->isCachingSet = \true;
    }
    protected function sortItems(&$items, $fullOrdering)
    {
        list($orderBy, $dir) = \explode(' ', $fullOrdering);
        \usort($items, function ($a, $b) use($orderBy, $dir) {
            if ($dir == 'ASC') {
                return $a[$orderBy] <=> $b[$orderBy];
            }
            return $b[$orderBy] <=> $a[$orderBy];
        });
    }
    protected function getPageCacheId() : string
    {
        $parts = [];
        $parts[] = $this->adapter;
        $parts[] = $this->getCurrentPage();
        $parts[] = \serialize($this->params);
        $parts[] = $this->isCaptureCache;
        if (JCH_PRO && $this->params->get('pro_cache_platform', '0') && Utility::isMobile()) {
            $parts[] = '__MOBILE__';
        }
        //Add a value to the array that will be used to determine the page cache id
        $parts = Hooks::onPageCacheGetKey($parts);
        return \md5(\serialize($parts));
    }
    protected function getCurrentPage() : string
    {
        return SystemUri::toString();
    }
    public function store(string $html) : string
    {
        if ($this->getCachingEnabled()) {
            $html = $this->tagHtml($html);
            try {
                $data = ['body' => $html, 'headers' => Utility::getHeaders()];
                $this->pageCacheStorage->setItem($this->cacheId, $data);
                $tags = $this->getPageCacheTags();
                $iterableTaggableCache = $this->getIterableTaggableCache();
                //If we're not using the same storage to tag we'll need to cache an item to tag
                if ($this->pageCacheStorage !== $iterableTaggableCache) {
                    //Save an empty page using the same id then tag it
                    $iterableTaggableCache->setItem($this->cacheId, '<html><head><title></title></head><body></body></html>');
                }
                $iterableTaggableCache->setTags($this->cacheId, $tags);
            } catch (ExceptionInterface $e) {
                $this->logger->error('Error storing cache: ' . $e->getMessage());
            }
        }
        return $html;
    }
    public function setCaching() : void
    {
        //just return false with this filter if you don't want the page to be cached
        if (!Hooks::onPageCacheSetCaching()) {
            $this->disableCaching();
            return;
        }
        if ($this->input->server->get('REQUEST_METHOD') == 'POST' || $this->input->cookie->get('jch_optimize_no_cache_user_activity') == 'user_posted_form') {
            $this->disableCaching();
            return;
        }
        $this->enabled = $this->params->get('page_cache_select', 'jchoptimizepagecache') && Utility::isPageCacheEnabled($this->params) && Utility::isGuest() && !self::isExcluded($this->params) && $this->input->server->get('REQUEST_METHOD') === 'GET';
        $this->isCachingSet = \true;
    }
    /**
     * Returns the caching status if enabled or disabled. If caching wasn't explicitly set it will be set on
     * first call to this function
     *
     * @return bool
     */
    public function getCachingEnabled() : bool
    {
        if (!$this->isCachingSet) {
            $this->setCaching();
        }
        return $this->enabled;
    }
    /**
     * @throws \Exception
     */
    public function deleteCurrentPage()
    {
        $this->deleteItemByUrl($this->getCurrentPage());
    }
    /**
     * @throws \Exception
     */
    public function deleteItemByUrl($url)
    {
        $iterableTaggableCache = $this->getIterableTaggableCache();
        foreach ($iterableTaggableCache->getIterator() as $item) {
            $tags = $iterableTaggableCache->getTags($item);
            if ($tags[0] == 'pagecache' && $tags[1] == $url) {
                $this->deleteItemsByIds([$item]);
                break;
            }
        }
    }
    public function getIterableTaggableCache() : TaggableInterface
    {
        return $this->taggableCache;
    }
    public function deleteItemById(string $id) : bool
    {
        try {
            $this->pageCacheStorage->removeItem($id);
            $this->getIterableTaggableCache()->removeItem($id);
        } catch (ExceptionInterface|Exception $e) {
            $this->logger->error('Error deleting page cache item by id: ' . $e->getMessage());
            return \false;
        }
        return \true;
    }
    public function deleteItemsByIds(array $ids) : bool
    {
        try {
            $this->pageCacheStorage->removeItems($ids);
            //Check if any of these cache items are from another adapter
            $iterableTaggableCache = $this->getIterableTaggableCache();
            foreach ($iterableTaggableCache->getIterator() as $item) {
                if (\in_array($item, $ids)) {
                    $tags = $iterableTaggableCache->getTags($item);
                    if ($tags[3] != $this->adapter) {
                        $this->container->get($tags[3])->removeItem($item);
                    }
                    $iterableTaggableCache->removeItem($item);
                }
            }
        } catch (ExceptionInterface|Exception $e) {
            $this->logger->error('Error deleting page cache items by ids: ' . $e->getMessage());
            return \false;
        }
        return \true;
    }
    protected function isExcluded($params) : bool
    {
        $cache_exclude = $params->get('cache_exclude', array());
        if (Helper::findExcludes($cache_exclude, $this->getCurrentPage())) {
            return \true;
        }
        return \false;
    }
    public function tagHtml($html)
    {
        if (JCH_DEBUG) {
            $now = \date('l, F d, Y h:i:s A');
            $tag = "\n" . '<!-- Cached by JCH Optimize on ' . $now . ' GMT -->' . "\n" . '</body>';
            $html = \str_replace('</body>', $tag, $html);
        }
        return $html;
    }
    public function removeHtmlTag($html)
    {
        $search = '#<!-- Cached by JCH Optimize on .*? GMT -->\\n#';
        return \preg_replace($search, '', $html);
    }
    public function initialize()
    {
        $this->setCaching();
        $this->cacheId = $this->getPageCacheId();
        if ($this->input->server->get('REQUEST_METHOD') == 'POST') {
            if ($this->params->get('page_cache_exclude_form_users', '0')) {
                Hooks::onUserPostForm();
                if (!$this->input->cookie->get('jch_optimize_no_cache_user_activity') == 'user_posted_form') {
                    $options = ['httponly' => \true, 'expires' => \time() + (int) $this->params->get('page_cache_lifetime', '900')];
                    $this->input->cookie->set('jch_optimize_no_cache_user_activity', 'user_posted_form', $options);
                }
            }
            return;
        }
        if (!$this->params->get('page_cache_exclude_form_users', '0') && $this->input->cookie->get('jch_optimize_no_cache_user_activity') == 'user_posted_form') {
            Hooks::onUserPostFormDeleteCookie();
            $this->input->cookie->set('jch_optimize_no_cache_user_activity', '', ['expires' => 1]);
        }
        if (!$this->enabled) {
            return;
        }
        try {
            $data = $this->pageCacheStorage->getItem($this->cacheId);
            $data = Utility::prepareDataFromCache($data);
            if (!\is_null($data) && $this->input->cookie->get('jch_optimize_no_cache_user_activity') != 'user_posted_form') {
                if (!empty($data['body'])) {
                    $this->setCaptureCache($data['body']);
                }
                while (@\ob_end_clean()) {
                }
                Utility::outputData($data);
            }
        } catch (ExceptionInterface $e) {
            $this->logger->error('Error initializing page cache: ' . $e->getMessage());
        }
    }
    public function getAdapterName() : string
    {
        return $this->adapter;
    }
    public function deleteAllItems() : bool
    {
        try {
            //We also go through the cache using the iterator as there may be cache from other adapters as well
            $iterableTaggableCache = $this->getIterableTaggableCache();
            foreach ($iterableTaggableCache->getIterator() as $item) {
                $tags = $iterableTaggableCache->getTags($item);
                if ($tags[0] == 'pagecache') {
                    //Delete the cache
                    if ($tags[3] == $this->adapter) {
                        $this->pageCacheStorage->removeItem($item);
                    } else {
                        $this->container->get($tags[3])->removeItem($item);
                    }
                    //Delete the tag
                    $iterableTaggableCache->removeItem($item);
                }
            }
        } catch (Exception|ExceptionInterface $e) {
            $this->logger->error('Error deleting all page cache items: ' . $e->getMessage());
            return \false;
        }
        return \true;
    }
    protected function getPageCacheTags() : array
    {
        $device = Utility::isMobile() ? 'Mobile' : 'Desktop';
        return ['pagecache', $this->getCurrentPage(), $device, $this->adapter];
    }
    public function isCaptureCacheEnabled() : bool
    {
        return $this->isCaptureCache;
    }
    public function disableCaptureCache()
    {
        $this->isCaptureCache = \false;
    }
    public function getStorage() : StorageInterface
    {
        return $this->pageCacheStorage;
    }
    /**
     * To be overwritten by the CaptureCache class
     *
     * @return void
     */
    protected function setCaptureCache(string $html)
    {
    }
}
