<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Platform;

\defined('_JEXEC') or die('Restricted access');
use JchOptimize\Core\Admin\AbstractHtml;
use JchOptimize\Core\Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Uri\Uri;
use _JchOptimizeVendor\Laminas\Diactoros\Response;
class Html extends AbstractHtml
{
    /**
     * Returns HTML of the front page
     *
     * @return string
     */
    public function getHomePageHtml() : string
    {
        try {
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('beforeGetHtml') : null;
            $response = $this->getHtml($this->getSiteUrl());
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('afterGetHtml') : null;
            return $response;
        } catch (Exception\ExceptionInterface $e) {
            $this->logger->error($this->getSiteUrl() . ': ' . $e->getMessage());
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('afterGetHtml') : null;
            throw new Exception\RuntimeException('Try reloading the front page to populate the Exclude options');
        }
    }
    /**
     * @param $sUrl
     *
     * @return string
     */
    protected function getHtml($sUrl) : string
    {
        $oUri = new Uri($sUrl);
        $sQuery = $oUri->getQuery();
        \parse_str($sQuery, $aQuery);
        $aNewQuery = \array_merge($aQuery, array('jchbackend' => '1'));
        $oUri->setQuery($aNewQuery);
        try {
            /** @var Response $response */
            $response = $this->http->get($oUri->toString());
        } catch (\Exception $e) {
            throw new Exception\RuntimeException('Exception fetching HTML: ' . $sUrl . ' - Message: ' . $response->getStatusCode() . ': ' . $response->getReasonPhrase());
        }
        if ($response->getStatusCode() != 200) {
            throw new Exception\RuntimeException('Failed fetching HTML: ' . $sUrl . ' - Message: ' . $response->getStatusCode() . ': ' . $response->getReasonPhrase());
        }
        //Get body and set pointer to beginning of stream
        $body = $response->getBody();
        $body->rewind();
        return $body->getContents();
    }
    /**
     *
     * @return string
     */
    protected function getSiteUrl() : string
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();
        if (\is_null($oDefaultMenu)) {
            $oCompParams = ComponentHelper::getParams('com_languages');
            $sLanguage = $oCompParams->get('site', \_JchOptimizeVendor\JFactory::getApplication('site')->get('language', 'en-GB'));
            $oDefaultMenu = $oSiteMenu->getItems(array('home', 'language'), array('1', $sLanguage), \true);
        }
        return $this->getMenuUrl($oDefaultMenu);
    }
    protected function getSiteMenu()
    {
        return Factory::getApplication('site')->getMenu('site');
    }
    protected function getMenuUrl(MenuItem $oMenuItem) : string
    {
        $oSiteRouter = \JApplicationSite::getRouter();
        $bSefModeTest = \version_compare(JVERSION, '4.0', '<') && $oSiteRouter->getMode() == JROUTER_MODE_SEF;
        $sMenuUrl = $bSefModeTest ? 'index.php?Itemid=' . $oMenuItem->id : $oMenuItem->link . '&Itemid=' . $oMenuItem->id;
        return \JRoute::link('site', $sMenuUrl, \true, 0, \true);
    }
    public function getMainMenuItemsHtmls($iLimit = 5, $bIncludeUrls = \false) : array
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();
        $aAttributes = array('menutype', 'type', 'level', 'access', 'home');
        $aValues = array($oDefaultMenu->menutype, 'component', '1', '1', '0');
        //Only need 5 menu items including the home menu
        $aMenus = \array_slice(\array_merge(array($oDefaultMenu), $oSiteMenu->getItems($aAttributes, $aValues)), 0, $iLimit);
        $aHtmls = array();
        //Gonna limit the time spent on this
        $iTimerStart = \microtime(\true);
        /** @var MenuItem $oMenuItem */
        foreach ($aMenus as $oMenuItem) {
            $oMenuItem->link = $this->getMenuUrl($oMenuItem);
            try {
                if ($bIncludeUrls) {
                    $aHtmls[] = array('url' => $oMenuItem->link, 'html' => $this->getHtml($oMenuItem->link));
                } else {
                    $aHtmls[] = $this->getHtml($oMenuItem->link);
                }
            } catch (Exception\ExceptionInterface $e) {
                $this->logger->error($e->getMessage());
            }
            if (\microtime(\true) > $iTimerStart + 10.0) {
                break;
            }
        }
        return $aHtmls;
    }
}
