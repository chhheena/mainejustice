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
use JchOptimize\Model\Configure;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Router\Route as JRoute;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use function json_encode;
class ToggleSetting extends AbstractController
{
    /**
     * @var Configure
     */
    private $model;
    public function __construct(Configure $model, ?Input $input = null, ?AbstractApplication $application = null)
    {
        $this->model = $model;
        parent::__construct($input, $application);
    }
    public function execute()
    {
        $setting = $this->getInput()->get('setting');
        $this->model->toggleSetting($setting);
        $currentSettingValue = $this->model->getState()->get($setting);
        if ($setting == 'integrated_page_cache_enable') {
            $currentSettingValue = !\JchOptimize\Platform\Utility::isPageCacheEnabled($this->model->getState());
        }
        $class = $currentSettingValue ? 'enabled' : 'disabled';
        $class2 = '';
        $auto = \false;
        if ($setting == 'pro_remove_unused_css') {
            $class2 = $this->model->getState()->get('optimizeCssDelivery_enable') ? 'enabled' : 'disabled';
        }
        if ($setting == 'optimizeCssDelivery_enable') {
            $class2 = $this->model->getState()->get('pro_remove_unused_css') ? 'enabled' : 'disabled';
        }
        if ($setting == 'combine_files_enable' && $currentSettingValue) {
            $auto = $this->getEnabledAutoSetting();
        }
        $body = json_encode(['class' => $class, 'class2' => $class2, 'auto' => $auto]);
        $this->getApplication()->clearHeaders();
        $this->getApplication()->setHeader('Content-Type', 'application/json');
        $this->getApplication()->setHeader('Content-Length', \strlen($body));
        $this->getApplication()->setBody($body);
        $this->getApplication()->allowCache(\false);
        echo $this->getApplication()->toString();
        $this->getApplication()->close();
    }
    private function getEnabledAutoSetting()
    {
        $autoSettingsMap = Icons::autoSettingsArrayMap();
        $autoSettingsInitialized = \array_map(function ($a) {
            return '0';
        }, $autoSettingsMap);
        $currentAutoSettings = \array_intersect_key($this->model->getState()->toArray(), $autoSettingsInitialized);
        //order array
        $orderedCurrentAutoSettings = \array_merge($autoSettingsInitialized, $currentAutoSettings);
        $autoSettings = ['minimum', 'intermediate', 'average', 'deluxe', 'premium', 'optimum'];
        for ($j = 0; $j < 6; $j++) {
            if (\array_values($orderedCurrentAutoSettings) === \array_column($autoSettingsMap, 's' . ($j + 1))) {
                return $autoSettings[$j];
            }
        }
        //No auto setting configured
        return \false;
    }
}
