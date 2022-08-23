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
namespace JchOptimize\Core\FeatureHelpers;

use JchOptimize\Core\Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Html\LinkBuilder;
use Joomla\Registry\Registry;
use function array_merge;
\defined('_JCH_EXEC') or die('Restricted access');
class DynamicJs extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @var array $criticalJs Array of javascript files/scripts excluded from the Remove Unused Js feature
     */
    public static $criticalJs = [];
    /**
     * @var array $criticalJsDeferred Javascript files excluded from Remove Unused but were deferred
     */
    public static $criticalJsDeferred = [];
    /**
     * @var array $aJsDynamicUrls Array of Js Urls to load dynamically for Remove Unused Js feature
     */
    public static $aJsDynamicUrls = [];
    /**
     * @var array $jsDynamicIds Array of id of combined js files that need to be prepended to the js file that will
     *                                be loaded dynamically
     */
    public static $jsDynamicIds = [];
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var FilesManager
     */
    private $filesManager;
    /**
     * @var LinkBuilder
     */
    private $linkBuilder;
    /**
     * @var bool
     */
    private $enable;
    public function __construct(Registry $params, CacheManager $cacheManager, FilesManager $filesManager, LinkBuilder $linkBuilder)
    {
        parent::__construct($params);
        $this->cacheManager = $cacheManager;
        $this->filesManager = $filesManager;
        $this->linkBuilder = $linkBuilder;
        $this->enable = (bool) $this->params->get('pro_remove_unused_js_enable', '0');
    }
    /**
     * @throws Exception\ExcludeException
     */
    public function handleCriticalUrls($url)
    {
        if ($this->enable && Helper::findExcludes(@$this->filesManager->aExcludes['critical_js']['js'], $url)) {
            //if file was deferred we place them differently for now
            if ($this->filesManager->isFileDeferred($this->filesManager->aMatch[0])) {
                self::$criticalJsDeferred[] = ['url' => $url];
            } else {
                self::$criticalJs[] = ['url' => $url];
            }
            $this->filesManager->excludeJsIEO();
        }
    }
    /**
     * @throws Exception\ExcludeException
     */
    public function handleCriticalScripts($content)
    {
        if ($this->enable && Helper::findExcludes(@$this->filesManager->aExcludes['critical_js']['script'], $content)) {
            self::$criticalJs[] = ['content' => $content];
            $this->filesManager->excludeJsIEO();
        }
    }
    /**
     */
    public function appendCriticalJsToHtml()
    {
        if ($this->enable) {
            $criticalJsToCombine = array_merge(self::$criticalJs, self::$criticalJsDeferred);
            if (!empty($criticalJsToCombine)) {
                $this->cacheManager->getCombinedFiles($criticalJsToCombine, $criticalJsId, 'js');
                $this->linkBuilder->appendCriticalJsToHtml($this->linkBuilder->buildUrl($criticalJsId, 'js'));
            }
        }
    }
    public function prepareJsDynamicUrls($defers)
    {
        unset($defers['matches']);
        if (empty(self::$jsDynamicIds) && empty($defers)) {
            return;
        }
        $this->cacheManager->getAppendedFiles(self::$jsDynamicIds, $defers, $dynamicJsId);
        self::$aJsDynamicUrls[] = ['url' => $this->linkBuilder->buildUrl($dynamicJsId, 'js'), 'module' => \false, 'nomodule' => \false];
    }
}
