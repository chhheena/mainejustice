<?php
/*
 * @version		$Id: mod_allvideosharegallery.php 3.5.0 2020-01-25 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2020 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the syndicate functions only once
require_once( dirname( __FILE__ ) . '/helper.php' );
require_once( JPATH_ROOT . '/administrator/components/com_allvideoshare/libraries/utils.php' );

$items = AllVideoShareGalleryHelper::getItems( $params );
$config = AllVideoShareUtils::getConfig();

if ( $config->is_premium ) {
	$popup = $params->get( 'popup', $config->popup );
	if ( $popup < 0 ) {
		$popup = $config->popup;
	}
} else {
	$popup = 0;
}

$player_ratio = 56.25;		
if ( $popup ) {	
	$ratio = AllVideoShareUtils::getPlayer( 'ratio' );
	if ( $ratio > 0 ) {
		$player_ratio = $ratio;
	} 
}
		
$doc = JFactory::getDocument();

if ( $config->load_bootstrap_css ) {
	$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/bootstrap.css' ), 'text/css', 'screen' );
}
		
if ( $config->load_icomoon_font ) {
	$doc->addStyleSheet( JURI::root( true ) . '/media/jui/css/icomoon.css', 'text/css', 'screen' );
}

if ( $popup ) {
	$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/magnific-popup/magnific-popup.css' ), 'text/css', 'screen' );
}
		
$doc->addStyleSheet( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/css/allvideoshare.css' ), 'text/css', 'screen' );

if ( ! empty( $config->custom_css ) ) {
	$doc->addStyleDeclaration( $config->custom_css );
}

if ( $popup ) {
	JHtml::_( 'jquery.framework' );
	
	$doc->addScript( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/magnific-popup/jquery.magnific-popup.min.js' ) );
	$doc->addScript( AllVideoShareUtils::prepareURL( 'components/com_allvideoshare/assets/js/allvideoshare.js' ) );
}

require ( JModuleHelper::getLayoutPath( 'mod_allvideosharegallery', 'default_' . $params->get( 'type' ) ) );