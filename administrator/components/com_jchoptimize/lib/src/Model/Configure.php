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
namespace JchOptimize\Model;

\defined('_JEXEC') or \dir('Restricted Access');
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Platform\Utility;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelInterface;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use Joomla\Registry\Registry;
class Configure implements DatabaseModelInterface, StatefulModelInterface, ContainerAwareInterface
{
    use DatabaseModelTrait;
    use StatefulModelTrait;
    use \JchOptimize\Model\SaveSettingsTrait;
    use ContainerAwareTrait;
    /**
     * @var ModeSwitcher
     */
    private $modeSwitcherModel;
    public function __construct(Registry $params, \JchOptimize\Model\ModeSwitcher $modeSwitcherModel)
    {
        $this->modeSwitcherModel = $modeSwitcherModel;
        $this->setState($params);
    }
    public function applyAutoSettings($autoSetting)
    {
        $aAutoParams = Icons::autoSettingsArrayMap();
        $aSelectedSetting = \array_column($aAutoParams, $autoSetting);
        $aSettingsToApply = \array_combine(\array_keys($aAutoParams), $aSelectedSetting);
        foreach ($aSettingsToApply as $setting => $value) {
            $this->state->set($setting, $value);
        }
        $this->state->set('combine_files_enable', '1');
        $this->saveSettings();
    }
    public function toggleSetting($setting)
    {
        if (\is_null($setting)) {
            //@TODO some logging here
            return \false;
        }
        if ($setting == 'integrated_page_cache_enable') {
            try {
                $this->modeSwitcherModel->togglePluginState('jchoptimizepagecache', !Utility::isPageCacheEnabled($this->state));
                if (JCH_PRO) {
                    /** @see CaptureCache::updateHtaccess() */
                    $this->container->get(CaptureCache::class)->updateHtaccess(\true);
                }
                return \true;
            } catch (\Exception $e) {
                return \false;
            }
        }
        $iCurrentSetting = (int) $this->state->get($setting);
        $newSetting = (string) \abs($iCurrentSetting - 1);
        if ($setting == 'pro_remove_unused_css' && $newSetting == '1') {
            $this->state->set('optimizeCssDelivery_enable', '1');
        }
        if ($setting == 'optimizeCssDelivery_enable' && $newSetting == '0') {
            $this->state->set('pro_remove_unused_css', '0');
        }
        if ($setting == 'pro_smart_combine') {
            if ($newSetting == '1') {
                $aSCValues = AdminAjax::getInstance('SmartCombine')->run();
                $aValues = \array_merge($aSCValues->data['css'], $aSCValues->data['js']);
                $this->state->set('pro_smart_combine_values', \rawurlencode(\json_encode($aValues)));
            } else {
                $this->state->set('pro_smart_combine_values', '');
            }
        }
        $this->state->set($setting, $newSetting);
        $this->saveSettings();
    }
}
