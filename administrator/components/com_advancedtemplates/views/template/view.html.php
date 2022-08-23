<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\View\HtmlView as JView;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\ParametersNew as RL_Parameters;

/**
 * View to edit a template style.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class AdvancedTemplatesViewTemplate extends JView
{
	/**
	 * An array containing a list of compressed files
	 */
	protected $archive;
	/**
	 * Encrypted file path
	 */
	protected $file;
	/**
	 * Name of the present file
	 */
	protected $fileName;
	/**
	 * A nested array containing lst of files and folders
	 */
	protected $files;
	/**
	 * For loading font information
	 */
	protected $font;
	/**
	 * For loading the source form
	 */
	protected $form;
	/**
	 * Extension id
	 */
	protected $id;
	/**
	 * For loading image information
	 */
	protected $image;
	/**
	 * List of available overrides
	 */
	protected $overridesList;
	/**
	 * Template id for showing preview button
	 */
	protected $preview;
	/**
	 * For loading source file contents
	 */
	protected $source;
	/**
	 * For loading extension state
	 */
	protected $state;
	/**
	 * For loading template details
	 */
	protected $template;
	/**
	 * Type of the file - image, source, font
	 */
	protected $type;

	/**
	 * Execute and display a template script.
	 *
	 * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$app            = JFactory::getApplication();
		$this->file     = $app->input->get('file');
		$this->fileName = base64_decode($this->file);
		$explodeArray   = explode('.', $this->fileName);
		$ext            = end($explodeArray);
		$this->files    = $this->get('Files');
		$this->state    = $this->get('State');
		$this->template = $this->get('Template');
		$this->preview  = $this->get('Preview');

		$params       = JComponentHelper::getParams('com_templates');
		$imageTypes   = explode(',', $params->get('image_formats'));
		$sourceTypes  = explode(',', $params->get('source_formats'));
		$fontTypes    = explode(',', $params->get('font_formats'));
		$archiveTypes = explode(',', $params->get('compressed_formats'));

		if (in_array($ext, $sourceTypes))
		{
			$this->form = $this->get('Form');
			$this->form->setFieldAttribute('source', 'syntax', $ext);
			$this->source = $this->get('Source');
			$this->type   = 'file';
		}
		elseif (in_array($ext, $imageTypes))
		{
			$this->image = $this->get('Image');
			$this->type  = 'image';
		}
		elseif (in_array($ext, $fontTypes))
		{
			$this->font = $this->get('Font');
			$this->type = 'font';
		}
		elseif (in_array($ext, $archiveTypes))
		{
			$this->archive = $this->get('Archive');
			$this->type    = 'archive';
		}
		else
		{
			$this->type = 'home';
		}

		$this->overridesList = $this->get('OverridesList');
		$this->id            = $this->state->get('extension.id');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors));

			return false;
		}

		$this->getConfig();
		$this->addToolbar();

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		if ( ! $user->authorise('core.admin'))
		{
			$this->setLayout('readonly');
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$app->input->set('hidemainmenu', true);

		// User is global SuperUser
		$isSuperUser = $user->authorise('core.admin');

		// Get the toolbar object instance
		$bar          = JToolBar::getInstance('toolbar');
		$explodeArray = explode('.', $this->fileName);
		$ext          = end($explodeArray);

		if ($this->config->heading_title)
		{
			JToolbarHelper::title(JText::sprintf('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE', ucfirst($this->template->name)), 'eye thememanager');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('ATP_HEADING_TEMPLATE', ucfirst($this->template->name)), 'advancedtemplatemanager icon-reglab');
		}

		// Only show file edit buttons for global SuperUser
		if ($isSuperUser)
		{
			// Add an Apply and save button
			if ($this->type == 'file')
			{
				JToolbarHelper::apply('template.apply');
				JToolbarHelper::save('template.save');
			}
			// Add a Crop and Resize button
			elseif ($this->type == 'image')
			{
				JToolbarHelper::custom('template.cropImage', 'move', 'move', 'COM_TEMPLATES_BUTTON_CROP', false);
				JToolbarHelper::modal('resizeModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RESIZE');
			}
			// Add an extract button
			elseif ($this->type == 'archive')
			{
				JToolbarHelper::custom('template.extractArchive', 'arrow-down', 'arrow-down', 'COM_TEMPLATES_BUTTON_EXTRACT_ARCHIVE', false);
			}

			// Add a copy template button (Hathor override doesn't need the button)
			if ($app->getTemplate() != 'hathor')
			{
				JToolbarHelper::modal('copyModal', 'icon-copy', 'COM_TEMPLATES_BUTTON_COPY_TEMPLATE');
			}
		}

		// Add a Template preview button
		if ($this->preview->client_id == 0)
		{
			$bar->appendButton('Popup', 'picture', 'COM_TEMPLATES_BUTTON_PREVIEW', JUri::root() . 'index.php?tp=1&templateStyle=' . $this->preview->id, 800, 520);
		}

		// Only show file manage buttons for global SuperUser
		if ($isSuperUser)
		{
			// Add Manage folders button
			JToolbarHelper::modal('folderModal', 'icon-folder icon white', 'COM_TEMPLATES_BUTTON_FOLDERS');

			// Add a new file button
			JToolbarHelper::modal('fileModal', 'icon-file', 'COM_TEMPLATES_BUTTON_FILE');

			// Add a Rename file Button (Hathor override doesn't need the button)
			if ($app->getTemplate() != 'hathor' && $this->type != 'home')
			{
				JToolbarHelper::modal('renameModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RENAME_FILE');
			}

			// Add a Delete file Button
			if ($this->type != 'home')
			{
				JToolbarHelper::modal('deleteModal', 'icon-remove', 'COM_TEMPLATES_BUTTON_DELETE_FILE');
			}

			// Add a Compile Button
			if ($ext == 'less')
			{
				JToolbarHelper::custom('template.less', 'play', 'play', 'COM_TEMPLATES_BUTTON_LESS', false);
			}
		}

		if ($this->type == 'home')
		{
			JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			JToolbarHelper::cancel('template.close', 'COM_TEMPLATES_BUTTON_CLOSE_FILE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
	}

	/**
	 * Method for creating the collapsible tree.
	 *
	 * @param array $array The value of the present node for recursion
	 *
	 * @return  string
	 *
	 * @note    Uses recursion
	 * @since   3.2
	 */
	protected function directoryTree($array)
	{
		$temp        = $this->files;
		$this->files = $array;
		$txt         = $this->loadTemplate('tree');
		$this->files = $temp;

		return $txt;
	}

	/**
	 * Method for listing the folder tree in modals.
	 *
	 * @param array $array The value of the present node for recursion
	 *
	 * @return  string
	 *
	 * @note    Uses recursion
	 * @since   3.2
	 */
	protected function folderTree($array)
	{
		$temp        = $this->files;
		$this->files = $array;
		$txt         = $this->loadTemplate('folders');
		$this->files = $temp;

		return $txt;
	}

	/**
	 * Function that gets the config settings
	 *
	 * @return    Object
	 */
	protected function getConfig()
	{
		if ( ! isset($this->config))
		{

			$this->config = RL_Parameters::getComponent('advancedtemplates');
		}

		return $this->config;
	}
}
