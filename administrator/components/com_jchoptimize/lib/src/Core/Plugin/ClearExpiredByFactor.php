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
namespace JchOptimize\Core\Plugin;

use FilesystemIterator;
use JchOptimize\Core\PageCache\PageCache;
use JchOptimize\Core\SystemUri;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Profiler;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Cache\Storage\Plugin\AbstractPlugin;
use _JchOptimizeVendor\Laminas\Cache\Storage\PostEvent;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use _JchOptimizeVendor\Laminas\EventManager\EventManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;
use function count;
use function file_exists;
use function random_int;
use function time;
class ClearExpiredByFactor extends AbstractPlugin implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $callback = [$this, 'clearExpiredByFactor'];
        $this->listeners[] = $events->attach('setItem.post', $callback, $priority);
        $this->listeners[] = $events->attach('setItems.post', $callback, $priority);
        $this->listeners[] = $events->attach('addItem.post', $callback, $priority);
        $this->listeners[] = $events->attach('addItems.post', $callback, $priority);
    }
    /**
     * @throws \Exception
     */
    public function clearExpiredByFactor(PostEvent $event)
    {
        $factor = $this->getOptions()->getClearingFactor();
        if ($factor && random_int(1, $factor) === 1) {
            $this->clearExpired();
        }
    }
    private function clearExpired()
    {
        JCH_DEBUG ? Profiler::start('ClearExpired') : null;
        /** @var Registry $params */
        $params = $this->container->get('params');
        /** @var TaggableInterface $taggableCache */
        $taggableCache = $this->container->get(TaggableInterface::class);
        /** @var StorageInterface $cache */
        $cache = $this->container->get(StorageInterface::class);
        /** @var PageCache $pageCache */
        $pageCache = $this->container->get(PageCache::class);
        $ttl = $cache->getOptions()->getTtl();
        $ttlPageCache = $pageCache->getStorage()->getOptions()->getTtl();
        $time = time();
        //Temporarily set taggable cache ttl to 0
        $existingTtl = $taggableCache->getOptions()->getTtl();
        $taggableCache->getOptions()->setTtl(0);
        foreach ($taggableCache->getIterator() as $item) {
            $tags = $taggableCache->getTags($item);
            $metaData = $taggableCache->getMetadata($item);
            $mtime = $metaData['mtime'];
            $deleteTag = \true;
            //If item was only used on this page once more than a minute ago it's safe to delete
            //Or if there are only one tag this cache was only used on one page so is safe to delete if expired
            //Or if the cache is now more than twice it's lifetime it's time to say bye.
            if ($tags === [SystemUri::toString()] && $time > $mtime + 60 || count($tags) === 1 && $time >= $mtime + $ttl || $time > $mtime + 2.5 * $ttlPageCache) {
                try {
                    //Remove cache
                    $cache->removeItem($item);
                } catch (Throwable $e) {
                    //Don't bother to remove tags if this didn't work, we'll try again next time
                    $deleteTag = \false;
                }
                //We need to also delete the static css/js file if that option is set
                if ($params->get('htaccess', '2') == '2') {
                    $files = [Paths::cachePath(\false) . '/css/' . $item . '.css', Paths::cachePath(\false) . '/js/' . $item . '.js'];
                    try {
                        foreach ($files as $file) {
                            if (file_exists($file)) {
                                File::delete($file);
                                //If for some reason the file still exists don't delete tags
                                if (file_exists($file)) {
                                    $deleteTag = \false;
                                }
                                break;
                            }
                        }
                    } catch (Throwable $e) {
                        //Don't bother to delete the tags if this didn't work
                        $deleteTag = \false;
                    }
                }
                //Remove tag if cache successfully deleted
                try {
                    if ($deleteTag) {
                        $taggableCache->removeItem($item);
                    }
                } catch (Throwable $e) {
                    //Just ignore, we'll get another chance if this didn't work this time.
                }
            }
            //If page cache just delete if expired
            //Or if this ran while caching enabled delete current page
            if (isset($tags[0]) && $tags[0] == 'pagecache' && ($time >= $mtime + $ttlPageCache || $tags[1] == SystemUri::toString())) {
                //Remove cache and tags
                $pageCache->deleteItemById($item);
            }
        }
        $taggableCache->getOptions()->setTtl($existingTtl);
        //Sometimes some tags don't get built for static files, so we'll just delete the ones that are long expired
        if (file_exists(Paths::cachePath(\false))) {
            $directory = new RecursiveDirectoryIterator(Paths::cachePath(\false), FilesystemIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory);
            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (\in_array($file->getFilename(), ['index.html', '.htaccess'])) {
                    continue;
                }
                if ($time > $file->getMTime() + 2.5 * $ttlPageCache) {
                    try {
                        File::delete($file->getRealPath());
                    } catch (Throwable $e) {
                        //Ignore for now
                    }
                }
            }
        }
        JCH_DEBUG ? Profiler::stop('ClearExpired', \true) : null;
    }
}
