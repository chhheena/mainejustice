<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="mostread<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) : ?>
	<?php 
		$attrbs 		= json_decode($item->attribs);
		$images 		= json_decode($item->images);
		$intro_image 	= '';

		if(isset($attrbs->spfeatured_image) && $attrbs->spfeatured_image != '') {

			$intro_image = $attrbs->spfeatured_image;
			$basename = basename($intro_image);
			$list_image = JPATH_ROOT . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_large.' . JFile::getExt($basename);
			if(file_exists($list_image)) {
				$thumb_image = JURI::root(true) . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_large.' . JFile::getExt($basename);
			}

		} elseif(isset($images->image_intro) && !empty($images->image_intro)) {
			$thumb_image = $images->image_intro;
		} ?>

	<li itemscope itemtype="https://schema.org/Article">
		<img src="<?php echo $thumb_image; ?>" alt="image">
		<div class="article-info">
			<p class="category-tag">
				<a href="<?php echo JURI::base() . "index.php/" . $item->category_title ?>"><?php echo $item->category_title; ?></a>
			</p>
			<p class="date-time" itemprop="dateCreated"><?php echo Jhtml::_('date', $item->created, 'M d, H:i a'); ?></p>
			<a class="title" href="<?php echo $item->link; ?>" itemprop="url">
				<span itemprop="name">
					<?php echo $item->title; ?>
				</span>
			</a>
		</div> <!-- //.article-info -->
	</li>
<?php endforeach; ?>
</ul>
