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
namespace JchOptimize\Core\Service;

use JchOptimize\Core\Cdn;
use JchOptimize\Core\Css\Callbacks\ExtractCriticalCss;
use JchOptimize\Core\FeatureHelpers\CdnDomains;
use JchOptimize\Core\FeatureHelpers\DynamicJs;
use JchOptimize\Core\FeatureHelpers\DynamicSelectors;
use JchOptimize\Core\FeatureHelpers\Fonts;
use JchOptimize\Core\FeatureHelpers\GoogleFonts;
use JchOptimize\Core\FeatureHelpers\Http2Excludes;
use JchOptimize\Core\FeatureHelpers\LazyLoadExtended;
use JchOptimize\Core\FeatureHelpers\ReduceDom;
use JchOptimize\Core\FeatureHelpers\Webp;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Html\LinkBuilder;
use JchOptimize\Core\Http2Preload;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
class FeatureHelpersProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share(CdnDomains::class, function (Container $container) : CdnDomains {
            return (new CdnDomains($container->get('params'), $container->get(Cdn::class)))->setContainer($container);
        }, \true);
        $container->share(DynamicJs::class, function (Container $container) : DynamicJs {
            return (new DynamicJs($container->get('params'), $container->get(CacheManager::class), $container->get(FilesManager::class), $container->get(LinkBuilder::class)))->setContainer($container);
        }, \true);
        $container->share(DynamicSelectors::class, function (Container $container) : DynamicSelectors {
            return (new DynamicSelectors($container->get('params'), $container->get(ExtractCriticalCss::class)))->setContainer($container);
        });
        $container->share(Fonts::class, function (Container $container) : Fonts {
            return (new Fonts($container->get('params')))->setContainer($container);
        }, \true);
        $container->share(GoogleFonts::class, function (Container $container) : GoogleFonts {
            return (new GoogleFonts($container->get('params')))->setContainer($container);
        }, \true);
        $container->share(Http2Excludes::class, function (Container $container) : Http2Excludes {
            return (new Http2Excludes($container->get('params'), $container->get(Http2Preload::class)))->setContainer($container);
        }, \true);
        $container->share(LazyLoadExtended::class, function (Container $container) : LazyLoadExtended {
            return (new LazyLoadExtended($container->get('params')))->setContainer($container);
        }, \true);
        $container->share(ReduceDom::class, function (Container $container) : ReduceDom {
            return (new ReduceDom($container->get('params')))->setContainer($container);
        }, \true);
        $container->share(Webp::class, function (Container $container) : Webp {
            return (new Webp($container->get('params')))->setContainer($container);
        }, \true);
    }
}
