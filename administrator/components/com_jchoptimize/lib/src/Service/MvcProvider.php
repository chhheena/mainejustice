<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Service;

use JchOptimize\Controller\Ajax;
use JchOptimize\Controller\ApplyAutoSetting;
use JchOptimize\Controller\ControlPanel;
use JchOptimize\Controller\ModeSwitcher as ModeSwitcherController;
use JchOptimize\Controller\OptimizeImage;
use JchOptimize\Controller\OptimizeImages;
use JchOptimize\Controller\PageCache;
use JchOptimize\Controller\ToggleSetting;
use JchOptimize\Controller\Utility;
use JchOptimize\ControllerResolver;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\PageCache\PageCache as CorePageCache;
use JchOptimize\Model\ApiParams;
use JchOptimize\Model\Cache;
use JchOptimize\Model\Configure;
use JchOptimize\Model\ModeSwitcher as ModeSwitcherModel;
use JchOptimize\Model\OrderPlugins;
use JchOptimize\Model\Updates;
use JchOptimize\Model\PageCache as PageCacheModel;
use JchOptimize\Platform\Paths;
use JchOptimize\View\ControlPanelHtml;
use JchOptimize\View\OptimizeImagesHtml;
use JchOptimize\View\PageCacheHtml;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use _JchOptimizeVendor\Joomla\Renderer\BladeRenderer;
use _JchOptimizeVendor\Joomla\Renderer\RendererInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
class MvcProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        //MVC Dependencies
        $container->share(Input::class, [$this, 'getInputService'], \true);
        $container->share(AbstractApplication::class, [$this, 'getAbstractApplicationService'], \true);
        $container->share(RendererInterface::class, [$this, 'getTemplateRendererService'], \true);
        $container->share(ControllerResolver::class, [$this, 'getControllerResolverService'], \true);
        //controllers
        $container->alias('ControlPanel', ControlPanel::class)->share(ControlPanel::class, [$this, 'getControllerControlPanelService'], \true);
        $container->alias('PageCache', PageCache::class)->share(PageCache::class, [$this, 'getControllerPageCacheService'], \true);
        $container->alias('OptimizeImages', OptimizeImages::class)->share(OptimizeImages::class, [$this, 'getControllerOptimizeImagesService'], \true);
        $container->alias('Ajax', Ajax::class)->share(Ajax::class, [$this, 'getControllerAjaxService'], \true);
        $container->alias('Utility', Utility::class)->share(Utility::class, [$this, 'getControllerUtilityService'], \true);
        $container->alias('ApplyAutoSetting', ApplyAutoSetting::class)->share(ApplyAutoSetting::class, [$this, 'getControllerApplyAutoSettingService'], \true);
        $container->alias('ToggleSetting', ToggleSetting::class)->share(ToggleSetting::class, [$this, 'getControllerToggleSettingService'], \true);
        $container->alias('ModeSwitcher', ModeSwitcherController::class)->share(ModeSwitcherController::class, [$this, 'getControllerModeSwitcherService'], \true);
        $container->alias('OptimizeImage', OptimizeImage::class)->share(OptimizeImage::class, [$this, 'getControllerOptimizeImageService'], \true);
        //Models
        $container->share(Cache::class, [$this, 'getModelCacheService'], \true);
        $container->share(ApiParams::class, [$this, 'getModelApiParamsService'], \true);
        $container->share(OrderPlugins::class, [$this, 'getModelOrderPluginsService'], \true);
        $container->share(Configure::class, [$this, 'getModelConfigureService'], \true);
        $container->share(ModeSwitcherModel::class, [$this, 'getModelModeSwitcherService'], \true);
        $container->share(Updates::class, [$this, 'getModelUpdatesService'], \true);
        $container->share(PageCacheModel::class, [$this, 'getModelPageCacheService'], \true);
        //View
        $container->share(ControlPanelHtml::class, [$this, 'getViewControlPanelHtmlService'], \true);
        $container->share(PageCacheHtml::class, [$this, 'getViewPageCacheHtmlService'], \true);
        $container->share(OptimizeImagesHtml::class, [$this, 'getViewOptimizeImagesHtmlService'], \true);
    }
    public function getInputService() : Input
    {
        return new Input($_REQUEST);
    }
    /**
     * @throws \Exception
     */
    public function getAbstractApplicationService() : ?AbstractApplication
    {
        try {
            return Factory::getApplication();
        } catch (\Exception $e) {
            return null;
        }
    }
    public function getTemplateRendererService(Container $container) : RendererInterface
    {
        return (new BladeRenderer($container->get(\_JchOptimizeVendor\Illuminate\View\Factory::class)))->addFolder(Paths::templatePath());
    }
    public function getControllerResolverService(Container $container) : ControllerResolver
    {
        return new ControllerResolver($container, $container->get(Input::class));
    }
    public function getControllerControlPanelService(Container $container) : ControlPanel
    {
        return (new ControlPanel($container->get(Cache::class), $container->get(Updates::class), $container->get(ControlPanelHtml::class), $container->get(Icons::class), $container->get(Input::class), $container->get(AbstractApplication::class)))->setContainer($container);
    }
    public function getControllerPageCacheService(Container $container) : PageCache
    {
        return new PageCache($container->get(PageCacheModel::class), $container->get(PageCacheHtml::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerOptimizeImagesService(Container $container) : OptimizeImages
    {
        return new OptimizeImages($container->get(ApiParams::class), $container->get(OptimizeImagesHtml::class), $container->get(Icons::class));
    }
    public function getControllerAjaxService(Container $container) : Ajax
    {
        return new Ajax($container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerUtilityService(Container $container) : Utility
    {
        return new Utility($container->get(OrderPlugins::class), $container->get(Cache::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerApplyAutoSettingService(Container $container) : ApplyAutoSetting
    {
        return new ApplyAutoSetting($container->get(Configure::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerToggleSettingService(Container $container) : ToggleSetting
    {
        return new ToggleSetting($container->get(Configure::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerModeSwitcherService(Container $container) : ModeSwitcherController
    {
        return new ModeSwitcherController($container->get(ModeSwitcherModel::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getControllerOptimizeImageService(Container $container) : OptimizeImage
    {
        return new OptimizeImage($container->get(Input::class), $container->get(AbstractApplication::class));
    }
    public function getModelCacheService(Container $container) : Cache
    {
        return (new Cache($container->get(StorageInterface::class), $container->get(CorePageCache::class), $container->get(TaggableInterface::class)))->setContainer($container);
    }
    public function getModelApiParamsService(Container $container) : ApiParams
    {
        $model = new ApiParams();
        $model->setState($container->get('params'));
        return $model;
    }
    public function getModelOrderPluginsService(Container $container) : OrderPlugins
    {
        $model = new OrderPlugins();
        $model->setDb($container->get('db'));
        return $model;
    }
    public function getModelConfigureService(Container $container) : Configure
    {
        $model = (new Configure($container->get('params'), $container->get(ModeSwitcherModel::class)))->setContainer($container);
        $model->setDb($container->get('db'));
        return $model;
    }
    public function getModelModeSwitcherService(Container $container) : ModeSwitcherModel
    {
        $model = new ModeSwitcherModel($container->get('params'), $container->get(Cache::class));
        $model->setDb($container->get('db'));
        return $model;
    }
    public function getModelUpdatesService(Container $container) : Updates
    {
        return new Updates($container->get('params'), $container->get('db'));
    }
    public function getModelPageCacheService(Container $container) : PageCacheModel
    {
        return new PageCacheModel($container->get(CorePageCache::class), $container);
    }
    public function getViewControlPanelHtmlService(Container $container) : ControlPanelHtml
    {
        return (new ControlPanelHtml($container->get(RendererInterface::class)))->setLayout('control_panel');
    }
    public function getViewPageCacheHtmlService(Container $container) : PageCacheHtml
    {
        return (new PageCacheHtml($container->get(RendererInterface::class)))->setLayout('page_cache');
    }
    public function getViewOptimizeImagesHtmlService(Container $container) : OptimizeImagesHtml
    {
        return (new OptimizeImagesHtml($container->get(RendererInterface::class)))->setLayout('optimize_images');
    }
}
