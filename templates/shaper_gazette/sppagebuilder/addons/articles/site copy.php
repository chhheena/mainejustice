<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted access');

class SppagebuilderAddonArticles extends SppagebuilderAddons{

	public function render(){
		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : 'panel-default';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		// Addon options
		$scroller	= (isset($this->addon->settings->scroller) && $this->addon->settings->scroller) ? $this->addon->settings->scroller : 0;
		$intro_scroller	= (isset($this->addon->settings->intro_scroller) && $this->addon->settings->intro_scroller) ? $this->addon->settings->intro_scroller : 0;
		$article_height	= (isset($this->addon->settings->article_height) && $this->addon->settings->article_height) ? $this->addon->settings->article_height : 'auto';
		$article_intros_height	= (isset($this->addon->settings->article_intros_height) && $this->addon->settings->article_intros_height) ? $this->addon->settings->article_intros_height : 'auto';
		$article_layout 	= (isset($this->addon->settings->article_layouts) && $this->addon->settings->article_layouts) ? $this->addon->settings->article_layouts : 'default';
		$leading_columns	= (isset($this->addon->settings->leading_columns) && $this->addon->settings->leading_columns) ? $this->addon->settings->leading_columns : 1;
		$intro_columns	= (isset($this->addon->settings->intro_columns) && $this->addon->settings->intro_columns) ? $this->addon->settings->intro_columns : 0;
		$border_style	= (isset($this->addon->settings->border_style) && $this->addon->settings->border_style) ? $this->addon->settings->border_style : 0;
		$resource 		= (isset($this->addon->settings->resource) && $this->addon->settings->resource) ? $this->addon->settings->resource : 'article';
		$catid 			= (isset($this->addon->settings->catid) && $this->addon->settings->catid) ? $this->addon->settings->catid : 0;
		$tagids 		= (isset($this->addon->settings->tagids) && $this->addon->settings->tagids) ? $this->addon->settings->tagids : array();
		$k2catid 		= (isset($this->addon->settings->k2catid) && $this->addon->settings->k2catid) ? $this->addon->settings->k2catid : 0;
		$include_subcat = (isset($this->addon->settings->include_subcat)) ? $this->addon->settings->include_subcat : 1;
		$post_type 		= (isset($this->addon->settings->post_type) && $this->addon->settings->post_type) ? $this->addon->settings->post_type : '';
		$ordering 		= (isset($this->addon->settings->ordering) && $this->addon->settings->ordering) ? $this->addon->settings->ordering : 'latest';
		$limit 			= (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 3;
		$columns 		= (isset($this->addon->settings->columns) && $this->addon->settings->columns) ? $this->addon->settings->columns : 3;
		$show_intro 	= (isset($this->addon->settings->show_intro)) ? $this->addon->settings->show_intro : 1;
		$intro_limit 	= (isset($this->addon->settings->intro_limit) && $this->addon->settings->intro_limit) ? $this->addon->settings->intro_limit : 200;
		$hide_thumbnail = (isset($this->addon->settings->hide_thumbnail)) ? $this->addon->settings->hide_thumbnail : 0;
		$show_author 	= (isset($this->addon->settings->show_author)) ? $this->addon->settings->show_author : 1;
		$show_category 	= (isset($this->addon->settings->show_category)) ? $this->addon->settings->show_category : 1;
		$show_date 		= (isset($this->addon->settings->show_date)) ? $this->addon->settings->show_date : 1;
		$show_readmore 	= (isset($this->addon->settings->show_readmore)) ? $this->addon->settings->show_readmore : 1;
		$readmore_text 	= (isset($this->addon->settings->readmore_text) && $this->addon->settings->readmore_text) ? $this->addon->settings->readmore_text : 'Read More';
		$link_articles 	= (isset($this->addon->settings->link_articles)) ? $this->addon->settings->link_articles : 0;
		$link_catid 	= (isset($this->addon->settings->link_catid)) ? $this->addon->settings->link_catid : 0;
		$link_k2catid 	= (isset($this->addon->settings->link_k2catid)) ? $this->addon->settings->link_k2catid : 0;

		$all_articles_btn_text   = (isset($this->addon->settings->all_articles_btn_text) && $this->addon->settings->all_articles_btn_text) ? $this->addon->settings->all_articles_btn_text : 'See all posts';
		$all_articles_btn_class  = (isset($this->addon->settings->all_articles_btn_size) && $this->addon->settings->all_articles_btn_size) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_size : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_type : ' sppb-btn-default';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_shape) && $this->addon->settings->all_articles_btn_shape) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_shape: ' sppb-btn-rounded';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->all_articles_btn_appearance : '';
		$all_articles_btn_class .= (isset($this->addon->settings->all_articles_btn_block) && $this->addon->settings->all_articles_btn_block) ? ' ' . $this->addon->settings->all_articles_btn_block : '';
		$all_articles_btn_icon   = (isset($this->addon->settings->all_articles_btn_icon) && $this->addon->settings->all_articles_btn_icon) ? $this->addon->settings->all_articles_btn_icon : '';
		$all_articles_btn_icon_position = (isset($this->addon->settings->all_articles_btn_icon_position) && $this->addon->settings->all_articles_btn_icon_position) ? $this->addon->settings->all_articles_btn_icon_position: 'left';

		$output   = '';
		//include k2 helper
		$k2helper 		= JPATH_ROOT . '/components/com_sppagebuilder/helpers/k2.php';
		$article_helper = JPATH_ROOT . '/components/com_sppagebuilder/helpers/articles.php';
		$isk2installed  = self::isComponentInstalled('com_k2');

		if ($resource == 'k2') {
			if ($isk2installed == 0) {
				$output .= '<p class="alert alert-danger">' . JText::_('COM_SPPAGEBUILDER_ADDON_ARTICLE_ERORR_K2_NOTINSTALLED') . '</p>';
				return $output;
			} elseif(!file_exists($k2helper)) {
				$output .= '<p class="alert alert-danger">' . JText::_('COM_SPPAGEBUILDER_ADDON_K2_HELPER_FILE_MISSING') . '</p>';
				return $output;
			} else {
				require_once $k2helper;
			}
			$items = SppagebuilderHelperK2::getItems($limit, $ordering, $k2catid, $include_subcat);
		} else {
			require_once $article_helper;
			$items = SppagebuilderHelperArticles::getArticles($limit, $ordering, $catid, $include_subcat, $post_type, $tagids);
		}

		if($border_style){
			$border_style = "article-border";
		}
		if (!count($items)) {
			$output .= '<p class="alert alert-warning">' . JText::_('COM_SPPAGEBUILDER_NO_ITEMS_FOUND') . '</p>';
			return $output;
		}

		if(count((array) $items)) {
			$output  .= '<div class="sppb-addon sppb-addon-articles ' . $class . ' ' . $article_layout . ' ' . $border_style . '">';

			if($title) {
				$output .= '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>';
			}

			$nano_scroller = '';
			$nano_scroller_wrapper = '';
			if($scroller){
				$nano_scroller = 'nano';
				$nano_scroller_wrapper = 'nano-content';
			}

			$nano_intro_scroller = '';
			$nano_intro_scroller_wrapper = '';
			if($intro_scroller){
				$nano_intro_scroller = 'nano';
				$nano_intro_scroller_wrapper = 'nano-content';
			}
			$output .= '<div class="sppb-addon-content '. $nano_scroller .'" style="height:'. $article_height .'px;">';
			$output	.= '<div class="sppb-row '. $nano_scroller_wrapper .'">';

			$total_item = count($items) - 1;
			foreach ($items as $key => $item) {
				//bg image
				$bgimage = '';
				if($article_layout == "classic"){
						$bgimage = 'style="background-image: url('. $item->image_thumbnail .');"';
				}
				
				//Creative Layout
				if(($article_layout == "creative" || $article_layout == "simple") && $key == 0) {
					$output .= '<div class="col-sm-12 leading-item">';	
				}
				if($article_layout == "creative" && $key == 1) {
					$output .= '<div class="sppb-col-sm-'. $leading_columns .' subleading-item">';	
				}
				if($article_layout == "creative" && $key == 2) {
					$output .= '<div class="sppb-col-sm-'. $intro_columns . ' ' . $nano_intro_scroller .' intro-items" style="height:'. $article_intros_height .'px;">';	
					$output .= '<div class="'. $nano_intro_scroller_wrapper .'">';	
				}

				//Standard Layout
				if($article_layout == "standard" && $key == 0) {
					$output .= '<div class="sppb-col-sm-'. $leading_columns .' leading-item">';	
				}
				if($article_layout == "standard" && $key == 1) {
					$output .= '<div class="sppb-col-sm-'. $intro_columns . ' ' . $nano_intro_scroller .' intro-items" style="height:'. $article_intros_height .'px;">';	
					$output .= '<div class="'. $nano_intro_scroller_wrapper .'">';		
				}

				//Essential Layout
				if($article_layout == "essential" && $key == 0) {
					$output .= '<div class="sppb-col-md-'. $leading_columns .' leading-item">';	
				}
				if($article_layout == "essential" && $key == 1) {
					$output .= '<div class="sppb-col-md-'. $intro_columns . ' ' . $nano_intro_scroller .' intro-items" style="height:'. $article_intros_height .'px;">';	
					$output .= '<div class="'. $nano_intro_scroller_wrapper .'">';	
				}

				if($article_layout != "creative" && $article_layout != "standard" && $article_layout != "simple" && $article_layout != "essential") {
				$output .= '<div class="sppb-col-sm-'. $leading_columns .'">';
				}
				$video_type = '';
				if($item->post_format == 'video'){
					$video_type = 'video-format';
				}
				$output .= '<div class="sppb-addon-article '. $video_type .'" '. $bgimage . '>';
				
				if($article_layout != "classic" && !$hide_thumbnail) {
					$image = '';
					if ($resource == 'k2') {
						if(isset($item->image_medium) && $item->image_medium){
							$image = $item->image_medium;
						} elseif(isset($item->image_large) && $item->image_large){
							$image = $item->image_medium;
						}
					} elseif($article_layout == "horizontal" || ($article_layout == "creative" && $key > 1)) {
						$image = $item->image_small;
					} elseif($item->post_format == 'video' && $key > 0) {
						$image = $item->image_medium;
					} elseif($item->post_format == 'video') {
						$image = $item->image_large;
					} elseif($article_layout == "essential" && $key > 0) {
						$image = $item->image_small;
					} elseif($article_layout == "essential") {
						$image = $item->image_medium;
					} else {
						$image = $item->image_medium;
					}
					$output .= '<div class="sppb-addon-article-info-left">';
					if($resource != 'k2' && $item->post_format=='gallery') {
						if(count((array) $item->imagegallery->images)) {
							$output .= '<div class="sppb-carousel sppb-slide" data-sppb-ride="sppb-carousel">';
							$output .= '<div class="sppb-carousel-inner">';
							foreach ($item->imagegallery->images as $gallery_item) {
								if (isset($gallery_item['thumbnail']) && $gallery_item['thumbnail']) {
									$output .= '<div class="sppb-item">';
									$output .= '<img class="lazyestload" data-src="'. $gallery_item['thumbnail'] .'" src="'. $gallery_item['thumbnail'] .'" alt="">';
									$output .= '</div>';
								} elseif (isset($gallery_item['full']) && $gallery_item['full']) {
									$output .= '<div class="sppb-item">';
									$output .= '<img class="lazyestload" data-src="'. $gallery_item['full'] .'" src="'. $gallery_item['full'] .'" alt="">';
									$output .= '</div>';
								}
							}
							$output	.= '</div>';

							$output	.= '<a class="left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
							$output	.= '<a class="right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-angle-right"></i></a>';

							$output .= '</div>';

						} elseif ( isset($item->image_thumbnail) && $item->image_thumbnail ) {
							$output .= '<a href="'. $item->link .'" itemprop="url"><img class="sppb-img-responsive" src="'. $item->image_thumbnail .'" alt="'. $item->title .'" itemprop="thumbnailUrl"></a>';
						}
					} else {
						$play_icon = ($item->post_format == 'video')? '<span class="play-icon"></span>': "";
						if(isset($image) && $image && $article_layout != "standard" && $article_layout != "simple") {
							$output .= '<a class="article-img-wrap" href="'. $item->link .'" itemprop="url">'. $play_icon .'<img class="sppb-img-responsive lazyestload" src="'. $image .'" alt="'. $item->title .'" itemprop="thumbnailUrl" data-src="' .$image. '"></a>';
						}
						if(isset($image) && $image && $article_layout == "standard" && $key == 0) {
							$output .= '<a class="article-img-wrap" href="'. $item->link .'" itemprop="url">'. $play_icon .'<img class="sppb-img-responsive lazyestload" src="'. $image .'" alt="'. $item->title .'" itemprop="thumbnailUrl" data-src="' .$image. '"></a>';
						}
						if(isset($image) && $image && $article_layout == "simple" && $key == 0) {
							$output .= '<a class="article-img-wrap" href="'. $item->link .'" itemprop="url">'. $play_icon .'<img class="sppb-img-responsive lazyestload" src="'. $image .'" alt="'. $item->title .'" itemprop="thumbnailUrl" data-src="' .$image. '"></a>';
						}
					}
					$output .= '</div>';
				}

				$output .= '<div class="sppb-addon-article-info-wrapper">';
				if($article_layout != "classic"){
					$output .= '<h3 class="sppb-addon-article-title"><a href="'. $item->link .'" itemprop="url">' . $item->title . '</a></h3>';
				}
				if($article_layout != "horizontal"){
					if($show_author || $show_category || $show_date) {
						$output .= '<div class="sppb-article-meta">';
	
						if($show_date) {
							$output .= '<span class="sppb-meta-date" itemprop="datePublished">' . Jhtml::_('date', $item->publish_up, 'DATE_FORMAT_LC3') . '</span>';
						}
	
						if($show_category) {
							if ($resource == 'k2') {
									$item->catUrl = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->category_alias))));
								} else {
									$item->catUrl = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug));
								}
							$output .= '<span class="sppb-meta-category"><a href="'. $item->catUrl .'" itemprop="genre">' . $item->category . '</a></span>';
						}
	
						if($show_author) {
							$author = ( $item->created_by_alias ?  $item->created_by_alias :  $item->username);
							$output .= '<span class="sppb-meta-author" itemprop="name">' . JTEXT::_('CREATED_BY') . ' ' . $author . '</span>';
						}
	
						$output .= '</div>';
					}
				}

				if($article_layout == "classic"){
					$output .= '<h3 class="sppb-addon-article-title"><a href="'. $item->link .'" itemprop="url">' . $item->title . '</a></h3>';
				}
				if($item->post_format == 'video' && $key == 0 && $show_intro == false) {
					$output .= '<div class="sppb-article-introtext gazette-custom-font">'. substr($item->introtext, 0, $intro_limit) .'</div>';
				}

				if($show_intro) {
					$output .= '<div class="sppb-article-introtext gazette-custom-font">'. substr($item->introtext, 0, $intro_limit) .'</div>';
				}
				if($article_layout == "horizontal"){
					if($show_author || $show_category || $show_date) {
						$output .= '<div class="sppb-article-meta">';

						if($show_category) {
							if ($resource == 'k2') {
									$item->catUrl = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->category_alias))));
								} else {
									$item->catUrl = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug));
								}
							$output .= '<span class="sppb-meta-category"><a href="'. $item->catUrl .'" itemprop="genre">' . $item->category . '</a></span>';
						}
						if($show_author) {
							$author = ( $item->created_by_alias ?  $item->created_by_alias :  $item->username);
							$output .= '<span class="sppb-meta-author" itemprop="name">'. JText::_('CREATED_BY') . ' ' .'<span class="author-name">' . $author .'</span></span>';
						}
						if($show_date) {
							$output .= '<span class="sppb-meta-date" itemprop="datePublished">' . Jhtml::_('date', $item->publish_up, 'DATE_FORMAT_LC3') . '</span>';
						}

						$output .= '</div>';
					}
				}
				if($show_readmore) {
					$output .= '<a class="sppb-readmore" href="'. $item->link .'" itemprop="url">'. $readmore_text .'</a>';
				}
				
				$output .= '</div>';//sppb-addon-article-info-wrapper
				$output .= '</div>'; //.sppb-addon-article

				if($article_layout != "creative" && $article_layout != "standard" && $article_layout != "simple"  && $article_layout != "essential") {
					$output .= '</div>'; //sppb-col-sm-dynamic
				}
				if($article_layout == "creative" && $key == 0) {
					$output .= '</div>';	//leading item 1(cretive layout)
				}
				if($article_layout == "creative" && $key == 1) {
					$output .= '</div>';	//leading item 2(cretive layout)
				}
				if($article_layout == "creative" && $key == $total_item) {
					$output .= '</div>';	
					$output .= '</div>';	//intro items (cretive layout)
				}
				if($article_layout == "standard" && $key == 0) {
					$output .= '</div>';	 //leading item 1 (standard layout)
				}
				if(($article_layout == "standard" || $article_layout == "simple") && $key == $total_item) {
					$output .= '</div>';
					$output .= '</div>';	 //intro items 1 (standard layout)
				}
				if($article_layout == "essential" && $key == 0) {
					$output .= '</div>';	//leading item (essential layout)
				}
				if($article_layout == "essential" && $key == $total_item) {
					// See all link
					if($link_articles) {

						if($all_articles_btn_icon_position == 'left') {
							$all_articles_btn_text = ($all_articles_btn_icon) ? '<i class="fa ' . $all_articles_btn_icon . '"></i> ' . $all_articles_btn_text : $all_articles_btn_text;
						} else {
							$all_articles_btn_text = ($all_articles_btn_icon) ? $all_articles_btn_text . ' <i class="fa ' . $all_articles_btn_icon . '"></i>' : $all_articles_btn_text;
						}

						if ($resource == 'k2') {
							if(!empty($link_k2catid)){
								$output .= '<div class="sppb-article-readmore-wrapper">';
								$output  .= '<a href="' . urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($link_k2catid))) . '" " id="btn-' . $this->addon->id . '" class="sppb-btn' . $all_articles_btn_class . '">' . $all_articles_btn_text . '</a>';
								$output .= '</div>';
							}
						} else{
							if(!empty($link_catid)){
								$output .= '<div class="sppb-article-readmore-wrapper">';
								$output  .= '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($link_catid)) . '" id="btn-' . $this->addon->id . '" class="sppb-btn' . $all_articles_btn_class . '">' . $all_articles_btn_text . '</a>';
								$output .= '</div>';
							}
						}

					}
					$output .= '</div>';	 //intro items (essential layout)
				}
			}

			$output  .= '</div>';

			// See all link
			if($link_articles && $article_layout != "essential") {

				if($all_articles_btn_icon_position == 'left') {
					$all_articles_btn_text = ($all_articles_btn_icon) ? '<i class="fa ' . $all_articles_btn_icon . '"></i> ' . $all_articles_btn_text : $all_articles_btn_text;
				} else {
					$all_articles_btn_text = ($all_articles_btn_icon) ? $all_articles_btn_text . ' <i class="fa ' . $all_articles_btn_icon . '"></i>' : $all_articles_btn_text;
				}

				if ($resource == 'k2') {
					if(!empty($link_k2catid)){
						$output .= '<div class="sppb-article-readmore-wrapper">';
						$output  .= '<a href="' . urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($link_k2catid))) . '" " id="btn-' . $this->addon->id . '" class="sppb-btn' . $all_articles_btn_class . '">' . $all_articles_btn_text . '</a>';
						$output .= '</div>';
					}
				} else{
					if(!empty($link_catid)){
						$output .= '<div class="sppb-article-readmore-wrapper">';
						$output  .= '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($link_catid)) . '" id="btn-' . $this->addon->id . '" class="sppb-btn' . $all_articles_btn_class . '">' . $all_articles_btn_text . '</a>';
						$output .= '</div>';
					}
				}

			}

			$output  .= '</div>';
			$output  .= '</div>';
		}

		return $output;
	}

	public function scripts() {
		$app = JFactory::getApplication();
		$base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/js/';
		return array($base_path . 'lazyestload.js', $base_path . 'jquery.nanoscroller.min.js');
	}
		
	public function css() {
		$addon_id = '#sppb-addon-' .$this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$options = new stdClass;
		$options->button_type = (isset($this->addon->settings->all_articles_btn_type) && $this->addon->settings->all_articles_btn_type) ? $this->addon->settings->all_articles_btn_type : '';
		$options->button_appearance = (isset($this->addon->settings->all_articles_btn_appearance) && $this->addon->settings->all_articles_btn_appearance) ? $this->addon->settings->all_articles_btn_appearance : '';
		$options->button_color = (isset($this->addon->settings->all_articles_btn_color) && $this->addon->settings->all_articles_btn_color) ? $this->addon->settings->all_articles_btn_color : '';
		$options->button_color_hover = (isset($this->addon->settings->all_articles_btn_color_hover) && $this->addon->settings->all_articles_btn_color_hover) ? $this->addon->settings->all_articles_btn_color_hover : '';
		$options->button_background_color = (isset($this->addon->settings->all_articles_btn_background_color) && $this->addon->settings->all_articles_btn_background_color) ? $this->addon->settings->all_articles_btn_background_color : '';
		$options->button_background_color_hover = (isset($this->addon->settings->all_articles_btn_background_color_hover) && $this->addon->settings->all_articles_btn_background_color_hover) ? $this->addon->settings->all_articles_btn_background_color_hover : '';
		$options->button_fontstyle = (isset($this->addon->settings->all_articles_btn_fontstyle) && $this->addon->settings->all_articles_btn_fontstyle) ? $this->addon->settings->all_articles_btn_fontstyle : '';
		$options->button_letterspace = (isset($this->addon->settings->all_articles_btn_letterspace) && $this->addon->settings->all_articles_btn_letterspace) ? $this->addon->settings->all_articles_btn_letterspace : '';

		return $css_path->render(array('addon_id' => $addon_id, 'options' => $options, 'id' => 'btn-' . $this->addon->id));
	}

	public function js() {
		return 'jQuery( document ).ready(function( $ ) {
			if($(".nano").length>0){
        $(".nano").nanoScroller();
			}
		});';
	}

	static function isComponentInstalled($component_name){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( 'a.enabled' );
		$query->from($db->quoteName('#__extensions', 'a'));
		$query->where($db->quoteName('a.name')." = ".$db->quote($component_name));
		$db->setQuery($query);
		$is_enabled = $db->loadResult();
		return $is_enabled;
	}

}