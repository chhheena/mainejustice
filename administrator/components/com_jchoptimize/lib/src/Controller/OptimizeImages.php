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
use JchOptimize\Model\ApiParams;
use JchOptimize\View\OptimizeImagesHtml;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
class OptimizeImages extends AbstractController
{
    /**
     * @var OptimizeImagesHtml
     */
    private $view;
    /**
     * @var ApiParams
     */
    private $model;
    /**
     * @var Icons
     */
    private $icons;
    /**
     * Constructor
     *
     * @param   ApiParams           $model
     * @param   OptimizeImagesHtml  $view
     * @param   Icons               $icons
     */
    public function __construct(ApiParams $model, OptimizeImagesHtml $view, Icons $icons)
    {
        $this->model = $model;
        $this->view = $view;
        $this->icons = $icons;
        parent::__construct();
    }
    public function execute()
    {
        $this->view->setData(['view' => 'OptimizeImages', 'apiParams' => \json_encode($this->model->getCompParams()), 'icons' => $this->icons]);
        $this->view->loadResources();
        $this->view->loadToolBar();
        echo $this->view->render();
    }
}
