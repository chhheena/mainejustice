<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$tplParams 		= JFactory::getApplication()->getTemplate(true)->params;
$item 			= $displayData;
$params  		= $displayData->params;
$attribs 		= json_decode($displayData->attribs);
$images 		= json_decode($displayData->images);
$item_type  = (isset($displayData->item_type) && $displayData->item_type) ? $displayData->item_type : '';
//$imgsize 		= $tplParams->get('blog_list_image', 'default');
$imgsize 		= ($item_type == 'leading') ? 'thumbnail' : $tplParams->get('blog_list_image', 'default');
$intro_image 	= '';

// Geneate URL
$url    =  JRoute::_(ContentHelperRoute::getArticleRoute($item->id . ':' . $item->alias, $item->catid, $item->language));
$root   = JURI::base();
$root   = new JURI($root);
$url    = $root->getScheme() . '://' . $root->getHost() . $url;


if(isset($attribs->spfeatured_image) && $attribs->spfeatured_image != '') {

	if($imgsize == 'default') {
		$intro_image = $attribs->spfeatured_image;
	} else {
		$intro_image = $attribs->spfeatured_image;
		$basename = basename($intro_image);
		$image_path = JPATH_ROOT . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $imgsize .'.' . JFile::getExt($basename);
		if(file_exists($image_path)) {
			$intro_image = JURI::root(true) . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $imgsize .'.' . JFile::getExt($basename);
		}
	}
} elseif(isset($images->image_intro) && !empty($images->image_intro)) {
	$intro_image = $images->image_intro;
}

?>

<?php if(!empty($intro_image) || (isset($images->image_intro) && !empty($images->image_intro))) { ?>

<div class="entry-image intro-image">
	<div class="sppb-post-share-social">
		<span class="share-button"><i class="fa fa-share-square-o"></i></span>
		<div class="sppb-post-share-social-others">
			<a class="fa fa-facebook" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_FACEBOOK'); ?>" onClick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $url; ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo $url; ?>">
			</a>

			<a class="fa fa-twitter" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_TWITTER'); ?>" onClick="window.open('http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $item->title); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $item->title); ?>">
			</a>

			<a class="fa fa-google-plus" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_GOOGLE_PLUS'); ?>" onClick="window.open('https://plus.google.com/share?url=<?php echo $url; ?>','Google plus','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="https://plus.google.com/share?url=<?php echo $url; ?>" >
			</a>

			<!-- <a class="fa fa-pinterest" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_PINTEREST'); ?>" onClick="window.open('http://pinterest.com/pin/create/button/?url=<?php echo $url; ?>&amp;description=<?php echo $url; ?>','Pinterest','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="http://pinterest.com/pin/create/button/?url=<?php echo $url; ?>&amp;description='.$page_title. '" >
			</a>

			<a class="fa fa-linkedin" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_LINKEDIN'); ?>" onClick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>','Linkedin','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>" >
			</a> -->
		</div>
	</div> <!-- //.sppb-post-share-social -->

	<?php if ($params->get('link_titles') && $params->get('access-view')) { ?>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>">
	<?php } ?>

	<img
	<?php if ($images->image_intro_caption):
			echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption) . '"';
			endif; ?>
	src="<?php echo htmlspecialchars($intro_image); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>" itemprop="thumbnailUrl"/>

	<?php if ($params->get('link_titles') && $params->get('access-view')) { ?>
		</a>
	<?php } ?>
</div>
<?php } ?>
