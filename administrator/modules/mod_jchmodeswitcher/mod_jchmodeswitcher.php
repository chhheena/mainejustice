<?php

defined( '_JEXEC' ) or die( 'Restricted Access' );

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** @var Registry $params */

$sUrl = 'index.php?option=com_jchoptimize&view=ModeSwitcher';

if ( PluginHelper::isEnabled( 'system', 'jchoptimize' ) )
{
	$sMode = Text::_( 'MOD_JCHMODESWITCHER_PRODUCTION' );
	$sUrl  .= '&task=setDevelopment';
}
else
{
	$sMode = Text::_( 'MOD_JCHMODESWITCHER_DEVELOPMENT' );
	$sUrl  .= '&task=setProduction';
}

$sUrl = Route::_( $sUrl . '&return=' . base64_encode( Uri::getInstance()->toString() ) );

require ModuleHelper::getLayoutPath( 'mod_jchmodeswitcher', $params->get( 'layout', 'default' ) );