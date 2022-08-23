<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (!class_exists ('VmConfig'))
{
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}
VmConfig::loadConfig ();

// Load the language file of com_virtuemart.
VmConfig::loadJLang('com_virtuemart',true);
if (!class_exists ('calculationHelper')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'calculationh.php');
}
if (!class_exists ('CurrencyDisplay')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'currencydisplay.php');
}
if (!class_exists ('VirtueMartModelVendor')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'vendor.php');
}
if (!class_exists ('VmImage')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'image.php');
}
if (!class_exists ('shopFunctionsF')) {
	require(JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'shopfunctionsf.php');
}
if (!class_exists ('calculationHelper')) {
	require(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'cart.php');
}
if (!class_exists ('VirtueMartModelProduct')) {
	JLoader::import ('product', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
}

class MinitekWallLibSourceVirtuemart
{
	public function getVirtuemartProductsCount($data_source, $globalLimit)
	{
		$productModel = VmModel::getModel('Product');
		if (array_key_exists('vmp_category_id', $data_source))
		{
			$category_id = $data_source['vmp_category_id'];
		}
		else
		{
			$category_id = 0;
		}
		$onlyPublished = TRUE;
		$withCalc = TRUE;
		$single = FALSE;

		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$front = TRUE;

			$user = JFactory::getUser();
			if (!($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart')))
			{
				if ($show_prices = VmConfig::get('show_prices', 1) == '0')
				{
					$withCalc = FALSE;
				}
			}
		}
		else
		{
			$front = FALSE;
		}

		$productModel->setFilter();

		if ($category_id)
		{
			$productModel->virtuemart_category_id = $category_id;
		}

		// Get ids
		$ids = $this->sortSearchListQuery($data_source, $startLimit=0, $pageLimit=0, $globalLimit);

		// Get count
		$products_count = count($ids);

		return $products_count;
	}

	public function getVirtuemartProducts($data_source, $startLimit, $pageLimit, $globalLimit)
	{
		$productModel = VmModel::getModel('Product');
		if (array_key_exists('vmp_category_id', $data_source))
		{
			$category_id = $data_source['vmp_category_id'];
		}
		else
		{
			$category_id = 0;
		}
		$onlyPublished = TRUE;
		$withCalc = TRUE;
		$single = FALSE;

		$app = JFactory::getApplication();
		if ($app->isSite()) {
			$front = TRUE;

			$user = JFactory::getUser();
			if (!($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart')))
			{
				if ($show_prices = VmConfig::get('show_prices', 1) == '0')
				{
					$withCalc = FALSE;
				}
			}
		}
		else
		{
			$front = FALSE;
		}

		$productModel->setFilter();

		if ($category_id)
		{
			$productModel->virtuemart_category_id = $category_id;
		}

		// Get ids
		$ids = $this->sortSearchListQuery($data_source, $startLimit, $pageLimit, $globalLimit);

		// Get products
		$usermodel = VmModel::getModel ('user');
		$currentVMuser = $usermodel->getCurrentUser ();
		if(!is_array($currentVMuser->shopper_groups)){
			$virtuemart_shoppergroup_ids = (array)$currentVMuser->shopper_groups;
		} else {
			$virtuemart_shoppergroup_ids = $currentVMuser->shopper_groups;
		}

		$products = array();
		foreach ($ids as $id)
		{
			if ($product = $productModel->getProduct((int)$id['virtuemart_product_id'], $front, $withCalc, $onlyPublished,1,$virtuemart_shoppergroup_ids)) {
				$products[] = $product;
			}
		}

		$productModel->addImages($products);

		return $products;
	}

	static function getRecursiveCategories($category_id, $cat_array)
	{
    $categoryModel = VmModel::getModel('Category');
    $categories = $categoryModel->getChildCategoryList(1, $category_id);

		if (count($categories))
		{
			foreach ($categories as $category)
			{
				$cat_array[] = $category;
				$category_children = self::getRecursiveCategories($category->virtuemart_category_id, $cat_array);
				foreach ($category_children as $category_child)
				{
					$cat_array[] = $category_child;
				}
			}
		}

    return $cat_array;
	}

	public function sortSearchListQuery($data_source, $startLimit, $pageLimit, $globalLimit, $langFields = array())
	{
		$app = JFactory::getApplication();

		// Data Source params
		$data_source == 'custom_filtering';

		// Set the list start limit
		if (!$startLimit)
		{
			$limit = $globalLimit;
			$start = 0;
		}
		else
		{
			$page = $app->input->get('page', '', 'INT');
			if (!$page || $page == 1) {
				$limit	= $startLimit;
				$start = 0;
			} else {
				$start_limit = $startLimit;
				$limit = $pageLimit;
				$start = $start_limit + (($page - 2) * $limit);

				$pagination = $app->input->get('pagination');
				if ($app->input->get('filters') == 'filters' && ($pagination == 1 || $pagination == '4')) { // Pagination: Append / Infinite
					$start = 0;
					$limit = $start_limit + (($page - 1) * $limit);
				}

				if ($start < $globalLimit) {
					if ($start + $pageLimit >= $globalLimit) {
						$limit = $globalLimit - $start;
					}
				} else {
					$limit = 0;
				}
			}
		}

		$category_filtering_type = (bool)$data_source['vmp_category_filtering_type'];
		$catfilter = $data_source['vmp_catfilter'];
		if (array_key_exists('vmp_category_id', $data_source))
		{
			$category_id = $data_source['vmp_category_id'];
		}
		else
		{
			$category_id = 0;
		}

		$manufacturer_filtering_type = (bool)$data_source['vmp_manufacturer_filtering_type'];
		$manufacturerfilter = (bool)$data_source['vmp_manufacturerfilter'];
		if (array_key_exists('vmp_manufacturers', $data_source))
		{
			$manufacturers = $data_source['vmp_manufacturers'];
			if (count($manufacturers) === 1 && $manufacturers[0] === '')
			{
				$manufacturers = 0;
			}
		}
		else
		{
			$manufacturers = 0;
		}

		// Exclude products
		$exclude_items = $data_source['vmp_exclude_items'];
		if (preg_match('/^[0-9,]+$/i', $exclude_items))
		{
			$exclude_items = trim($exclude_items, ',');
			$exclude_items = explode(',', $exclude_items);
			JArrayHelper::toInteger($exclude_items);
			$excluded_str = implode(',', $exclude_items);
		} else {
			 $excluded_str = 0;
		}

		$featured_items = $data_source['vmp_featured_items'];

		// Specific products
		/*$specific_items = $data_source['vmp_specific_items'];
		if (preg_match('/^[0-9,]+$/i', $specific_items))
		{
			$specific_items = trim($specific_items, ',');
			$specific_items = explode(',', $specific_items);
			JArrayHelper::toInteger($specific_items);
			$specific_str = implode(',', $specific_items);
		} else {
			$specific_str = 0;
		}*/

		// Core variables
		$productModel = VmModel::getModel('Product');
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$isSite = true;
		if ($app->isAdmin() or (vRequest::get('manage',false) and VmConfig::isSuperVendor()) ){
			$isSite = false;
		}
		$groupBy = ' group by p.`virtuemart_product_id` ';
		$ff_select_price = '';

		// Administrative variables to organize the joining of tables
		$joinCategory = FALSE;
		$joinCatLang = FALSE;
		$joinMf = FALSE;
		$joinMfLang = FALSE;
		$joinPrice = TRUE;
		$joinCustom = FALSE;
		$joinShopper = FALSE;
		$joinChildren = FALSE;
		$orderBy = ' ';

		// Start where / or
		$where = array();
		$or = array();

		// Only published
		if ($isSite)
		{
			$where[] = ' p.`published`="1" ';
		}
		if (!empty($productModel->virtuemart_vendor_id)){
			$where[] = ' p.`virtuemart_vendor_id` = "'.$productModel->virtuemart_vendor_id.'" ';
		}

		///////////////////////////////
		// Data source = Specific Items
		///////////////////////////////
		/*if ($data_source == 'vmp_specific_items')
		{
			// Specific products
			if ($specific_str)
			{
				$where[] = ' `p`.`virtuemart_product_id` IN ('.$specific_str.') ';
			}
		}*/

		/////////////////////////////////
		// Data source = Custom filtering
		/////////////////////////////////
		//else if ($data_source == 'custom_filtering')
		//{
			// Category filtering
			if (!$catfilter) // All Categories
			{
				if ($category_filtering_type) // Inclusive
				{}
				else if (!$category_filtering_type) // Exclusive
				{
					$joinCategory = TRUE;
					$where[] = ' `pc`.`virtuemart_category_id` IN (0) ';
				}
			}
			else // Selected Categories
			{
				if ($category_id)
				{
					if ($category_filtering_type) // Inclusive
					{
						$joinCategory = TRUE;

						if (isset($data_source['vmp_catchildren']) && $data_source['vmp_catchildren'])
						{
							$catmodel = VmModel::getModel ('category');
							$subwhere = array();

							foreach ($category_id as $parent_cat)
							{
								$childcats = self::getRecursiveCategories($parent_cat, null);
								$cats = $parent_cat;
								if (count($childcats))
								{
									foreach ($childcats as $childcat)
									{
										$cats .= ','.$childcat->virtuemart_category_id;
									}
								}
								$subwhere[] = ' `pc`.`virtuemart_category_id` IN ('.$cats.') ';
							}
							if (count($subwhere))
							{
								$where[] = '(' . implode(' OR ',$subwhere).')';
							}
						}
						else
						{
							$where[] = ' `pc`.`virtuemart_category_id` IN (' . implode(',',$category_id).') ';
						}
					}
					else if (!$category_filtering_type) // Exclusive
					{
						$joinCategory = TRUE;

						if (isset($data_source['vmp_catchildren']) && $data_source['vmp_catchildren'])
						{
							$catmodel = VmModel::getModel ('category');
							$subwhere = array();

							foreach ($category_id as $parent_cat)
							{
								$childcats = self::getRecursiveCategories($parent_cat, null);
								$cats = $parent_cat;
								if (count($childcats))
								{
									foreach ($childcats as $childcat)
									{
										$cats .= ','.$childcat->virtuemart_category_id;
									}
								}
								$subwhere[] = ' `pc`.`virtuemart_category_id` NOT IN ('.$cats.') ';
							}
							if (count($subwhere))
							{
								$where[] = '(' . implode(' AND ',$subwhere).')';
							}
						}
						else
						{
							$where[] = ' `pc`.`virtuemart_category_id` NOT IN (' . implode(',',$category_id).') ';
						}
					}
				}
			}

			// Manufacturer filtering
			if (!$manufacturerfilter) // All Manufacturers
			{
				if ($manufacturer_filtering_type) // Inclusive
				{}
				else if (!$manufacturer_filtering_type) // Exclusive
				{
					$joinMf = TRUE;
					$joinMfLang = TRUE;
					$where[] = ' `m`.`virtuemart_manufacturer_id` IN (0) ';
				}
			}
			else // Selected Manufacturers
			{
				if ($manufacturers)
				{
					if ($manufacturer_filtering_type) // Inclusive
					{
						$joinMf = TRUE;
						$joinMfLang = TRUE;
						$where[] = ' `m`.`virtuemart_manufacturer_id` IN (' . implode(',',$manufacturers).') ';
					}
					else if (!$manufacturer_filtering_type) // Exclusive
					{
						$joinMf = TRUE;
						$joinMfLang = TRUE;
						$where[] = ' `m`.`virtuemart_manufacturer_id` NOT IN (' . implode(',',$manufacturers).') ';
					}
				}
			}

			//
			if ($productModel->product_parent_id) {
				$where[] = ' p.`product_parent_id` = ' . $productModel->product_parent_id;
			}

			// Check shopper groups
			if ($isSite) {
				$usermodel = VmModel::getModel ('user');
				$currentVMuser = $usermodel->getUser ();
				$virtuemart_shoppergroup_ids = (array)$currentVMuser->shopper_groups;

				if (is_array ($virtuemart_shoppergroup_ids)) {
					$sgrgroups = array();
					foreach ($virtuemart_shoppergroup_ids as $key => $virtuemart_shoppergroup_id) {
						$sgrgroups[] = '`ps`.`virtuemart_shoppergroup_id`= "' . (int)$virtuemart_shoppergroup_id . '" ';
					}
					$sgrgroups[] = '`ps`.`virtuemart_shoppergroup_id` IS NULL ';
					$where[] = " ( " . implode (' OR ', $sgrgroups) . " ) ";

					$joinShopper = TRUE;
				}
			}

			// Exclude products
			if ($excluded_str) {
				$where[] = ' `p`.`virtuemart_product_id` NOT IN (' . $excluded_str.') ';
			}

			// Featured products
			if (!$data_source['vmp_featured_items'])
			{
				$where[] = " p.`product_special` != 1";
			} else
			if ($data_source['vmp_featured_items'] == '2')
			{
				$where[] = " p.`product_special` = 1";
			}

			// Show products with discount
			if (!$data_source['vmp_with_discount'])
			{
				$where[] = " (pp.`override` = 0) ";
			} else
			if ($data_source['vmp_with_discount'] == '2')
			{
				$where[] = " (pp.`override` != 0) ";
			}

			// Show products out of stock
			if (!$data_source['vmp_out_of_stock'])
			{
				$where[] = ' p.`product_in_stock` - p.`product_ordered` > "0" ';
			}

			// Show children products
			if (!$data_source['vmp_children_products'])
			{
				$where[] = " (p.`product_parent_id` = 0) ";
			}

			// Time range
			if ($data_source['vmp_time_range']) {
				$datenow = JFactory::getDate();
				$date =  $datenow->toSql();
				$where[] = " p.`created_on` > DATE_SUB('{$date}',INTERVAL ".$data_source['vmp_time_range']." DAY) ";
			}
		//}

		// Ordering
		$ordering = $data_source['vmp_ordering'];
		$ordering_direction = $data_source['vmp_ordering_direction'];
		switch ($ordering) {
			case 'created_on':
				$orderBy = ' ORDER BY `p`.`created_on` '.$ordering_direction.', `p`.`virtuemart_product_id` '.$ordering_direction.' ';
				break;
			case 'modified_on':
				$orderBy = ' ORDER BY `p`.`modified_on` '.$ordering_direction.', `p`.`virtuemart_product_id` '.$ordering_direction.' ';
				break;
			case 'product_name':
				$orderBy = ' ORDER BY `l`.`product_name` '.$ordering_direction.', `p`.`virtuemart_product_id` '.$ordering_direction.' ';
				break;
			case 'hits':
				$orderBy = ' ORDER BY `p`.`hits` '.$ordering_direction.', `p`.`virtuemart_product_id` '.$ordering_direction.' ';
				break;
			case 'product_sales':
				$orderBy = ' ORDER BY `p`.`product_sales` '.$ordering_direction.', `p`.`virtuemart_product_id` '.$ordering_direction.' ';
				break;
			case 'random':
				$orderBy = 'ORDER BY RAND()';
				break;
		}

		// Start tables
		$joinedTables = array();
		$joinLang = false;

		//This option switches between showing products without the selected language or only products with language.
		if($app->isSite() and !VmConfig::get('prodOnlyWLang',false))
		{
			$productLangFields = array('product_s_desc','product_desc','product_name','metadesc','metakey','slug');
			foreach($productLangFields as $field){
				if(strpos($orderBy,$field,6)!==FALSE){
					$langFields[] = $field;
					$orderbyLangField = $field;
					$joinLang = true;
					break;
				}
			}

		} else {
			$joinLang = true;
		}

		$selectLang = '';
		if ($joinLang or count($langFields)>0 ) {

			if(!VmConfig::get('prodOnlyWLang',false) and VmConfig::$defaultLang!=VmConfig::$vmlang and Vmconfig::$langCount>1){

				$productModel->useLback = true;
				$method = 'LEFT';
				if($isSite){
					$method = 'INNER';
				}
				$joinedTables[] = ' '.$method.' JOIN `#__virtuemart_products_' .VmConfig::$defaultLang . '` as ld using (`virtuemart_product_id`)';
				$joinedTables[] = ' LEFT JOIN `#__virtuemart_products_' . VmConfig::$vmlang . '` as l using (`virtuemart_product_id`)';
				$langFields = array_unique($langFields);

				if(count($langFields)>0){
					foreach($langFields as $langField){
						$selectLang .= ', IFNULL(l.'.$langField.',ld.'.$langField.') as '.$langField.'';
					}
				}
			} else {
				$productModel->useLback = false;
				$joinedTables[] = ' INNER JOIN `#__virtuemart_products_' . VmConfig::$vmlang . '` as l using (`virtuemart_product_id`)';
			}
		}

		$select = ' p.`virtuemart_product_id`'.$ff_select_price.$selectLang.' FROM `#__virtuemart_products` as p ';

		if ($joinShopper == TRUE) {
			$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_shoppergroups` as ps ON p.`virtuemart_product_id` = `ps`.`virtuemart_product_id` ';
		}

		if ($joinCategory == TRUE or $joinCatLang) {
			$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_categories` as pc ON p.`virtuemart_product_id` = `pc`.`virtuemart_product_id` ';
			if($joinCatLang){
				$joinedTables[] = ' LEFT JOIN `#__virtuemart_categories_' . VmConfig::$vmlang . '` as c ON c.`virtuemart_category_id` = `pc`.`virtuemart_category_id`';
			}
		}

		if ($joinMf == TRUE or $joinMfLang) {
			$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_manufacturers` ON p.`virtuemart_product_id` = `#__virtuemart_product_manufacturers`.`virtuemart_product_id` ';
			$joinedTables[] = 'LEFT JOIN `#__virtuemart_manufacturers_' . VmConfig::$vmlang . '` as m ON m.`virtuemart_manufacturer_id` = `#__virtuemart_product_manufacturers`.`virtuemart_manufacturer_id` ';
		}

		if ($joinPrice == TRUE) {
			$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_prices` as pp ON p.`virtuemart_product_id` = pp.`virtuemart_product_id` ';
		}

		if ($productModel->searchcustoms) {
			$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_customfields` as pf ON p.`virtuemart_product_id` = pf.`virtuemart_product_id` ';
		}

		if ($productModel->searchplugin !== 0) {
			if (!empty($PluginJoinTables)) {
				$plgName = $PluginJoinTables[0];
				$joinedTables[] = ' LEFT JOIN `#__virtuemart_product_custom_plg_' . $plgName . '` as ' . $plgName . ' ON ' . $plgName . '.`virtuemart_product_id` = p.`virtuemart_product_id` ';
			}
		}

		if ($joinChildren) {
			$joinedTables[] = ' LEFT OUTER JOIN `#__virtuemart_products` children ON p.`virtuemart_product_id` = children.`product_parent_id` ';
		}

		if ($productModel->searchplugin !== 0) {
			JPluginHelper::importPlugin('vmcustom');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('plgVmBeforeProductSearch', array(&$select, &$joinedTables, &$where, &$groupBy, &$orderBy,&$joinLang));
		}

		// WHERE strings
		if (count ($where) > 0) {
			$whereString = ' WHERE (' . implode (' AND ', $where) . ') ';
		}
		else {
			$whereString = '';
		}

		$orderByString = $orderBy;

		if($productModel->_onlyQuery){
			return (array($select,$joinedTables,$where,$orderBy,$joinLang));
		}
		$joinedTables = " \n".implode(" \n",$joinedTables);

		$joinedTables .= "\n".$whereString."\n".$groupBy."\n".$orderBy;

		$q = 'SELECT '.$select.$joinedTables;

		$db->setQuery( $q, $start, $limit );
		$this->ids = $db->loadAssocList();

		if($err=$db->getErrorMsg()){
			vmError('exeSortSearchListQuery '.$err);
		}

		if(empty($this->ids)){
			$errors = $db->getErrorMsg();
			if( !empty( $errors)){
				vmdebug('exeSortSearchListQuery error in class '.get_class($this).' sql:',$db->getErrorMsg());
			}
			$this->ids = array();
		}

		return $this->ids;

		//$product_ids = $productModel->exeSortSearchListQuery(2, $select, $joinedTables, $whereString, $groupBy, $orderBy, '', $limit);
		//$product_ids = $this->exeSortSearchListQuery(2, $select, $joinedTables, $whereString, $groupBy, $orderBy, '', $start, $limit);

		//return $product_ids;

	}

	public function exeSortSearchListQuery($object, $select, $joinedTables, $whereString = '', $groupBy = '', $orderBy = '', $filter_order_Dir = '', $start, $limit)
	{
		$db = JFactory::getDbo();

		//and the where conditions
		$joinedTables .="\n".$whereString."\n".$groupBy."\n".$orderBy.' '.$filter_order_Dir ;

		//if($this->_withCount){
			//$q = 'SELECT SQL_CALC_FOUND_ROWS '.$select.$joinedTables;
		//} else {
			$q = 'SELECT '.$select.$joinedTables;
		//}

		$db->setQuery( $q, $start, $limit );
		$this->ids = $db->loadAssocList();

		if($err=$db->getErrorMsg()){
			vmError('exeSortSearchListQuery '.$err);
		}

		/*if($this->_withCount){

			$db->setQuery('SELECT FOUND_ROWS()');
			$count = $db->loadResult();

			if($count == false){
				$count = 0;
			}
			$this->_total = $count;
			if($limitStart>=$count){
				if(empty($limit)){
					$limit = 1.0;
				}
				$limitStart = floor($count/$limit);
				$db->setQuery($q,$limitStart,$limit);
				if($object == 2){
					$this->ids = $db->loadColumn();
				} else if($object == 1 ){
					$this->ids = $db->loadAssocList();
				} else {
					$this->ids = $db->loadObjectList();
				}
			}
		} else {
			$this->_withCount = true;
		}*/

		if(empty($this->ids)){
			$errors = $db->getErrorMsg();
			if( !empty( $errors)){
				vmdebug('exeSortSearchListQuery error in class '.get_class($this).' sql:',$db->getErrorMsg());
			}
			if($object == 2 or $object == 1){
				$this->ids = array();
			}
		}

		return $this->ids;
	}
}
