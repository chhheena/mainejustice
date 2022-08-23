<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$doc = JFactory::getDocument();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$tpl_path    = JFactory::getApplication()->getTemplate();

$doc = JFactory::getDocument();
$tpl_params = JFactory::getApplication()->getTemplate(true)->params;

JHtml::_('behavior.caption');
?>
<div class="blog<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="http://schema.org/Blog">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
        <h2> <?php echo $this->escape($this->params->get('page_subheading')); ?>
            <?php if ($this->params->get('show_category_title')) : ?>
                <span class="subheading-category"><?php echo $this->category->title; ?></span>
            <?php endif; ?>
        </h2>
    <?php endif; ?>

    <?php if ($this->params->get('show_cat_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
        <?php $this->category->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
        <?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
    <?php endif; ?>

    <?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
        <div class="category-desc clearfix">
            <?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
                <img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="<?php echo htmlspecialchars($this->category->getParams()->get('image_alt')); ?>"/>
            <?php endif; ?>
            <?php if ($this->params->get('show_description') && $this->category->description) : ?>
                <?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
        <?php if ($this->params->get('show_no_articles', 1)) : ?>
            <p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    $totalItemNo = 0;
    $leadingcount = 0;
    ?>

    <?php if (!empty($this->lead_items)) : ?>
        <div class="items-leading clearfix">
            <div id="art-leading-carousel" class="carousel slide" data-ride="art-carousel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="carousel-inner" role="listbox">
                            <?php
                            foreach ($this->lead_items as $key => &$item) :
                                $key ++;
                                ?>
                                <article class="item  <?php echo ($key == 1) ? 'active' : '' ?> leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?><?php echo $item->featured ? ' item-featured' : ''; ?>"
                                         itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
                                             <?php
                                             $this->item = & $item;
                                             $this->item->key = $key;
                                             $this->item->item_type = "leading";
                                             echo $this->loadTemplate('item');
                                             ?>
                                </article>
                                <?php $leadingcount++; ?>
                            <?php endforeach; ?>
                        </div><!-- / .carousel-inner -->

                        <!-- Controls -->
                        <!-- <div class="carousel-controls">
                            <a class="left art-control" data-target="#art-leading-carousel" role="button" data-slide="prev">
                                <i class="fa fa-angle-left"></i>
                            </a>
                            <a class="right art-control" data-target="#art-leading-carousel" role="button" data-slide="next">
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </div> -->
                    </div> <!-- //.col-md-8 -->

                    <div class="col-md-4">
                        <!-- Indicators -->
                        <ol class="carousel-indicators">
                            <?php foreach ($this->lead_items as $key => &$item) : ?>
                                <li data-target="#art-leading-carousel" data-slide-to="<?php echo $key ?>" class="<?php echo ($key == 0) ? 'active' : '' ?>">
                                    <article>
                                        <div class="date"><?php echo Jhtml::_('date', $item->created, 'M d, H:i a'); ?></div>
                                        <h4 class="title"><?php echo $item->title ?></h4>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div> <!-- //.col-md-4 -->
                </div> <!-- //.row -->


            </div><!-- / . art-leading-carousel -->

        </div><!-- end items-leading -->
    <?php endif; ?>

    <?php
    $introcount = (count($this->intro_items));
    $counter = 0;
    ?>

    <?php if (!empty($this->intro_items)) : ?>
        <?php foreach ($this->intro_items as $key => &$item) : ?>
            <?php $rowcount = ((int) $key % (int) $this->columns) + 1; ?>
            <?php if ($rowcount == 1) : ?>
                <?php $row = $counter / $this->columns; ?>
                <div class="items-row <?php echo 'row-' . $row; ?> row clearfix">
                <?php endif; ?>
                <div class="col-sm-<?php echo round((12 / $this->columns)); ?>">
                    <article class="item column-<?php echo $rowcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?><?php echo $item->featured ? ' item-featured' : ''; ?>"
                             itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
                                 <?php
                                 $this->item = & $item;
                                 $this->item->item_type = "";
                                 echo $this->loadTemplate('item');
                                 ?>
                    </article>
                    <!-- end item -->
                    <?php
                    $totalItemNo++;
                    $counter++;
                    ?>
                </div><!-- end col-sm-* -->
            <?php if (($rowcount == $this->columns) or ( $counter == $introcount)) : ?>
                </div><!-- end row -->
        <?php endif; ?>

            <!-- if item has 3 then module position will be set -->
        <?php if (($totalItemNo == ( $tpl_params->get('bloglist_ad', 3) ) ) && $doc->countModules('bloglist-ad')) { ?>
                <div class="bloglist-add items-row row clearfix">
                    <div class="col-sm-12">
                        <?php
                        jimport('joomla.application.module.helper');
                        $modules = JModuleHelper::getModules('bloglist-ad');
                        $attribs['style'] = 'sp_xhtml';

                        foreach ($modules as $key => $module) {
                            echo JModuleHelper::renderModule($module, $attribs);
                        }
                        ?>
                    </div> <!-- /.col-sm- -->
                </div> <!-- /.items-row -->
            <?php } ?> <!-- // END:: key condition -->

        <?php endforeach; ?>
    <?php endif; ?>

        <?php if (!empty($this->link_items)) : ?>
        <div class="items-more">
        <?php echo $this->loadTemplate('links'); ?>
        </div>
    <?php endif; ?>

        <?php if (!empty($this->children[$this->category->id]) && $this->maxLevel != 0) : ?>
        <div class="cat-children">
            <?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
                <h3> <?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
            <?php endif; ?>
        <?php echo $this->loadTemplate('children'); ?> </div>
    <?php endif; ?>
        <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
        <div class="pagination-wrapper">
            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
            <?php endif; ?>
        <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
<?php endif; ?>
</div>