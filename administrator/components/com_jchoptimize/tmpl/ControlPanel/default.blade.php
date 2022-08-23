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

defined( '_JEXEC' ) or die( 'Restricted Access' );

use JchOptimize\Core\Admin\Icons;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;

$aToggleIcons         = Icons::compileToggleFeaturesIcons( Icons::getToggleSettings() );
$aAdvancedToggleIcons = Icons::compileToggleFeaturesIcons( Icons::getAdvancedToggleSettings() );

?>
    <div class="grid mt-3" style="grid-template-rows: auto;">
        <div class="g-col-12 g-col-lg-8" style="grid-row-end: span 2;">
            <div id="combine-files-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_COMBINE_FILES_AUTO_SETTINGS')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_COMBINE_FILES_DESC')</p>
                <div class="icons-container">
                    {{Icons::printIconsHTML(Icons::compileToggleFeaturesIcons(Icons::getCombineFilesEnableSetting()))}}
                    <div class="icons-container">
                        {{Icons::printIconsHTML(Icons::compileAutoSettingsIcons(Icons::getAutoSettingsArray()))}}
                    </div>
                </div>
            </div>
        </div>
        <div class="g-col-12 g-col-lg-4" style="grid-row-end: span 3;" >
            <div id="utility-settings-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_UTILITY_SETTINGS')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_UTILITY_DESC')</p>
                <div>
                    <div class="icons-container">
                        {{Icons::printIconsHTML(Icons::compileUtilityIcons(Icons::getUtilityArray(['browsercaching', 'orderplugins', 'keycache'])))}}
                        <div class="icons-container">
                                {{Icons::printIconsHTML(Icons::compileUtilityIcons(Icons::getUtilityArray(['cleancache'])))}}
                            <div>
                                <br>
                                <div><em>{{JText::sprintf( 'COM_JCHOPTIMIZE_FILES', $this->no_files )}}</em></div>
                                <div><em>{{JText::sprintf( 'COM_JCHOPTIMIZE_SIZE', $this->size )}}</em></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="clear:both"></div>
            </div>
        </div>
        <div class="g-col-12 g-col-lg-8" style="grid-row-end: span 3;">
            <div id="toggle-settings-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_STANDARD_SETTINGS')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_STANDARD_SETTINGS_DESC')</p>
                <div>
                    <div class="icons-container">
                        {{Icons::printIconsHTML($aToggleIcons)}}
                    </div>
                </div>
            </div>
        </div>
        <div class="g-col-12 g-col-lg-4" style="grid-row-end: span 2;">
            <div id="advanced-settings-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_ADVANCED_SETTINGS')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_ADVANCED_SETTINGS_DESC')</p>
                <div>
                    <div class="icons-container">
                        {{Icons::printIconsHTML($aAdvancedToggleIcons)}}
                    </div>
                </div>
            </div>
        </div>
        <div class="g-col-12">
            <div id="copyright-block" class="admin-panel-block">
                <strong>JCH Optimize Pro {{JCH_VERSION}}</strong> Copyright 2021 &copy; <a
                        href="https://www.jch-optimize.net/">JCH Optimize</a>
            </div>
        </div>
    </div>