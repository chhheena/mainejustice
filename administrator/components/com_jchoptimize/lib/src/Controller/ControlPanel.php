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
namespace JchOptimize\Controller;

\defined('_JEXEC') or die('Restricted Access');
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Model\Cache;
use JchOptimize\Model\Updates;
use JchOptimize\View\ControlPanelHtml;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Input\Input;
class ControlPanel extends AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var Cache
     */
    private $cacheModel;
    /**
     * @var ControlPanelHtml
     */
    private $view;
    /**
     * @var Updates
     */
    private $updatesModel;
    /**
     * @var Icons
     */
    private $icons;
    /**
     * Constructor
     *
     * @param   Cache                          $cacheModel
     * @param   Updates                        $updatesModel
     * @param   ControlPanelHtml               $view
     * @param   Icons                          $icons
     * @param   Input|null                     $input
     * @param   AdministratorApplication|null  $app
     */
    public function __construct(Cache $cacheModel, Updates $updatesModel, ControlPanelHtml $view, Icons $icons, Input $input = null, AdministratorApplication $app = null)
    {
        $this->cacheModel = $cacheModel;
        $this->updatesModel = $updatesModel;
        $this->view = $view;
        $this->icons = $icons;
        parent::__construct($input, $app);
    }
    public function execute()
    {
        $this->updatesModel->refreshUpdateSite();
        if (JCH_PRO) {
            /** @see CaptureCache::updateHtaccess() */
            $this->container->get(CaptureCache::class)->updateHtaccess();
        }
        list($size, $numFiles) = $this->cacheModel->getCacheSize();
        $this->view->setData(['size' => $size, 'numFiles' => $numFiles, 'view' => 'ControlPanel', 'icons' => $this->icons]);
        $this->view->loadResources();
        $this->view->loadToolBar();
        if (!PluginHelper::isEnabled('system', 'jchoptimize')) {
            $editUrl = Route::_('index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[search]=JCH Optimize', \false);
            $this->getApplication()->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_PLUGIN_NOT_ENABLED', $editUrl), 'warning');
        }
        echo $this->view->render();
    }
}
