<?php

/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Language\Text;

class HelixUltimateFeatureGotop
{

	private $params;

	public function __construct( $params )
	{
		$this->params = $params;
		$this->position = $this->params->get('goto_top_position');
		$this->load_pos = 'after';
	}

	public function renderFeature()
	{

		$html  = '<a href="#" class="sp-scroll-up" aria-label="Scroll Up">
			<span class="goto-top">'. Text::_('HELIX_ULTIMATE_GOTO_TOP') .'</span>
		</a>';
		return $html;

	}
}
