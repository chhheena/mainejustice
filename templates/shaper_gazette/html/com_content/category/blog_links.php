<?php

/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

?>

<ul class="list-group">
	<?php foreach ($this->link_items as &$item) : ?>
	<?php 
		$params = $item->params;
		$info    	 = $params->get('info_block_position', 0);
		$useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
			|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') );

		$attribs = json_decode($item->attribs);
		$article_format = (isset($attribs->helix_ultimate_article_format) && $attribs->helix_ultimate_article_format) ? $attribs->helix_ultimate_article_format : 'standard';
	?>
		<li class="list-group-item">
			<div class="item-image">
				<?php if($article_format == 'gallery') : ?>
					<?php echo LayoutHelper::render('joomla.content.blog.gallery', array('attribs' => $attribs, 'id'=>$item->id)); ?>
				<?php elseif($article_format == 'video') : ?>
					<?php echo LayoutHelper::render('joomla.content.blog.video', array('attribs' => $attribs)); ?>
				<?php elseif($article_format == 'audio') : ?>
					<?php echo LayoutHelper::render('joomla.content.blog.audio', array('attribs' => $attribs)); ?>
				<?php else: ?>
					<?php echo LayoutHelper::render('joomla.content.intro_image', array('item' => $item, 'type' => 'link') ); ?>
				<?php endif; ?>
			</div>

			<div class="item-info">
				<a href="<?php echo Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
				<?php echo $item->title; ?></a>

				<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
					<?php echo LayoutHelper::render('joomla.content.info_block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
