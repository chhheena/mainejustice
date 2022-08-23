<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class AboutHelper
{
    /** @var \Joomla\CMS\Layout\FileLayout */
    private $layout;
    /** @var SimpleXMLElement */
    private $remoteManifest;
    private $localManifest;
    private $remoteManifestUrl;
    private $localManifestPath;
    private $extension;

    public function __construct($extension)
    {
        $this->extension = $extension;
        $this->remoteManifest = @simplexml_load_string($this->fetchUrl($this->getRemoteManifestUrl()));
        $this->localManifest = \Joomla\CMS\Installer\Installer::parseXMLInstallFile($this->getLocalManifestPath());
        $this->layout = new \Joomla\CMS\Layout\FileLayout('about_layout', __DIR__);
    }

    /**
     * @return mixed
     */
    public function getRemoteManifestUrl()
    {
        if (null === $this->remoteManifestUrl) {
            $this->remoteManifestUrl = 'http://thephpfactory.com/versions/com_' . $this->extension . 'factory.xml';
        }

        return $this->remoteManifestUrl;
    }

    /**
     * @param mixed $remoteManifestUrl
     */
    public function setRemoteManifestUrl($remoteManifestUrl)
    {
        $this->remoteManifestUrl = $remoteManifestUrl;
    }

    /**
     * @return mixed
     */
    public function getLocalManifestPath()
    {
        if (null === $this->localManifestPath) {
            $this->localManifestPath = JPATH_ADMINISTRATOR . '/components/com_' . $this->extension . 'factory/' . $this->extension . 'factory.xml';
        }

        return $this->localManifestPath;
    }

    /**
     * @param mixed $localManifestPath
     */
    public function setLocalManifestPath($localManifestPath)
    {
        $this->localManifestPath = $localManifestPath;
    }

    public function render()
    {
        $this->layout->setData([
            'data' => $this->getData(),
        ]);

        return $this->layout->render();
    }

    private function getData()
    {
        return new Joomla\Registry\Registry([
            'version'           => [
                'current' => $this->currentVersion(),
                'latest'  => $this->latestVersion(),
            ],
            'isUpdateAvailable' => $this->isUpdateAvailable(),
            'versionHistory'    => $this->versionHistory(),
            'supportAndUpdates' => $this->supportAndUpdates(),
            'otherProducts'     => $this->otherProducts(),
            'aboutCompany'      => $this->aboutCompany(),
            'translation'       => [
                'release_notes'           => $this->translate('release_notes', 'Latest Release Notes'),
                'current_version'         => $this->translate('current_version', 'Your installed version'),
                'latest_version'          => $this->translate('latest_version', 'Latest version available'),
                'update_is_available'     => $this->translate('update_is_available', 'New version available'),
                'update_is_not_available' => $this->translate('update_is_not_available', 'No new version available'),
                'support_and_updates'     => $this->translate('support_and_updates', 'Support and Updates'),
                'other_products'          => $this->translate('other_products', 'Other Products'),
                'about_company'           => $this->translate('about_company', 'About thePHPFactory'),
            ],
        ]);
    }

    private function latestVersion()
    {
        return (string)$this->remoteManifest->latestversion;
    }

    private function currentVersion()
    {
        return (string)$this->localManifest['version'];
    }

    private function isUpdateAvailable()
    {
        return version_compare($this->latestVersion(), $this->currentVersion(), '>');
    }

    private function supportAndUpdates()
    {
        return (string)$this->remoteManifest->downloadlink;
    }

    private function otherProducts()
    {
        return (string)$this->remoteManifest->otherproducts;
    }

    private function aboutCompany()
    {
        return (string)$this->remoteManifest->aboutfactory;
    }

    private function versionHistory()
    {
        return (string)$this->remoteManifest->versionhistory;
    }

    private function fetchUrl($url)
    {
        $cache = JPATH_CACHE . '/' . md5($url);

        if (!file_exists($cache) || time() - filemtime($cache) > 60 * 60) {
            $handle = curl_init();

            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_MAXREDIRS, 5);
            curl_setopt($handle, CURLOPT_AUTOREFERER, 1);
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handle, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($handle);

            curl_close($handle);

            file_put_contents($cache, $response);
        }

        return file_get_contents($cache);
    }

    private function translate($string, $default)
    {
        $language = \Joomla\CMS\Factory::getLanguage();

        if ($language->hasKey($key = 'COM_' . $this->extension . 'FACTORY_ABOUT_HEADING_' . $string)) {
            return \Joomla\CMS\Language\Text::_($key);
        }

        return $default;
    }
}
