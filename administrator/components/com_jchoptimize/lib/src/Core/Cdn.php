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

use JchOptimize\Core\FeatureHelpers\CdnDomains;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
\defined('_JCH_EXEC') or die('Restricted access');
class Cdn implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    private $params;
    public $scheme = '';
    protected $domains = null;
    protected $filePaths = [];
    protected $cdnFileTypes = null;
    private $enabled = \false;
    public function __construct(Registry $params)
    {
        $this->params = $params;
        $this->enabled = (bool) $this->params->get('cookielessdomain_enable', '0');
        switch ($params->get('cdn_scheme', '0')) {
            case '1':
                $this->scheme = 'http:';
                break;
            case '2':
                $this->scheme = 'https:';
                break;
            case '0':
            default:
                $this->scheme = '';
                break;
        }
    }
    private function initialize()
    {
        $defaultFiles = self::getStaticFiles();
        $domainArray = [];
        $this->cdnFileTypes = [];
        if ($this->enabled) {
            if (\trim($this->params->get('cookielessdomain', '')) != '') {
                $domain1 = $this->params->get('cookielessdomain');
                $sStaticFiles1 = \implode('|', \array_merge($this->params->get('staticfiles', $defaultFiles), $this->params->get('pro_customcdnextensions', [])));
                $domainArray[$this->scheme . $this->prepareDomain($domain1)] = $sStaticFiles1;
            }
            if (JCH_PRO) {
                /** @see CdnDomains::addCdnDomains() */
                $this->container->get(CdnDomains::class)->addCdnDomains($domainArray);
            }
        }
        $this->domains = $domainArray;
        if (!empty($this->domains)) {
            foreach ($this->domains as $cdn_file_types) {
                $this->cdnFileTypes = \array_merge($this->cdnFileTypes, \explode('|', $cdn_file_types));
            }
            $this->cdnFileTypes = \array_unique($this->cdnFileTypes);
        }
    }
    /**
     * Returns array of default static files to load from CDN
     *
     *
     * @return array $aStaticFiles Array of file type extensions
     */
    public static function getStaticFiles() : array
    {
        return ['css', 'js', 'jpe?g', 'gif', 'png', 'ico', 'bmp', 'pdf', 'webp', 'svg'];
    }
    /**
     *
     * @param   string  $domain
     *
     * @return string
     */
    public function prepareDomain(string $domain) : string
    {
        return '//' . \preg_replace('#^(?:https?:)?//|/$#i', '', \trim($domain));
    }
    /**
     * Returns an array of file types that will be loaded by CDN
     *
     * @return array $aCdnFileTypes Array of file type extensions
     */
    public function getCdnFileTypes() : array
    {
        if (\is_null($this->cdnFileTypes)) {
            $this->initialize();
        }
        return $this->cdnFileTypes;
    }
    /**
     * @param   string  $path
     * @param   null    $origPath
     *
     * @return array|bool|mixed
     */
    public function loadCdnResource(string $path, $origPath = null)
    {
        $domains = $this->getCdnDomains();
        if (empty($origPath)) {
            $origPath = $path;
        }
        //if disabled or no domain is configured abort
        if (!$this->enabled || empty($domains)) {
            return $origPath;
        }
        //If we haven't matched a cdn domain to this file yet then find one.
        if (!isset($this->filePaths[$path])) {
            $this->filePaths[$path] = $this->selectDomain($this->domains, $path);
        }
        if ($this->filePaths[$path] === \false) {
            return $origPath;
        }
        return $this->filePaths[$path];
    }
    /**
     *
     * @staticvar int $iIndex
     *
     * @param   array   $domainArray
     * @param   string  $path
     *
     * @return bool|string
     */
    private function selectDomain(array &$domainArray, string $path)
    {
        //If no domain is matched to a configured file type then we'll just return the file
        $cdnUrl = \false;
        for ($i = 0; \count($domainArray) > $i; $i++) {
            $staticFiles = \current($domainArray);
            $domain = \key($domainArray);
            \next($domainArray);
            if (\current($domainArray) === \false) {
                \reset($domainArray);
            }
            if (\preg_match('#\\.(?>' . $staticFiles . ')#i', $path)) {
                //Prepend the cdn domain to the file path if a match is found.
                $cdnUrl = $domain . $path;
                break;
            }
        }
        return $cdnUrl;
    }
    public function getCdnDomains() : array
    {
        if (\is_null($this->domains)) {
            $this->initialize();
        }
        return $this->domains;
    }
}
