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

defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\DB as RL_DB;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class PlgFieldsArticlesFilters
{
	private $db;
	private $form;
	private $params;

	public function __construct($params, $form = null)
	{
		$this->params = $params;
		$this->form   = $form;
		$this->db     = JFactory::getDbo();
	}

	public function addToQuery(&$query)
	{
		$this->addCategoriesToQuery($query);
	}

	public function get()
	{
		$filters = [];

		if ($this->params->get('filter_categories'))
		{
			$categories = RL_Array::toArray($this->params->get('categories'));
			if ($this->params->get('filter_categories') === 'current')
			{
				$categories = [$this->getCurrentCategoryId()];
			}

			$filters['filter_categories'] = true;

			$filters['categories']              = $categories;
			$filters['categories_inc_children'] = $this->params->get('categories_inc_children');
		}


		return $filters;
	}

	public function getCategories()
	{
		if ( ! $this->params->get('filter_categories'))
		{
			return [];
		}

		if ($this->params->get('filter_categories') === 'current')
		{
			return [$this->getCurrentCategoryId()];
		}

		$categories = (array) $this->params->get('categories', []);

		if (empty($categories))
		{
			return [];
		}

		$inc_children = $this->params->get('categories_inc_children');

		if ( ! $inc_children)
		{
			return $categories;
		}

		$children = $this->getCategoriesChildIds($categories);

		if ($inc_children == 2)
		{
			return $children;
		}

		return array_merge($categories, $children);
	}

	public function getCurrentArticleId()
	{
		$input = JFactory::getApplication()->input;

		if ($input->get('option') != 'com_content'
			|| ! in_array($input->get('view'), ['form', 'article'])
			|| ! in_array($input->get('layout'), ['edit', 'modal'])
		)
		{
			return 0;
		}

		if ($this->form && $this->form->getValue('id'))
		{
			return $this->form->getValue('id');
		}

		return $input->getInt('id');
	}

	private function addCategoriesToQuery(&$query)
	{
		$categories = $this->getCategories();

		if (empty($categories))
		{
			return $categories;
		}

		$query->where('a.catid ' . RL_DB::in($categories));

		return $categories;
	}

	private function addCustomFieldsToQuery(&$query)
	{
	}

	private function addTagsToQuery(&$query)
	{
	}

	private function addUsersToQuery(&$query)
	{
	}

	private function filterDownArticlesByCustomFields(&$ids, $id, $value)
	{
	}

	private function getArticlesByCustomFields()
	{
	}

	private function getCategoriesChildIds($categories = [])
	{
		$children = [];

		$query = $this->db->getQuery(true)
			->select('a.id')
			->from($this->db->quoteName('#__categories', 'a'))
			->where('a.extension = ' . $this->db->quote('com_content'))
			->where('a.published = 1');

		while ( ! empty($categories))
		{
			$query->clear('where')
				->where('a.parent_id' . RL_DB::in($categories));
			$this->db->setQuery($query);
			$categories = $this->db->loadColumn();

			$children = array_merge($children, $categories);
		}

		return $children;
	}

	private function getCurrentArticleLanguage()
	{
	}

	private function getCurrentCategoryId()
	{
	}

	private function getTags()
	{
	}

	private function getTagsChildIds($tags = [])
	{
	}

	private function getUsers()
	{
	}
}
