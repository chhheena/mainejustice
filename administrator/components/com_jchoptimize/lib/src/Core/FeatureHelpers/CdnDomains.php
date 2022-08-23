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

use JchOptimize\Core\Cdn;
use Joomla\Registry\Registry;
\defined('_JCH_EXEC') or die('Restricted access');
class CdnDomains extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @var Cdn
     */
    private $cdn;
    public function __construct(Registry $params, Cdn $cdn)
    {
        parent::__construct($params);
        $this->cdn = $cdn;
    }
    public function addCdnDomains(array &$domains)
    {
        if (\trim($this->params->get('pro_cookielessdomain_2', '')) != '') {
            $domain2 = $this->params->get('pro_cookielessdomain_2');
            $sStaticFiles2 = \implode('|', $this->params->get('pro_staticfiles_2', Cdn::getStaticFiles()));
            $domains[$this->cdn->scheme . $this->cdn->prepareDomain($domain2)] = $sStaticFiles2;
        }
        if (\trim($this->params->get('pro_cookielessdomain_3', '')) != '') {
            $domain3 = $this->params->get('pro_cookielessdomain_3');
            $sStaticFiles3 = \implode('|', $this->params->get('pro_staticfiles_3', Cdn::getStaticFiles()));
            $domains[$this->cdn->scheme . $this->cdn->prepareDomain($domain3)] = $sStaticFiles3;
        }
    }
    public function preconnect()
    {
        if ($this->params->get('cookielessdomain_enable', '0') && $this->params->get('pro_cdn_preconnect', '1')) {
            $domains = $this->cdn->getCdnDomains();
            $cdnPreConnect = '';
            foreach ($domains as $domain => $staticFiles) {
                $cdnPreConnect .= "\t" . '<link rel="preconnect" href="' . $domain . '" crossorigin />' . "\n";
            }
            return $cdnPreConnect;
        }
    }
}
