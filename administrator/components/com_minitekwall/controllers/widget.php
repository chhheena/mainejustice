<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallControllerWidget extends JControllerForm
{
	protected $text_prefix = 'COM_MINITEKWALL_WIDGET';

	protected function allowAdd($data = array())
	{
		$allow = null;

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.type_id', '');
		$app->setUserState('com_minitekwall.source_id', '');

		$task = $app->input->get('task');
		$item = $model->getItem();
		$id = $item->get('id');

		// Save source to widgets_source table
		$source_id = $item->get('source_id');
		$this_source = $source_id.'_source';
		$source = $validData[$this_source];
		$source = json_encode($source);

		$db = JFactory::getDbo();

		// Widget is new - insert source
		if (!$validData['id'])
		{
			$query = $db->getQuery(true);
			$columns = array(
				$db->quoteName('widget_id'),
				$db->quoteName($this_source)
			);
			$values = array(
				$db->quote($id),
				$db->quote($source)
			);
			$query
				->insert($db->quoteName('#__minitek_wall_widgets_source'))
				->columns($columns)
				->values(implode(',', $values));
			$db->setQuery($query);
			$db->execute();
		}
		else // Existing widget - update source
		{
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName($this_source) . ' = ' . $db->quote($source)
			);
			$conditions = array(
				$db->quoteName('widget_id') . ' = ' . $db->quote($id)
			);
			$query
				->update($db->quoteName('#__minitek_wall_widgets_source'))
				->set($fields)
				->where($conditions);

			$db->setQuery($query);
			$db->execute();
		}

		$msg = JText::_('COM_MINITEKWALL_WIDGET_SAVED');

		if ($task == 'apply')
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$id, $msg, 'Message');
		}

		return parent::postSaveHook($model, $validData);
	}

	public function clearWidgetStateVariables()
	{
		$app = JFactory::getApplication();

		$app->setUserState('com_minitekwall.type_id', '');
		$app->setUserState('com_minitekwall.source_id', '');
	}

	public function cancel($key = NULL)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.$this->context";

		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $app->input->getInt($key);

		// Attempt to check-in the current record.
		if ($recordId)
		{
			if ($checkin)
			{
				if ($model->checkin($recordId) === false)
				{
					// Check-in failed, go back to the record and display a notice.
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend($recordId, $key), false
						)
					);

					return false;
				}
			}
		}

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		// Clear state variables
		$this->clearWidgetStateVariables();

		// Redirect
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);

		return true;
	}

	public function selectMasonry()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.type_id', 'masonry');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectScroller()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.type_id', 'scroller');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceJoomla()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'joomla');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceCustom()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'custom');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceK2()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'k2');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceVirtuemart()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'virtuemart');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceJomsocial()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'jomsocial');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceEasyblog()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'easyblog');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceEasysocial()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'easysocial');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectSourceFolder()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'folder');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function selectRSSFeed()
	{
		// Set user variable
		$app = JFactory::getApplication();
		$app->setUserState('com_minitekwall.source_id', 'rss');

		$jinput = $app->input;
		$this_id = $jinput->get('id');

		// Redirect
		if ($this_id && $this_id !== 0)
		{
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit&id='.$this_id);
		} else {
			$app->redirect('index.php?option=com_minitekwall&view=widget&layout=edit');
		}
	}

	public function createModule()
	{
		JSession::checkToken('request') or jexit('Invalid token');

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$id = $jinput->get('id');

		$model = $this->getModel('Widget');
		$position = '';

		if ($id)
		{
			$data = $model->createModule($id, $position);
			if ($data)
			{
				// Redirect to module
				$msg = JText::_('COM_MINITEKWALL_WIDGET_MODULE_CREATED');
				$app->redirect('index.php?option=com_modules&filter.search=id:'.$data, $msg, 'Message');
			}
			else
			{
				// Redirect to widgets
				$msg = JText::_('COM_MINITEKWALL_WIDGET_ERROR_WHILE_CREATING_MODULE');
				$app->redirect('index.php?option=com_minitekwall&view=widgets', 'Notice');
			}
		}
		else
		{
			// Redirect to widgets
			$msg = JText::_('COM_MINITEKWALL_WIDGET_ERROR_WHILE_CREATING_MODULE');
			$app->redirect('index.php?option=com_minitekwall&view=widgets', 'Notice');
		}
	}

	public function createModuleforPlugin()
	{
		JSession::checkToken('request') or jexit('Invalid token');

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$id = $jinput->get('id');

		$model = $this->getModel('Widget');
		$position = 'minitekwall-'.$id;

		if ($id)
		{
			$data = $model->createModule($id, $position);
			if ($data)
			{
				// Redirect to module
				$msg = JText::_('COM_MINITEKWALL_WIDGET_MODULE_CREATED');
				$app->redirect('index.php?option=com_modules&filter.search=id:'.$data, $msg, 'Message');
			}
			else
			{
				// Redirect to widgets
				$msg = JText::_('COM_MINITEKWALL_WIDGET_ERROR_WHILE_CREATING_MODULE');
				$app->redirect('index.php?option=com_minitekwall&view=widgets', 'Notice');
			}
		}
		else
		{
			// Redirect to widgets
			$msg = JText::_('COM_MINITEKWALL_WIDGET_ERROR_WHILE_CREATING_MODULE');
			$app->redirect('index.php?option=com_minitekwall&view=widgets', 'Notice');
		}
	}
}
