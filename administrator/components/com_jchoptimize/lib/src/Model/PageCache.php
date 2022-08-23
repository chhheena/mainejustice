<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Model;

use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Core\PageCache\PageCache as CorePageCache;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use Joomla\Registry\Registry;
use function is_null;
use function version_compare;
class PageCache implements StatefulModelInterface, ContainerAwareInterface
{
    use StatefulModelTrait;
    use ContainerAwareTrait;
    /**
     * @var CorePageCache
     * @since version
     */
    private $pageCache;
    /**
     * Constructor
     *
     * @param   CorePageCache  $pageCache
     * @param   Container      $container
     */
    public function __construct(CorePageCache $pageCache, Container $container)
    {
        $this->pageCache = $pageCache;
        $this->setContainer($container);
        if (JCH_PRO) {
            $this->container->get(CaptureCache::class)->updateHtaccess();
        }
        try {
            $registry = $this->populateRegistryFromRequest(['filter', 'list']);
        } catch (\Exception $e) {
            $registry = new Registry();
        }
        $this->setState($registry);
    }
    /**
     * @param $keys
     *
     * @return Registry
     * @throws \Exception
     */
    private function populateRegistryFromRequest($keys) : Registry
    {
        $data = new Registry();
        $app = Factory::getApplication();
        $session = $app->getSession();
        $input = version_compare(JVERSION, '4', 'lt') ? $app->input : $app->getInput();
        foreach ($keys as $key) {
            //Check for value from input first
            $requestKey = $input->getString($key);
            if (is_null($requestKey)) {
                //Not found, let's see if it's saved in session
                $requestKey = $session->get($key);
            }
            //If we've got one by now let's set it in registry
            if (!is_null($requestKey)) {
                foreach ($requestKey as $requestName => $requestValue) {
                    if (!empty($requestValue)) {
                        $data->set($key . '_' . $requestName, $requestValue);
                    }
                }
                //Set the new value in session
                $session->set($key, $requestKey);
            }
        }
        return $data;
    }
    public function getItems() : array
    {
        $filters = ['time-1', 'time-2', 'search', 'device', 'adapter', 'http-request'];
        foreach ($filters as $filter) {
            $filterState = $this->getState()->get("filter_{$filter}");
            if (!empty($filterState)) {
                $this->pageCache->setFilter("filter_{$filter}", $filterState);
            }
        }
        //ordering
        $fullOrderingList = $this->getState()->get('list_fullordering');
        if (!empty($fullOrderingList)) {
            $this->pageCache->setList('list_fullordering', $fullOrderingList);
        }
        return $this->pageCache->getItems();
    }
    public function delete($ids) : bool
    {
        return $this->pageCache->deleteItemsByIds($ids);
    }
    public function deleteAll() : bool
    {
        return $this->pageCache->deleteAllItems();
    }
    public function getAdaptorName() : string
    {
        return $this->pageCache->getAdapterName();
    }
    public function isCaptureCacheEnabled() : bool
    {
        return $this->pageCache->isCaptureCacheEnabled();
    }
}
