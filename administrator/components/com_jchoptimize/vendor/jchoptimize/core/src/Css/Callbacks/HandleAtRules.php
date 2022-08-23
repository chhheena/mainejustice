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

namespace JchOptimize\Core\Css\Callbacks;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


use JchOptimize\Core\Combiner;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Fonts;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Html\Processor;
use JchOptimize\Core\Url;


class HandleAtRules extends CallbackBase
{
	public $aAtImports = array();
	public $aGFonts = array();
	public $fontFace = '';
	/** @var  Processor $oHtmlProcessor */
	protected $oHtmlProcessor;

	function processMatches( $aMatches, $sContext )
	{
		if ( $sContext == 'charset' )
		{
			return '';
		}

		if ( $sContext == 'font-face' )
		{
			if ( ! preg_match( '#font-display#i', $aMatches[0] ) )
			{
				$aMatches[0] = preg_replace( '#;?\s*}$#', ';font-display:swap;}', $aMatches[0] );
			}

			if ( $this->oParams->get( 'pro_optimize_fonts', '0' ) && empty( $this->aUrl['combining-fontface'] ) )
			{
				$this->fontFace .= $aMatches[0];

				return '';
			}

			return $aMatches[0];
		}

		//At this point we should be in import context
		$sUrl   = $aMatches[3];
		$sMedia = $aMatches[4];

		//Handle Google fonts files
		if ( strpos( $sUrl, 'fonts.googleapis.com' ) !== false )
		{
			//If we're optimizing Google fonts then we need to save the url
			if ( $this->oParams->get( 'pro_optimize_gfont_enable', '0' ) )
			{
				$this->aGFonts[] = array( 'url' => $sUrl, 'media' => $sMedia );

				return '';
			}
			//Otherwise, don't attempt to retrieve contents
			else
			{
				$this->aAtImports[] = $aMatches[0];

				return '';
			}
		}

		//If we're not replacing @imports then just save and return
		if ( ! $this->oParams->get( 'replaceImports', '0' ) )
		{
			$this->aAtImports[] = $aMatches[0];

			return '';
		}

		$oFilesManager = FilesManager::getInstance( $this->oParams );

		if ( empty( $sUrl )
			|| ! $oFilesManager->isHttpAdapterAvailable( $sUrl )
			|| ( Url::isSSL( $sUrl ) && ! extension_loaded( 'openssl' ) )
			|| ( ! Url::isHttpScheme( $sUrl ) )
		)
		{
			return $aMatches[0];
		}

		if ( $oFilesManager->isDuplicated( $sUrl ) )
		{
			return '';
		}

		//Try to get contents from imported url
		$aUrlArray = array();

		$aUrlArray[0]['url']   = $sUrl;
		$aUrlArray[0]['media'] = $sMedia;
		//$aUrlArray[0]['id']    = md5($aUrlArray[0]['url'] . $this->oHtmlProcessor->sFileHash);

		$oCombiner = new Combiner( $this->oParams );

		try
		{
			$sFileContents = $oCombiner->combineFiles( $aUrlArray, 'css' );
		}
		catch ( Exception $e )
		{
			return $aMatches[0];
		}

		return $sFileContents['content'];
	}
}
