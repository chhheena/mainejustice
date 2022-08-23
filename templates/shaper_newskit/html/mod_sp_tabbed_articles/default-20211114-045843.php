<?php
/*------------------------------------------------------------------------
# mod_sp_tabbed_articles - Tabbed articles module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2015 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
defined ('_JEXEC') or die('resticted aceess');

?>
<?php if(count($categories)) { ?>
<div class="sp-vertical-tabs">
	<div class="row">
		<div class="col-sm-3">
			<ul class="sp-tab-btns">
				<?php foreach ($categories as $key=>$cat) { ?>
				<li class="<?php echo ($key==0)? 'active': ''; ?>"><a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($cat->id)); ?>"><?php echo $cat->title; ?></a></li>
				<?php } ?>
			</ul>
		</div>
		<div class="col-sm-9">
			<div class="sp-tab-content">
				<?php foreach ($categories as $key=>$category) { ?>
				<div class="sp-tab-pane <?php echo ($key==0)? 'active': ''; ?>">
					<?php $articles = modSpTabbedArticlesHelper::getArticles($limit, $ordering, $category->id); ?>
					<?php if(count($articles)) { ?>
					<div class="row">
						<?php foreach ($articles as $article) { ?>
						<div itemscope itemtype="http://schema.org/Article" class="col-sm-<?php echo round(12/$columns); ?>">
						<div class="sp-article-inner">
								<div class="sp-overlay"></div>
								<div class="sp-img-wrapper">
									<a href="<?php echo $article->link; ?>" itemprop="url">
										<img class="img-responsive" src="<?php echo $article->image_thumbnail; ?>">
									</a>
								</div>

								<div class="sp-article-info">
									<h4 class="entry-title" itemprop="name">
										<a href="<?php echo $article->link; ?>" itemprop="url">
											<?php echo $article->title; ?>
										</a>
									</h4>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php } ?>