<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\View\HtmlView as JView;
use RegularLabs\Library\ParametersNew as RL_Parameters;

jimport('joomla.application.component.view');

/**
 * List View
 */
class ContentTemplaterViewList extends JView
{
	protected $config;
	protected $enabled;
	protected $list;
	protected $pagination;
	protected $parameters;
	protected $state;

	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$this->enabled       = ContentTemplaterHelper::isEnabled();
		$this->list          = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->config        = RL_Parameters::getComponent('contenttemplater');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->hasCategories = $this->get('HasCategories');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = ContentTemplaterHelper::getActions();

		$viewLayout = JFactory::getApplication()->input->get('layout', 'default');

		if ($viewLayout == 'import')
		{
			// Set document title
			JFactory::getDocument()->setTitle(JText::_('CONTENTTEMPLATER') . ': ' . JText::_('RL_IMPORT_ITEMS'));
			// Set ToolBar title
			JToolbarHelper::title(JText::_('CONTENTTEMPLATER') . ': ' . JText::_('RL_IMPORT_ITEMS'), 'contenttemplater icon-reglab');
			// Set toolbar items for the page
			JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_contenttemplater');

			return;
		}

		// Set document title
		JFactory::getDocument()->setTitle(JText::_('CONTENTTEMPLATER') . ': ' . JText::_('RL_LIST'));
		// Set ToolBar title
		JToolbarHelper::title(JText::_('CONTENTTEMPLATER') . ': ' . JText::_('RL_LIST'), 'contenttemplater icon-reglab');
		// Set toolbar items for the page
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('item.add');
		}
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('item.edit');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::custom('list.copy', 'copy', 'copy', 'JTOOLBAR_DUPLICATE', true);
		}
		if ($canDo->get('core.edit.state') && $state->get('filter.state') != 2)
		{
			JToolbarHelper::publish('list.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('list.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		}
		if ($canDo->get('core.delete') && $state->get('filter.state') == -2)
		{
			JToolbarHelper::deleteList('', 'list.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('list.trash');
		}
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::custom('list.export', 'box-remove', 'box-remove', 'RL_EXPORT');
			JToolbarHelper::custom('list.import', 'box-add', 'box-add', 'RL_IMPORT', false);
		}
		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_contenttemplater');
		}
	}
}
