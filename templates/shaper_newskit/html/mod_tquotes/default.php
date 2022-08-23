<?php 
/*------------------------------------------------------------------------
# mod_tquotes 
# ------------------------------------------------------------------------
# author    Kevin Burke
# copyright Copyright (C) 2012 Mytidbits.us All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.mytidbits.us
# Technical Support:  Forum - http://www.mytidbits.us/forum
-------------------------------------------------------------------
*/ 
//no direct access
defined('_JEXEC') or die('Restricted access'); 


$style_method=$params->get('style_method');

$text_color=$params->get('text_color');
$background=$params->get('background');
$fontweight=$params->get('font-weight');
$fontsize=$params->get('font-size');
$fontstyle=$params->get('font-style');
$sep=$params->get('sep');
$quotemarks=$params->get('quotemarks');
$moduleclass_sfx=$params->get('moduleclass_sfx');
$font_family=$params->get('font_family');
$padding=$params->get('padding');
$line_height=$params->get('line-height');


//JHtml::_('stylesheet', 'mod_tquotes/tquote.css ' , array(), true);
//	JHtml::_('stylesheet', 'mod_tquotes/tquote1.css ' , array(), true);
//	JHtml::_('stylesheet', 'mod_tquotes/tquote2.css ' , array(), true);
//echo '<br>quote marks='.$quotemarks.'<br>';


if($sep)
{
	$quote = $rows[$num];
	$parts=explode($sep,$quote);
	
	$quote= $parts[0];
	$author=&$parts[1];
}
else
	$quote = $rows[$num];	
	
//*************display options	**************************************
		
 switch($style_method)
{
case '1':
	
		if($quotemarks==0 )
				{echo $quote; }
		if($quotemarks==1 ) 
				{echo '"'.$quote.'"';}?>
	 	 <div align = "right"><?php echo $author; ?>	</div>	
	<?php	break;
	
case '2': ?>
			<div style="color:<?php echo $text_color;?>;
			font-family:<?php echo $font_family; ?>;
			padding:<?php echo $padding.'px'; ?> ;
			line-height:<?php echo $line_height.'px'; ?>  ;
			background:<?php echo $background; ?> ;
			font-weight:<?php echo $fontweight; ?> ;
			font-size:<?php echo $fontsize;?> ;
			font-style:<?php echo $fontstyle;?> "> 
		<?php if($quotemarks==0 )
		{echo $quote;}
		if($quotemarks==1 )	
		{echo '"'.$quote.'"';}	
			?>
			<div align = "right"><?php echo $author; ?></div></div>	
	<?php
	
	break;
	
case '3':
	
		if($quotemarks==0 )
		{ ?>
					<div class="mod_tquote_quote"><p><?php echo $quote; ?></p></div>
					<div class="mod_tquote_author"><p><?php echo $author; ?></p></div>
		<?php }
		if($quotemarks==1 )	
		{ ?>
					<div class="mod_tquote_quote"><p><span><?php echo $quote;?> </span></p></div>
	 				<div class="mod_tquote_author"><p><?php echo $author; ?></p></div>
		<?php }
	break;
	
	case '4':
	
		if($quotemarks==0 )
		{ ?>
					<div class="mod_tquote1_quote"><p><?php echo $quote; ?></p></div>
					<div class="mod_tquote1_author"><p><?php echo $author; ?></p></div>
		<?php }
		if($quotemarks==1 )	
		{ ?>
					<div class="mod_tquote1_quote"><p><span><?php echo $quote;?></span> </p></div>
	 				<div class="mod_tquote1_author"><p><?php echo $author; ?></p></div>
	<?php	}
	break;
	
	case '5':
	
		if($quotemarks==0 )
		{ ?>
					<div class="mod_tquote2_quote"><p><?php echo $quote; ?></p></div>
					<div class="mod_tquote2_author"><p> <?php echo $author; ?></p></div>
		<?php }
		if($quotemarks==1 )	
		{ ?>
					<div class="mod_tquote2_quote"><p><span><?php echo $quote;?></span></p></div>
	 				<div class="mod_tquote2_author"><p><?php echo $author; ?></p></div>
	<?php }	
		
	break;
	default:
	echo 'You have to choose a display method. Please go to admin>modules>tquotes> and choose one';
}		

