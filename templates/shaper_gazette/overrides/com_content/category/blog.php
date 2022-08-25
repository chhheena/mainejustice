<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$app = Factory::getApplication();

$this->category->text = $this->category->description;
$app->triggerEvent('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $app->triggerEvent('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

$plugin = JPluginHelper::getPlugin('content', 'jw_allvideos');

// Check if plugin is enabled
if ($plugin) {
    // Get plugin params
    $pluginParams = new JRegistry($plugin->params);

    $param1 = $pluginParams->get('vfolder');
    // echo'<pre>';
    // print_r($param1);
}
?>

<div class="blog<?php echo $this->pageclass_sfx; ?>">
<?php
if (is_dir($param1)) {
    if ($dh = opendir($param1)) {
        while (($file = readdir($dh)) !== false) {
            // echo "filename: .".$file."<br />";
            if (preg_match('/^.*\.(mp4|mov)$/i', $file)) {
                //echo $file; ?>
                <div class="article-list articles-leading<?php echo $this->params->get('blog_class_leading'); ?>">
                <div class="avPlayerWrapper avVideo">
                    <div class="avPlayerContainer text-center">
                        <div id="" class="avPlayerBlock"><video class="avPlayer" style="width:600px;height:450px;" src="<?php echo JURI::base() .
                            $param1; ?>/<?php echo $file?>" preload="metadata" controls="" controlslist=""></video>
                        </div>
                        <div class="avDownloadLink">
                            <a target="_blank" href="<?php echo JURI::base(); ?>/<?php echo $param1; ?>/<?php echo $file?>" download="">Download</a>
                        </div>
                    </div>
                </div>
                </div>
                
                <?php
            }
        }
        closedir($dh);
    }
}
?>
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading')); ?>
			<?php if ($this->params->get('show_category_title')) : ?>
				<span class="subheading-category"><?php echo $this->category->title; ?></span>
			<?php endif; ?>
		</h2>
	<?php endif; ?>
	
	<?php echo $afterDisplayTitle; ?>

	<?php if ($this->params->get('show_cat_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
		<?php $this->category->tagLayout = new FileLayout('joomla.content.tags'); ?>
		<?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
	<?php endif; ?>

	<?php if ($beforeDisplayContent || $afterDisplayContent || $this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
		<div class="category-desc clearfix">
			<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
				<img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="<?php echo htmlspecialchars($this->category->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>">
			<?php endif; ?>
			<?php echo $beforeDisplayContent; ?>
			<?php if ($this->params->get('show_description') && $this->category->description) : ?>
				<?php echo HTMLHelper::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
			<?php endif; ?>
			<?php echo $afterDisplayContent; ?>
		</div>
	<?php endif; ?>

	<?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
		<?php if ($this->params->get('show_no_articles', 1)) : ?>
			<p><?php echo Text::_('COM_CONTENT_NO_ARTICLES'); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php $leadingcount = 0; ?>
	<?php if (!empty($this->lead_items)) : ?>
		<div class="article-list articles-leading<?php echo $this->params->get('blog_class_leading'); ?>">
			<?php foreach ($this->lead_items as &$item) : ?>
				<div class="article<?php echo $item->state == 0 ? ' system-unpublished' : null; ?>"
					itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
						<?php
						$this->item = & $item;
						$this->item->leading = true;
						echo $this->loadTemplate('item');
						?>
				</div>
				<?php $leadingcount++; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php
	$introcount = count($this->intro_items);
	$counter = 0;
	?>

	<?php if (!empty($this->intro_items)) : ?>
		<?php $blogClass = $this->params->get('blog_class', ''); ?>
		<?php if ((int) $this->params->get('num_columns') > 1) : ?>
			<?php $blogClass .= 'cols-' . (int) $this->params->get('num_columns'); ?>	
		<?php endif; ?>
		<div class="article-list">
			<div class="row row-<?php echo $counter + 1; ?> <?php echo $blogClass; ?>">
			<?php foreach ($this->intro_items as $key => &$item) : ?>
				<div class="col-lg-<?php echo round(12 / $this->params->get('num_columns')); ?>">
					<div class="article"
						itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
						<?php
						$this->item = & $item;
						echo $this->loadTemplate('item');
						?>
					</div>
					<?php $counter++; ?>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->link_items)) : ?>
		<div class="articles-more mb-4">
			<?php echo $this->loadTemplate('links'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->maxLevel != 0 && !empty($this->children[$this->category->id])) : ?>
		<div class="cat-children mb-4">
			<?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
				<h3> <?php echo Text::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
			<?php endif; ?>
			<?php echo $this->loadTemplate('children'); ?>
		</div>
	<?php endif; ?>

	<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
		<nav class="pagination-wrapper d-lg-flex justify-content-between w-100">
			<?php echo $this->pagination->getPagesLinks(); ?>
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
</div>
