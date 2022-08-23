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

use JchOptimize\Core\Css\Callbacks\ExtractCriticalCss;
use JchOptimize\Core\Helper;
use Joomla\Registry\Registry;
\defined('_JCH_EXEC') or die('Restricted access');
class DynamicSelectors extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @var ExtractCriticalCss
     */
    private $extractCriticalCss;
    public function __construct(Registry $params, ExtractCriticalCss $extractCriticalCss)
    {
        parent::__construct($params);
        $this->extractCriticalCss = $extractCriticalCss;
    }
    public function getDynamicSelectors($aMatches)
    {
        //Add all CSS containing any specified dynamic CSS to the critical CSS
        $dynamicSelectors = Helper::getArray($this->params->get('pro_dynamic_selectors', []));
        $dynamicSelectors = \array_unique(\array_merge($dynamicSelectors, ['offcanvas', 'off-canvas', 'mobilemenu', 'mobile-menu']));
        if (!empty($dynamicSelectors)) {
            foreach ($dynamicSelectors as $dynamicSelector) {
                if (\strpos($aMatches[2], $dynamicSelector) !== \false) {
                    $this->extractCriticalCss->appendToCriticalCss($aMatches[0]);
                    $this->extractCriticalCss->_debug('', '', 'afterAddDynamicCss');
                    return \true;
                }
            }
        }
        $this->extractCriticalCss->_debug('', '', 'afterSearchDynamicCss');
        return \false;
    }
}
