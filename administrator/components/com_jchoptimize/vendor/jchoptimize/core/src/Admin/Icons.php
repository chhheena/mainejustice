<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin;

use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Icons
{

	public static function printIconsHTML( $aButtons )
	{
		$sIconsHTML = '';

		foreach ( $aButtons as $aButton )
		{
			$sContentAttr = Utility::bsTooltipContentAttribute();
			$sTooltip     = @$aButton['tooltip'] ? " class=\"hasPopover fig-caption\" title=\"{$aButton['name']}\" {$sContentAttr}=\"{$aButton['tooltip']}\" " : ' class="fig-caption"';
			$sIconSrc     = Paths::iconsUrl() . '/' . $aButton['icon'];
			$sToggle      = '<i class="toggle fa"></i>';

			if ( ! JCH_PRO && ! empty ( $aButton['proonly'] ) )
			{
				$aButton['link']   = '';
				$aButton['script'] = '';
				$aButton['class']  = 'disabled proonly';
				$sToggle           = '<span id="proonly-span"><em>(Pro Only)</em></span>';
			}

			$sIconsHTML .= <<<HTML
<figure id="{$aButton['id']}" class="icon {$aButton['class']}">
	<a href="{$aButton['link']}" class="btn" {$aButton['script']}>
		<img src="{$sIconSrc}" alt="" width="50" height="50" />
		<span{$sTooltip}>{$aButton['name']}</span>
		{$sToggle}
	</a>
</figure>

HTML;
		}

		return $sIconsHTML;
	}

	public static function getAutoSettingsArray()
	{
		return [
			[
				'name'    => 'Minimum',
				'icon'    => 'minimum.png',
				'setting' => 1,

			],
			[
				'name'    => 'Intermediate',
				'icon'    => 'intermediate.png',
				'setting' => 2
			],
			[
				'name'    => 'Average',
				'icon'    => 'average.png',
				'setting' => 3
			],
			[
				'name'    => 'Deluxe',
				'icon'    => 'deluxe.png',
				'setting' => 4
			],
			[
				'name'    => 'Premium',
				'icon'    => 'premium.png',
				'setting' => 5
			],
			[
				'name'    => 'Optimum',
				'icon'    => 'optimum.png',
				'setting' => 6
			]
		];
	}

	public static function compileAutoSettingsIcons( $aSettings )
	{
		$aButtons = [];

		for ( $i = 0; $i < count( $aSettings ); $i++ )
		{
			$aButtons[ $i ]['link']    = '';
			$aButtons[ $i ]['icon']    = $aSettings[ $i ]['icon'];
			$aButtons[ $i ]['name']    = $aSettings[ $i ]['name'];
			$aButtons[ $i ]['script']  = 'onclick="jchPlatform.applyAutoSettings(' . $aSettings[ $i ]['setting'] . ', 1); return false;"';
			$aButtons[ $i ]['id']      = strtolower( str_replace( ' ', '-', trim( $aSettings[ $i ]['name'] ) ) );
			$aButtons[ $i ]['class']   = 'disabled';
			$aButtons[ $i ]['tooltip'] = htmlspecialchars( self::generateAutoSettingTooltip( $aSettings[ $i ]['setting'] ) );
		}

		$oParams             = Plugin::getPluginParams();
		$sCombineFilesEnable = $oParams->get( 'combine_files_enable', '0' );
		$aParamsArray        = $oParams->toArray();

		$aAutoSettings = self::autoSettingsArrayMap();

		$aAutoSettingsInit = array_map( function ( $a ) {
			return '0';
		}, $aAutoSettings );

		$aCurrentAutoSettings = array_intersect_key( $aParamsArray, $aAutoSettingsInit );
		//order array
		$aCurrentAutoSettings = array_merge( $aAutoSettingsInit, $aCurrentAutoSettings );

		if ( $sCombineFilesEnable )
		{
			for ( $j = 0; $j < 6; $j++ )
			{
				if ( array_values( $aCurrentAutoSettings ) === array_column( $aAutoSettings, 's' . ( $j + 1 ) ) )
				{
					$aButtons[ $j ]['class'] = 'enabled';

					break;
				}
			}
		}

		return $aButtons;
	}

	private static function generateAutoSettingTooltip( $setting )
	{
		$aAutoSettingsMap      = self::autoSettingsArrayMap();
		$aCurrentSettingValues = array_column( $aAutoSettingsMap, 's' . $setting );
		$aCurrentSettingArray  = array_combine( array_keys( $aAutoSettingsMap ), $aCurrentSettingValues );
		$aSetting              = array_map( function ( $v ) {
			return ( $v == '1' ) ? 'on' : 'off';
		}, $aCurrentSettingArray );

		$text = <<<HTML
<h4 class="list-header">CSS</h4>
<ul class="unstyled list-unstyled">
<li>Combine CSS <i class="toggle fa {$aSetting['css']}"></i></li>
<li>Minify CSS <i class="toggle fa {$aSetting['css_minify']}"></i></li>
<li>Resolve @imports <i class="toggle fa {$aSetting['replaceImports']}"></i></li>
<li>Include in-page styles <i class="toggle fa {$aSetting['inlineStyle']}"></i></li>
</ul>
<h4 class="list-header">Javascript</h4>
<ul class="unstyled list-unstyled">
<li>Combine Javascript <i class="toggle fa {$aSetting['javascript']}"></i></li>
<li>Minify javascript <i class="toggle fa {$aSetting['js_minify']}"></i></li>
<li>Include in-page scripts <i class="toggle fa {$aSetting['inlineScripts']}"></i></li>
<li>Place js at bottom <i class="toggle fa {$aSetting['bottom_js']}"></i></li>
<li>Defer/Async javascript <i class="toggle fa {$aSetting['loadAsynchronous']}"></i></li>
</ul>
<h4 class="list-header">Combine files</h4>
<ul class="unstyled list-unstyled">
<li>Gzip js/css <i class="toggle fa {$aSetting['gzip']}"></i> </li>
<li>Minify HTML <i class="toggle fa {$aSetting['html_minify']}"></i> </li>
<li>Include third-party files <i class="toggle fa {$aSetting['includeAllExtensions']}"></i></li>
<li>Include external files <i class="toggle fa {$aSetting['phpAndExternal']}"></i></li>
</ul>
HTML;

		return $text;
	}

	public static function autoSettingsArrayMap()
	{
		return [
			'css'                  => [ 's1' => '1', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'javascript'           => [ 's1' => '1', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'gzip'                 => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'css_minify'           => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'js_minify'            => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'html_minify'          => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'includeAllExtensions' => [ 's1' => '0', 's2' => '0', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'replaceImports'       => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'phpAndExternal'       => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'inlineStyle'          => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'inlineScripts'        => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'bottom_js'            => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '0', 's5' => '1', 's6' => '1' ],
			'loadAsynchronous'     => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '0', 's5' => '0', 's6' => '1' ]
		];
	}

	public static function getApi2UtilityArray()
	{
		return self::getUtilityArray( [ 'restoreimages', 'deletebackups' ] );
	}

	public static function getUtilityArray( $aActions = array() )
	{
		$aUtilities = [
			( $action = 'browsercaching' )  => [
				'action'  => $action,
				'icon'    => 'browser_caching.png',
				'name'    => 'Optimize .htaccess',
				'tooltip' => Utility::translate( 'Use this button to add codes to your htaccess file to enable leverage browser caching and gzip compression.' )
			],
			( $action = 'filepermissions' ) => [
				'action'  => $action,
				'icon'    => 'file_permissions.png',
				'name'    => 'Fix file permissions',
				'tooltip' => Utility::translate( "If your site has lost CSS formatting after enabling the plugin, the problem could be that the plugin files were installed with incorrect file permissions so the browser cannot access the cached combined file. Click here to correct the plugin's file permissions." )
			],
			( $action = 'cleancache' )      => [
				'action'  => $action,
				'icon'    => 'clean_cache.png',
				'name'    => 'Clean Cache',
				'tooltip' => Utility::translate( "Click this button to clean the plugin's cache and page cache. If you have edited any CSS or javascript files you need to clean the cache so the changes can be visible." )
			],
			( $action = 'orderplugins' )    => [
				'action'  => $action,
				'icon'    => 'order_plugin.png',
				'name'    => 'Order Plugin',
				'tooltip' => Utility::translate( 'The published order of the plugin is important! When you click on this icon, it will attempt to order the plugin correctly.' )
			],
			( $action = 'keycache' )        => [
				'action'  => $action,
				'icon'    => 'keycache.png',
				'name'    => 'Generate new cache key',
				'tooltip' => Utility::translate( "If you've made any changes to your files generate a new cache key to counter browser caching of the old content." )
			],
			( $action = 'restoreimages' )   => [
				'action'  => $action,
				'icon'    => 'restoreimages.png',
				'name'    => 'Restore Original Images,',
				'tooltip' => Utility::translate( "If you're not satisfied with the images that were optimized you can restore the original ones by clicking this button if they were not deleted. This will also remove any webp image created from the restored file." ),
				'proonly' => true
			],
			( $action = 'deletebackups' )   => [
				'action'  => $action,
				'icon'    => 'deletebackups.png',
				'name'    => 'Delete Backup Images',
				'tooltip' => Utility::translate( "This will permanently delete the images that were backed up. There's no way to undo this so be sure you're satisfied with the ones that were optimized before clicking this button." ),
				'proonly' => true
			]

		];

		if ( empty( $aActions ) )
		{
			return $aUtilities;
		}
		else
		{
			return array_intersect_key( $aUtilities, array_flip( $aActions ) );
		}
	}

	public static function compileUtilityIcons( $aUtilities )
	{
		$aIcons = [];
		$i      = 0;

		foreach ( $aUtilities as $aUtility )
		{
			$aIcons[ $i ]['link']    = Paths::adminController( $aUtility['action'] );
			$aIcons[ $i ]['icon']    = $aUtility['icon'];
			$aIcons[ $i ]['name']    = Utility::translate( $aUtility['name'] );
			$aIcons[ $i ]['id']      = strtolower( str_replace( ' ', '-', trim( $aUtility['name'] ) ) );
			$aIcons[ $i ]['tooltip'] = @$aUtility['tooltip'] ?: false;
			$aIcons[ $i ]['script']  = '';
			$aIcons[ $i ]['class']   = '';
			$aIcons[ $i ]['proonly'] = @$aUtility['proonly'] ?: false;

			$i++;
		}

		return $aIcons;
	}

	public static function getToggleSettings()
	{
		$oParams = Plugin::getPluginParams();

		$aSettings = [
			[
				'name'    => 'Add Image Attributes',
				'setting' => ( $setting = 'img_attributes_enable' ),
				'icon'    => 'img_attributes.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Adds \'height\' and/or \'width\' attributes to &lt:img&gt;\'s, if missing, to reduce CLS.' )
			],
			[
				'name'    => 'Sprite Generator',
				'setting' => ( $setting = 'csg_enable' ),
				'icon'    => 'sprite_gen.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Combines select background images into a sprite.' )
			],
			[
				'name'    => 'Http/2 Push',
				'setting' => ( $setting = 'http2_push_enable' ),
				'icon'    => 'http2_push.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Preloads critical assets using the http/2 protocol to improve LCP.' )
			],
			[
				'name'    => 'Lazy Load Images',
				'setting' => ( $setting = 'lazyload_enable' ),
				'icon'    => 'lazyload.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Defer images that fall below the fold.' )
			],
			[
				'name'    => 'Optimize CSS Delivery',
				'setting' => ( $setting = 'optimizeCssDelivery_enable' ),
				'icon'    => 'optimize_css_delivery.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Eliminates CSS render-blocking' )
			],
			[
				'name'    => 'Optimize Google Fonts',
				'setting' => ( $setting = 'pro_optimize_gfont_enable' ),
				'icon'    => 'optimize_gfont.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true,
				'tooltip' => Utility::translate( 'Optimizes the loading of Google fonts.' )
			],
			[
				'name'    => 'CDN',
				'setting' => ( $setting = 'cookielessdomain_enable' ),
				'icon'    => 'cdn.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'tooltip' => Utility::translate( 'Loads static assets from a CDN server. Requires the CDN domain(s) to be configured on the Configuration tab.' )
			],
			[
				'name'    => 'Smart Combine',
				'setting' => ( $setting = 'pro_smart_combine' ),
				'icon'    => 'smart_combine.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true,
				'tooltip' => Utility::translate( 'Intelligently combines files in a number of smaller files, instead of one large file for better http2 delivery.' )
			],
			[
				'name'    => 'Page Cache',
				'setting' => 'integrated_page_cache_enable',
				'icon'    => 'cache.png',
				'enabled' => Utility::isPageCacheEnabled( $oParams ),
				'proonly' => (bool)( JCH_PLATFORM != 'WordPress' ),
				'tooltip' => Utility::translate( 'Toggles on/off the Page Cache feature.' )
			]
		];

		return $aSettings;
	}

	public static function getCombineFilesEnableSetting()
	{
		$oParams = Plugin::getPluginParams();

		return [
			[
				'name'    => 'Combine Files Enable',
				'setting' => ( $setting = 'combine_files_enable' ),
				'icon'    => 'combine_files_enable.png',
				'enabled' => $oParams->get( $setting, '1' )
			]
		];
	}

	public static function getAdvancedToggleSettings()
	{
		$oParams = Plugin::getPluginParams();

		$aSettings = [
			[
				'name'    => 'Remove Unused CSS',
				'setting' => ( $setting = 'pro_remove_unused_css' ),
				'icon'    => 'remove_unused_css.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true,
				'tooltip' => Utility::translate( 'Loads only the critical CSS required for rendering the page above the fold until user interacts with the page. Requires Optimize CSS Delivery to be enabled and may need the CSS Dynamic Selectors setting to be configured to work properly.' )
			],
			[
				'name'    => 'Reduce DOM',
				'setting' => ( $setting = 'pro_reduce_dom' ),
				'icon'    => 'reduce_dom.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true,
				'tooltip' => Utility::translate( '\'Defers\' the loading of some HTML block elements to speed up page rendering.' )
			],
			/*		[
						'name'    => 'Remove Unused Javascript',
						'setting' => ( $setting = 'pro_remove_unused_js_enable' ),
						'icon'    => 'remove_unused_js.png',
						'enabled' => $oParams->get( $setting, '0' ),
						'proonly' => true
					]   */
		];

		return $aSettings;
	}

	public static function compileToggleFeaturesIcons( $aSettings )
	{
		$aButtons = array();

		for ( $i = 0; $i < count( $aSettings ); $i++ )
		{
			$aButtons[ $i ]['link']    = '';
			$aButtons[ $i ]['icon']    = $aSettings[ $i ]['icon'];
			$aButtons[ $i ]['name']    = Utility::translate( $aSettings[ $i ]['name'] );
			$aButtons[ $i ]['script']  = 'onclick="jchPlatform.toggleSetting(\'' . $aSettings[ $i ]['setting'] . '\'); return false;"';
			$aButtons[ $i ]['id']      = strtolower( str_replace( ' ', '-', trim( $aSettings[ $i ]['name'] ) ) );
			$aButtons[ $i ]['class']   = $aSettings[ $i ]['enabled'] ? 'enabled' : 'disabled';
			$aButtons[ $i ]['proonly'] = ! empty( $aSettings[ $i ]['proonly'] ) ? true : false;
			$aButtons[ $i ]['tooltip'] = @$aSettings[ $i ]['tooltip'] ?: false;
		}

		return $aButtons;
	}
}