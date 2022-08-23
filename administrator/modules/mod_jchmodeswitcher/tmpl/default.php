<?php

use Joomla\CMS\Language\Text;

defined( '_JEXEC' ) or die( 'Restricted Access' );

/**
 * @var string $sMode
 * @var string $sUrl
 */

$sModeTitle = Text::_('MOD_JCHMODESWITCHER_MODE_TITLE');

if (version_compare(JVERSION, '3.99.99', '<'))
{
	echo <<<HTML
<div class="btn-group">
<span class="btn-group separator"></span>
<span class="icon-cog"></span>{$sModeTitle}: <a href="{$sUrl}">{$sMode}</a>
</div>
HTML;
}
else
{
	echo <<<HTML
<div class="header-item">
	<a href="{$sUrl}" class="header-item-content" title="Toggle JCH Optimize state">
		<div class="header-item-icon">
			<span class="icon-cog"></span>
		</div>
		<div class="header-item-text">{$sModeTitle}: <i>${sMode}</i></div>
	</a>
</div>
HTML;
}