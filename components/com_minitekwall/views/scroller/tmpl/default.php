<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

// Page title
if ($this->scr_page_title)
{
	if ($this->params->get('show_page_heading', 1)) 
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$active = $menu->getActive();

		if ($active->params->get('page_heading'))
		{
			$page_heading = $active->params->get('page_heading');
		} 
		else 
		{
			$doc = JFactory::getDocument();
			$page_heading = $doc->getTitle();
		}
	
		?><div class="page-header">
			<h1><?php echo $this->escape($page_heading); ?></h1>
		</div><?php 
	}
}

// Suffix
$suffix = '';

if (isset($this->suffix))
{
	$suffix = $this->suffix;
}

////////////////////////////////////////////////////////////////
// Scroller Container
////////////////////////////////////////////////////////////////
?><div id="mnwall_scr_<?php echo $this->widgetID; 
	?>" class="mnwall_scr mnwall_<?php echo $this->scroller_layout; ?> <?php 
	echo $suffix; 
	?>" style="background-color: <?php echo $this->cont_bg; ?>; <?php 
	if ($this->cont_image) 
	{
		?>background-image: url(<?php echo $this->cont_image; ?>); <?php 
	}
	?>padding-top: <?php echo $this->cont_padding; ?>px; padding-bottom: <?php echo $this->cont_padding; ?>px;"><?php
	include (dirname(__FILE__).'/'.$this->getLayout().'_'.$this->scroller_layout.'.php');
?></div>
