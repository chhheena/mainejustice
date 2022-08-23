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
namespace JchOptimize\Core;

use _JchOptimizeVendor\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
trait StorageTaggingTrait
{
    protected function tagStorage($id)
    {
        try {
            //If we're not using the same storage to tag then we need to cache an item to tag with the same id
            if ($this->callbackCache->getStorage() !== $this->taggableCache) {
                //If item not already set for tagging, set it
                $this->taggableCache->addItem($id, 'tag');
            }
            //Always attempt to store tags, item could be set on another page
            $this->setStorageTags($this->taggableCache, $id);
        } catch (ExceptionInterface $e) {
            //Don't sweat it.
            $this->logger->info('No tag set for item, ' . $id . ' because: ' . $e->getMessage());
        }
    }
    private function setStorageTags(TaggableInterface $cache, string $id)
    {
        $tags = $cache->getTags($id);
        $currentUrl = \JchOptimize\Core\SystemUri::toString();
        //If current url not yet tagged, tag it for this item. If it was only tagged once tag it again, so we
        //know this item was requested at least twice so shouldn't be removed until expired.
        if (\is_array($tags) && (!\in_array($currentUrl, $tags) || \count($tags) == 1)) {
            $cache->setTags($id, \array_merge($tags, [$currentUrl]));
        } elseif (empty($tags)) {
            $cache->setTags($id, [$currentUrl]);
        }
    }
}
