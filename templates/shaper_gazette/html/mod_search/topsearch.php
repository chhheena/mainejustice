<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$app    = JFactory::getApplication();
$tmpl_path   = JPATH_BASE .'/templates/'.$app->getTemplate().'/';
require_once $tmpl_path . 'helper.php';

$tags = TplShaperGazetteHelper::getTagList();

?>

<div class="top-search-wrapper">

	<div class="icon-top-wrapper">
		<i class="fa fa-search search-icon" aria-hidden="true"></i>
		<div class="close-icon">
			<div class="icon-close-wrap">
				<div class="icon-close"></div>
			</div>
		</div>
	</div>

	<div class="row top-search-input-wrap">
		<div class="col-sm-12">
			<div class="searchwrapper">
				<h3 class="input-title">What Are You Looking For?</h3>
				<form action="<?php echo JRoute::_('index.php');?>" method="post">
					<div class="search<?php echo $moduleclass_sfx ?>">
						<?php
							$output = '<div class="top-search-wrapper"><div class="sp_search_input"><i class="fa fa-search" aria-hidden="true"></i><input name="searchword" maxlength="'.$maxlength.'"  class="mod-search-searchword inputbox'.$moduleclass_sfx.'" type="text" size="'.$width.'" value="'.$text.'"  onblur="if (this.value==\'\') this.value=\''.$text.'\';" onfocus="if (this.value==\''.$text.'\') this.value=\'\';" /></div></div>';

							if ($button) :
								if ($imagebutton) :
									$button = '<input type="image" value="'.$button_text.'" class="button'.$moduleclass_sfx.'" src="'.$img.'" onclick="this.form.searchword.focus();"/>';
								else :
									$button = '<input type="submit" value="'.$button_text.'" class="button'.$moduleclass_sfx.'" onclick="this.form.searchword.focus();"/>';
								endif;
							endif;

							switch ($button_pos) :
								case 'top' :
									$button = $button.'<br />';
									$output = $button;
									break;

								case 'bottom' :
									$button = '<br />'.$button;
									$output = $output;
									break;

								case 'right' :
									$output = $output;
									break;

								case 'left' :
								default :
									$output = $button;
									break;
							endswitch;

							echo $output;
						?>
						<input type="hidden" name="task" value="search" />
						<input type="hidden" name="option" value="com_search" />
						<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
					</div>
				</form>
			</div> <!-- /.searchwrapper -->
		</div> <!-- /.col-sm-12 -->

		<div class="col-sm-12"> <!-- /.col-sm-12 -->
			<h4 class="tags-title">Popular Tags</h4>
			<div class="popular-tags-wrap">
				<ul>
					<?php foreach ($tags as $tag) { ?>
					<li>
						<?php $tag->url = JRoute::_('index.php?option=com_search&areas[0]=tags&searchword=' . $tag->title ); ?>
						<a href="<?php echo $tag->url; ?>"><?php echo $tag->title; ?></a>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div> <!-- /.col-sm-12 -->

	</div> <!-- /.row -->
</div> <!-- /.top-search-wrapper -->	