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

// No direct access
\defined('_JCH_EXEC') or die('Restricted access');
use CodeAlfa\Minify\Html;
use JchOptimize\Core\Exception;
use JchOptimize\Core\FeatureHelpers\ReduceDom;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\LinkBuilder;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Utility;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
/**
 * Main plugin file
 *
 */
class Optimize implements LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;
    /**
     * @var Registry
     */
    private $params;
    /**
     * @var HtmlProcessor
     */
    private $htmlProcessor;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var LinkBuilder
     */
    private $linkBuilder;
    /**
     * @var string
     */
    private $html;
    /**
     * @var false|int|string
     */
    private $jit = 1;
    /**
     * @var Http2Preload
     * @since version
     */
    private $http2Preload;
    /**
     * Constructor
     *
     * @throws Exception\RuntimeException
     */
    public function __construct(Registry $params, HtmlProcessor $htmlProcessor, CacheManager $cacheManager, LinkBuilder $linkBuilder, \JchOptimize\Core\Http2Preload $http2Preload)
    {
        \ini_set('pcre.backtrack_limit', 1000000);
        \ini_set('pcre.recursion_limit', 1000000);
        if (\version_compare(\PHP_VERSION, '7.0.0', '>=')) {
            $this->jit = \ini_get('pcre.jit');
            \ini_set('pcre.jit', "0");
        }
        if (\version_compare(\PHP_VERSION, '7.3', '<')) {
            throw new Exception\RuntimeException('PHP Version less than 7.3, Exiting plugin...');
        }
        $pcre_version = \preg_replace('#(^\\d++\\.\\d++).++$#', '$1', \PCRE_VERSION);
        if (\version_compare($pcre_version, '7.2', '<')) {
            throw new Exception\RuntimeException('PCRE Version less than 7.2. Exiting plugin...');
        }
        $this->params = $params;
        $this->htmlProcessor = $htmlProcessor;
        $this->cacheManager = $cacheManager;
        $this->linkBuilder = $linkBuilder;
        $this->http2Preload = $http2Preload;
    }
    public function setHtml($html)
    {
        $this->html = $html;
    }
    /**
     * Optimize website by aggregating css and js
     *
     * @return string
     */
    public function process() : string
    {
        JCH_DEBUG ? Profiler::start('Process', \true) : null;
        try {
            $this->htmlProcessor->setHtml($this->html);
            $this->linkBuilder->preProcessHtml();
            $this->htmlProcessor->processCombineJsCss();
            $this->htmlProcessor->processImageAttributes();
            $this->cacheManager->handleCombineJsCss();
            $this->cacheManager->handleImgAttributes();
            $this->htmlProcessor->processCdn();
            $this->htmlProcessor->processLazyLoad();
            $this->linkBuilder->postProcessHtml();
            $optimizedHtml = $this->reduceDom($this->minifyHtml($this->htmlProcessor->getHtml()));
            $this->sendHeaders();
            JCH_DEBUG ? Profiler::stop('Process', \true) : null;
            JCH_DEBUG ? Profiler::attachProfiler($optimizedHtml, $this->params->get('ampPage', '0')) : null;
        } catch (Exception\ExceptionInterface $e) {
            $this->logger->error((string) $e);
            $optimizedHtml = $this->html;
        }
        if (\version_compare(\PHP_VERSION, '7.0.0', '>=')) {
            \ini_set('pcre.jit', $this->jit);
        }
        return $optimizedHtml;
    }
    protected function reduceDom($html)
    {
        if (JCH_PRO) {
            /** @see ReduceDom::process() */
            $html = $this->container->get(ReduceDom::class)->process($html);
        }
        return $html;
    }
    protected function sendHeaders()
    {
        $headers = array();
        if ($this->http2Preload->isEnabled()) {
            $preloads = $this->http2Preload->getPreloads();
            $preloadHeaders = [];
            foreach ($preloads as $preload) {
                $preloadHeader = "<{$preload['href']}>; rel=preload; as={$preload['as']}";
                if ($preload['crossorigin']) {
                    $preloadHeader .= '; crossorigin';
                }
                if (!empty($preload['type'])) {
                    $preloadHeader .= '; type="' . $preload['type'] . '"';
                }
                $preloadHeaders[] = $preloadHeader;
            }
            if (!empty($preloadHeaders)) {
                $headers['Link'] = \implode(',', $preloadHeaders);
            }
        }
        if (!empty($headers)) {
            Utility::sendHeaders($headers);
        }
    }
    /**
     * If parameter is set will minify HTML before sending to browser;
     * Inline CSS and JS will also be minified if respective parameters are set
     *
     * @param   string  $html
     *
     * @return string                       Optimized HTML
     */
    public function minifyHtml(string $html) : string
    {
        JCH_DEBUG ? Profiler::start('MinifyHtml') : null;
        if ($this->params->get('combine_files_enable', '1') && $this->params->get('html_minify', 0)) {
            $aOptions = array();
            if ($this->params->get('css_minify', 0)) {
                $aOptions['cssMinifier'] = array('CodeAlfa\\Minify\\Css', 'optimize');
            }
            if ($this->params->get('js_minify', 0)) {
                $aOptions['jsMinifier'] = array('CodeAlfa\\Minify\\Js', 'optimize');
            }
            $aOptions['jsonMinifier'] = array('CodeAlfa\\Minify\\Json', 'optimize');
            $aOptions['minifyLevel'] = $this->params->get('html_minify_level', 0);
            $aOptions['isXhtml'] = \JchOptimize\Core\Helper::isXhtml($html);
            $aOptions['isHtml5'] = \JchOptimize\Core\Helper::isHtml5($html);
            $htmlMin = Html::optimize($html, $aOptions);
            if ($htmlMin == '') {
                $this->logger->error('Error while minifying HTML');
                $htmlMin = $html;
            }
            $html = $htmlMin;
            JCH_DEBUG ? Profiler::stop('MinifyHtml', \true) : null;
        }
        return $html;
    }
}
