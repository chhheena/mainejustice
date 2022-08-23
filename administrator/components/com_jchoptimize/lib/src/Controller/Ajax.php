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
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use Joomla\CMS\Application\AdministratorApplication;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\Input\Input;
class Ajax extends AbstractController
{
    /**
     * @var string[]
     */
    private $taskMap;
    /**
     * @param   Input                     $input
     * @param   AdministratorApplication  $app
     */
    public function __construct(Input $input, AdministratorApplication $app)
    {
        parent::__construct($input, $app);
        $this->taskMap = ['filetree' => 'doFileTree', 'multiselect' => 'doMultiSelect', 'optimizeimage' => 'doOptimizeImage', 'smartcombine' => 'doSmartCombine', 'garbagecron' => 'doGarbageCron'];
    }
    public function execute()
    {
        $this->{$this->taskMap[$this->getInput()->get('task')]}();
        $this->getApplication()->close();
    }
    private function doFileTree()
    {
        echo AdminAjax::getInstance('FileTree')->run();
    }
    private function doMultiSelect()
    {
        echo AdminAjax::getInstance('MultiSelect')->run();
    }
    private function doOptimizeImage()
    {
        echo AdminAjax::getInstance('OptimizeImage')->run();
    }
    private function doSmartCombine()
    {
        echo AdminAjax::getInstance('SmartCombine')->run();
    }
    private function doGarbageCron()
    {
        echo AdminAjax::getInstance('GarbageCron')->run();
    }
}
