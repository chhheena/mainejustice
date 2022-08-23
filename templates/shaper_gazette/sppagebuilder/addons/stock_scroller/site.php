<?php
/**
 * @package SP Page Builder
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2020 JoomShaper
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonStock_scroller extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? ' ' . $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$limit = (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 5;
		$time_span = (isset($this->addon->settings->time_span) && $this->addon->settings->time_span) ? $this->addon->settings->time_span : '1d';
		
		$change_type = (isset($this->addon->settings->change_type) && $this->addon->settings->change_type) ? $this->addon->settings->change_type : 'percentage';
		$list_type 	= (isset($this->addon->settings->list_type) && $this->addon->settings->list_type) ? $this->addon->settings->list_type : 'quote';

		// Get stock data
		$items = $this->getItems();

		// echo '<xmp>';
		// print_r($items);
		// echo '</xmp>';
		// die();

		//Output
		$output  = '<div class="sppb-addon sppb-addon-stock-scroller ' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
			$output .= '<div class="sppb-addon-content">';
				$output .= '<div class="stock-scroller-box">';
					$output .= '<div class="stock-scroller-wrap marquee">';
						$output .= '<ul class="stock-scroller-panel">';
							foreach ($items as $key => $item) {
								$price_status = "";
								if(isset($item->chart) && count($item->chart) && $item->chart && $list_type == 'chart') {
									foreach ($item->chart as $key => $chart_item ) {

										$key ++;
										$raw_date = $chart_item->date;  
										$strto_date = strtotime($raw_date);
										$date 		= date('Y/m/d', $strto_date);
										$chart_item->open = (isset($chart_item->marketOpen)) ? $chart_item->marketOpen : $chart_item->open;
										$chart_item->close = (isset($chart_item->marketClose)) ? $chart_item->marketClose : $chart_item->close;
										if( $chart_item->open > $chart_item->close ) {
											$chart_item->price_status = 'minus';
											$chart_item->price_icon = 'fa-long-arrow-down';
											$price_status = "down";
										} else {
											$chart_item->price_status = 'plus';
											$chart_item->price_icon = 'fa-long-arrow-up';
											$price_status = "up";
										}

										$output .= '<li class="stock-scroller-item '. $chart_item->price_status .'">';
											$output .= '<div class="sppb-addon-stock-item-wrap">';
												$output .= '<div class="sppb-addon-stock-info-wrap">';
													$output .= '<span>'. $item->quote->companyName  .' ('. $date . ' - '. $chart_item->label .') </span>';
													$output .= '<div class="sppb-addon-stock-price-wrap '. $price_status .'">';
														$output .= '<span>'. round($chart_item->close, 3)  .'</span>';
														$output .= '<span class="price-icon"><i class="fa $item->price_icon '. $chart_item->price_icon .'" aria-hidden="true"></i></span>';
													$output .= '</div>';
												$output .= '</div>';
											$output .= '</div>';
										$output .= '</li>';

										if($key == $limit) {
											break;
										}
									}
								} else {
									if($change_type == 'price') {
										$item->price_status 	= ($item->quote->open > $item->quote->close) ? 'minus' : 'plus';
										$item->price_icon 		= ($item->quote->open > $item->quote->close) ? 'fa-long-arrow-down' : 'fa-long-arrow-up';
										$price_status 				= ($item->quote->open > $item->quote->close) ? 'down' : 'up';
									} else {
										$item->price_status 	= ( strpos($item->quote->changePercent, '-') !== false ) ? 'minus' : 'plus';
										$item->price_icon 		= ( strpos($item->quote->changePercent, '-') !== false ) ? 'fa-long-arrow-down' : 'fa-long-arrow-up';
										$price_status 				= ( strpos($item->quote->changePercent, '-') !== false ) ? 'down' : 'up';
									}
									$output .= '<li class="stock-scroller-item '. $item->price_status .'">';
										$output .= '<div class="sppb-addon-stock-item-wrap">';
											$output .= '<div class="sppb-addon-stock-info-wrap">';
												$output .= '<span>'. $item->quote->companyName  .' </span>';
												$output .= '<div class="sppb-addon-stock-price-wrap '. $price_status .'">';
													if($change_type == 'price') {
														$output .= '<span>$'. round($item->quote->latestPrice, 3)  .'</span>';
													} else {
														$output .= '<span>'. $item->quote->changePercent  .'%</span>';
													}
													$output .= '<span class="price-icon"><i class="fa $item->price_icon '. $item->price_icon .'" aria-hidden="true"></i></span>';
												$output .= '</div>';
											$output .= '</div>';
										$output .= '</div>';
									$output .= '</li>';
								}
							}
						$output .= '</ul>';
					$output .= '</div>';
				$output .= '</div>';
	
			$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function scripts() {
		$app = JFactory::getApplication();
		$base_path = JURI::base() . '/templates/' . $app->getTemplate() . '/js/';
		
        return array($base_path . 'jquery.marquee.min.js');
	}

	private function getItems() {

		//get data from addons
		$change_type = (isset($this->addon->settings->change_type) && $this->addon->settings->change_type) ? $this->addon->settings->change_type : 'percentage';
		$list_type 	= (isset($this->addon->settings->list_type) && $this->addon->settings->list_type) ? $this->addon->settings->list_type : 'quote';
		$symbols 	= (isset($this->addon->settings->symbols) && $this->addon->settings->symbols) ? $this->addon->settings->symbols : 'AAPL,FB,TSLA,GOOG,GOOGL,MSFT,INTC,AMD,AMZN,BRK-A,BRK-B,BABA,JNJ,JPM,XOM,BAC,WMT,V,T,CHL,VZ,ORCL';
		$limit 		= (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 5;
		$time_span 	= (isset($this->addon->settings->time_span) && $this->addon->settings->time_span) ? $this->addon->settings->time_span : '1d';
		$api_key 	= (isset($this->addon->settings->api_key) && $this->addon->settings->api_key) ? $this->addon->settings->api_key : 'sk_89986f24886a43e79e5931857f21a5b8';

		
		jimport( 'joomla.filesystem.folder' );
		$cache_path = JPATH_CACHE . '/com_sppagebuilder/addons/addon-' . $this->addon->id;
		$cache_file = $cache_path . '/stock-scroller.json';
		
		if(!file_exists($cache_path)) {
			JFolder::create($cache_path, 0755);
		}

		if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 30 ))) {
			$items = file_get_contents($cache_file);
		} else {
			if($list_type =='quote') {
				//$api = 'https://api.iextrading.com/1.0/stock/market/batch?symbols='. $symbols .'&types=quote&range='.$time_span;
				$api = 'https://cloud.iexapis.com/stable/stock/market/batch?symbols='. $symbols .'&types=quote&range='.$time_span .'&format=json&token='. $api_key;
			} else {
				//$api = 'https://api.iextrading.com/1.0/stock/market/batch?symbols='. $symbols .'&types=quote,chart&range='.$time_span.'&last='. $limit;
				$api = 'https://cloud.iexapis.com/stable/stock/market/batch?symbols='. $symbols .'&types=quote,chart&range='.$time_span.'&last='. $limit . '&format=json&token='. $api_key;
			}
			
			if( ini_get('allow_url_fopen') ) {
				$items = file_get_contents($api);
				file_put_contents($cache_file, $items, LOCK_EX);
			} else {
				$items = $this->curl($api);
			}
		}

		$json = json_decode($items);
		
		if(isset($json)) {
			return $json;
		}

		return array();
	}

	function curl($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    $data = curl_exec($ch);
	    curl_close($ch);
	    return $data;
	}

}
