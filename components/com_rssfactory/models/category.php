<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

class RssFactoryFrontendModelCategory extends JModelLegacy
{
    protected $categories = null;
    protected $params = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $menu = JFactory::getApplication()->getMenu()->getActive();
        $this->params = $menu ? $menu->params : new JRegistry();
    }

    public function getDisplayConfig()
    {
        static $config = null;

        if (is_null($config)) {
            $array = array(
                'category'    => $this->params->get('category'),
                'subcategory' => $this->params->get('subcategory'),
            );

            $config = new JRegistry($array);
        }

        return $config;
    }

    public function getCategories($id = null, $recursive = false)
    {
        if (is_null($id)) {
            $id = JFactory::getApplication()->input->getInt('category_id', 'root');
        }

        if (is_null($this->categories)) {
            $categories = JCategories::getInstance('RssFactory')->get($id);
            $this->categories = array();

            if (is_object($categories)) {
                /** @var $categories JCategoryNode */
                $this->categories = $categories->getChildren($recursive);
            }

            $array = array();
            foreach ($this->categories as $category) {
                $array[] = $category->id;
            }

            // Count stories per category.
            $stories = $this->getStories($array);
            foreach ($this->categories as $i => $category) {
                if (isset($stories[$category->id])) {
                    $this->categories[$i]->stories = $stories[$category->id]['stories'];
                } else {
                    $this->categories[$i]->stories = 0;
                }

                $this->categories[$i]->children = count($category->getChildren());
            }

            // Get categories headlines.
            if ($this->getDisplayConfig()->get('subcategory.headlines_show', 1)) {
                foreach ($this->categories as $i => $category) {
                    $this->categories[$i]->headlines = RssFactoryFeedsHelper::getItemsForList(array(
                        'categories' => $category->id,
                        'limit'      => $this->getDisplayConfig()->get('subcategory.headlines_limit', 5),
                    ));
                }
            }

            // Remove empty categories.
            if (!$this->params->get('subcategory.empty_show', 1)) {
                foreach ($this->categories as $i => $category) {
                    if (!$category->stories && !$category->children) {
                        unset($this->categories[$i]);
                    }
                }
            }
        }

        return $this->categories;
    }

    public function getCategory($id = null)
    {
        if (is_null($id)) {
            $id = JFactory::getApplication()->input->getInt('category_id', 'root');
        }

        if (!$id) {
            $id = 'root';
        }

        $category = JCategories::getInstance('RssFactory')->get($id);

        if (!$category) {
            return false;
        }

        if ($this->getDisplayConfig()->get('category.headlines_show', 1)) {
            if ('root' != $category->id) {
                $category->headlines = RssFactoryFeedsHelper::getItemsForList(array(
                    'categories' => $category->id,
                    'limit'      => $this->getDisplayConfig()->get('category.headlines_limit', 5),
                ));

                $stories = $this->getStories(array($id));
                $category->stories = isset($stories[$id]['stories']) ? $stories[$id]['stories'] : 0;
            }
        }

        return $category;
    }

    protected function getStories($categories = array())
    {
        if (!$categories) {
            return array();
        }

        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->select('COUNT(c.rssid) AS stories, ctg.id AS category_id')
            ->from('#__rssfactory_cache c')
            ->leftJoin('#__rssfactory f ON f.id = c.rssid')
            ->leftJoin('#__categories ctg ON ctg.id = f.cat')
            ->where('ctg.id IN (' . implode(',', $categories) . ')')
            ->group('ctg.id');

        $results = $dbo->setQuery($query)
            ->loadAssocList('category_id');

        return $results;
    }
}
