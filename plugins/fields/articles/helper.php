<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\ArticlesAnywhere\Replace as AA_Replace;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class PlgFieldsArticlesHelper
{
	public static function getFullOrdering(
		$primary_ordering, $primary_direction,
		$secondary_ordering, $secondary_direction,
		$prefix_articles = 'a',
		$prefix_categories = 'c'
	)
	{
		$db = JFactory::getDbo();

		self::prepareOrdering($primary_ordering, $primary_direction);
		self::prepareOrdering($secondary_ordering, $secondary_direction);

		self::prefixOrdering($primary_ordering, $prefix_articles, $prefix_categories);
		self::prefixOrdering($secondary_ordering, $prefix_articles, $prefix_categories);

		return $db->quoteName($primary_ordering) . ' ' . $primary_direction . ','
			. $db->quoteName($secondary_ordering) . ' ' . $secondary_direction;
	}

	public static function getLinkedArticleIds($field_ids, $article_id, $field)
	{
	}

	public static function prepareCustomField($context, $item, &$field)
	{
		JPluginHelper::importPlugin('fields');

		$dispatcher = JEventDispatcher::getInstance();

		// Event allow plugins to modify the output of the field before it is prepared
		$dispatcher->trigger('onCustomFieldsBeforePrepareField', [$context, $item, &$field]);

		// Gathering the value for the field
		$value = $dispatcher->trigger('onCustomFieldsPrepareField', [$context, $item, &$field]);

		if (is_array($value))
		{
			$value = implode($value, ' ');
		}

		// Event allow plugins to modify the output of the prepared field
		$dispatcher->trigger('onCustomFieldsAfterPrepareField', [$context, $item, $field, &$value]);

		// Assign the value
		$field->value = $value;
	}

	public static function renderLayout($ids, $layout, $field, $layout_type = 'title', $apply_ordering = true)
	{
		if (count($ids) === 1)
		{
			$ids = RL_Array::toArray($ids[0]);
		}

		$settings = (object) [];
		switch ($layout_type)
		{
			case 'title_custom':
				$settings->custom_field = $field->fieldparams->get('custom_field', '');
				$settings->link_title   = $field->fieldparams->get('link_title', 1);
				break;
			case 'title':
			default:
				$settings->link_title = $field->fieldparams->get('link_title', 1);
				break;
		}

		$outputs   = self::getOutputs($ids, $layout, $field, $settings, $apply_ordering);
		$separator = $field->fieldparams->get('use_separator') ? $field->fieldparams->get('separator', ', ') : '';

		return implode($separator, $outputs);
	}

	public static function replaceDataTags($string, &$article)
	{
	}

	public static function runThroughArticlesAnywhere($string)
	{
	}

	private static function addFilters(&$query, $field)
	{
	}

	private static function getArticleFieldIds()
	{
	}

	private static function getArticleOrderId($article, $ordering)
	{
		if ( ! isset($article->{$ordering}))
		{
			return 0;
		}

		switch ($ordering)
		{
			case 'ordering':
			case 'hits':
				return ($article->{$ordering} + 100000000);

			case 'created_time':
			case 'modified_time':
			case 'publish_up':
			case 'alias':
			case 'title':
			default:
				return strtolower($article->{$ordering});
		}
	}

	private static function getCategoriesByFieldId($field_id)
	{
	}

	private static function getChildCategories($categories)
	{
	}

	private static function getDataTagValue(&$article, $tag)
	{
	}

	private static function getDataTagValueCustomField(&$article, $key)
	{
	}

	private static function getDataTags($html)
	{
	}

	private static function getLinkedArticleIdsByFieldId($field_id, $article_id, $field)
	{
	}

	private static function getOutputs($ids, $layout, $field, $settings, $apply_ordering = true)
	{
		$ids = array_unique($ids);

		$articles = self::getArticlesByIds($ids);

		if ($apply_ordering)
		{
			$primary_ordering    = $field->fieldparams->get('articles_ordering', 'title');
			$primary_direction   = $field->fieldparams->get('articles_ordering_direction', 'ASC');
			$secondary_ordering  = $field->fieldparams->get('articles_ordering_2', 'created');
			$secondary_direction = $field->fieldparams->get('articles_ordering_direction_2', 'DESC');

			self::orderArticles($articles, $primary_ordering, $primary_direction, $secondary_ordering, $secondary_direction);
		}

		return self::getOutputsByArticles($articles, $layout, $settings);
	}

	private static function getOutputsByArticles($articles, $layout, $settings)
	{
		$outputs     = [];
		$total_count = count($articles);

		foreach ($articles as $i => $article)
		{

			$output = $layout->render(compact('article', 'settings'));

			if (empty($output))
			{
				continue;
			}

			$outputs[] = $output;
		}

		return $outputs;
	}

	private static function getArticlesByIds($ids)
	{
		$articles = [];

		foreach ($ids as $id)
		{
			if ( ! $id)
			{
				continue;
			}

			$article = RL_Article::get($id);

			if (empty($article->id))
			{
				continue;
			}

			if ( ! in_array($article->access, JFactory::getUser()->getAuthorisedViewLevels()))
			{
				continue;
			}

			$articles[] = $article;
		}

		return $articles;
	}

	private static function orderArticles(&$articles, $primary_ordering, $primary_direction = 'ASC', $secondary_ordering = '', $secondary_direction = 'ASC')
	{
		$ordered = self::orderArticlesSet($articles, $primary_ordering, $primary_direction);

		if ( ! $secondary_ordering)
		{
			$articles = RL_Array::flatten($ordered);

			return;
		}

		foreach ($ordered as &$ordered_set)
		{
			$ordered_set = self::orderArticlesSet($ordered_set, $secondary_ordering, $secondary_direction);
		}

		$articles = RL_Array::flatten($ordered);
	}

	private static function orderArticlesSet($articles, $ordering, $direction = 'ASC')
	{
		if ( ! is_array($articles) || count($articles) < 2)
		{
			return $articles;
		}

		self::prepareOrdering($ordering, $direction);

		$ordered = [];

		// Handle 1st ordering
		foreach ($articles as $article)
		{
			$order_id = self::getArticleOrderId($article, $ordering);

			if ( ! isset($ordered[$order_id]))
			{
				$ordered[$order_id] = [];
			}

			$ordered[$order_id][] = $article;
		}

		switch ($direction)
		{
			case 'DESC':
				krsort($ordered);
				break;
			case 'ASC':
			default:
				ksort($ordered);
				break;
		}

		return array_values($ordered);
	}

	private static function prefixOrdering(&$ordering, $prefix_articles = 'a', $prefix_categories = 'c')
	{
		if (strpos($ordering, 'category_') === 0)
		{
			$ordering = $prefix_categories . '.' . substr($ordering, strlen('category_'));

			return;
		}

		$ordering = $prefix_articles . '.' . $ordering;
	}

	private static function prepareOrdering(&$ordering, &$direction)
	{
		if ($ordering == 'featured')
		{
			$direction = $direction == 'DESC' ? 'ASC' : 'DESC';
		}
	}
}
