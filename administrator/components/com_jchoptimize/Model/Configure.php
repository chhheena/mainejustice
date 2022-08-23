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

namespace JchOptimize\Component\Admin\Model;

defined( '_JEXEC' ) or dir( 'Restricted Access' );

use FOF40\Model\Model;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Utility;
use Joomla\Registry\Registry;

class Configure extends Model
{
	public function applyAutoSettings()
	{
		$aAutoParams = Icons::autoSettingsArrayMap();

		$sAutoSetting     = $this->getState( 'autosetting', 's1' );
		$aSelectedSetting = array_column( $aAutoParams, $sAutoSetting );

		$aSettingsToApply = array_combine( array_keys( $aAutoParams ), $aSelectedSetting );

		$this->container->params->setParams( $aSettingsToApply );
		$this->container->params->set( 'combine_files_enable', '1' );
		$this->container->params->save();
	}

	public function toggleSetting()
	{
		$setting = $this->getState( 'setting', null );

		if ( is_null( $setting ) )
		{
			//@TODO some logging here
			return false;
		}

		if ( $setting == 'integrated_page_cache_enable' )
		{
			/** @var ModeSwitcher $oModeSwitcherModel */
			$oModeSwitcherModel = $this->container->factory->model( 'ModeSwitcher' );
			$pageCache          = 'cache';

			if ( $this->container->params->get( 'pro_page_cache_integration_enable', '1' ) )
			{
				$pageCache = $this->container->params->get( 'pro_page_cache_select', 'cache' );
			}

			try
			{
				$oModeSwitcherModel->togglePluginState( $pageCache, ! Utility::isPageCacheEnabled( new Settings( new Registry( $this->container->params->getParams() ) ) ) );

				return true;
			}
			catch ( \Exception $e )
			{
				return false;
			}

		}

		$iCurrentSetting = (int)$this->container->params->get( $setting );
		$newSetting      = (string)abs( $iCurrentSetting - 1 );

		if ( $setting == 'pro_remove_unused_css' && $newSetting == '1' )
		{
			$this->container->params->set( 'optimizeCssDelivery_enable', '1' );
		}

		if ( $setting == 'optimizeCssDelivery_enable' && $newSetting == '0' )
		{
			$this->container->params->set( 'pro_remove_unused_css', '0' );
		}

		if ( $setting == 'pro_smart_combine' )
		{
			if ( $newSetting == '1' )
			{
				$aSCValues = AdminAjax::getInstance( 'SmartCombine' )->run();
				$aValues   = array_merge( $aSCValues->data['css'], $aSCValues->data['js'] );

				$this->container->params->set( 'pro_smart_combine_values', rawurlencode( json_encode( $aValues ) ) );
			}
			else
			{
				$this->container->params->set( 'pro_smart_combine_values', '' );
			}
		}

		$this->container->params->set( $setting, $newSetting );
		$this->container->params->save();
	}
}