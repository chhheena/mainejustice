<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.2
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class FactoryView extends HtmlView
{
    protected
        $option = 'com_rssfactory',
        $get = array(),
        $buttons = array(),
        $title = 'title',
        $id = 'id',
        $css = array(),
        $js = array(),
        $html = array(),
        $registerHtml = array(),
        $filters = array(),
        $permissions = array(),
        $tpl = null,
        $layout = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        if ($this->permissions) {
            $user = JFactory::getUser();

            foreach ($this->permissions as $layout => $permission) {
                if (is_string($layout) && $this->getLayout() != $layout) {
                    continue;
                }

                if (!$user->authorise($permission, $this->option)) {
                    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
                }
            }
        }

        if (null === $this->document) {
            $this->document = \Joomla\CMS\Factory::getDocument();
        }
    }

    public function display($tpl = null)
    {
        $isAdmin = JFactory::getApplication()->isAdmin();

        if (is_null($tpl)) {
            $tpl = $this->tpl;
        }

        if (!is_null($this->layout)) {
            $this->setLayout($this->layout);
        }

        foreach ($this->get as $get) {
            $this->$get = $this->get($get);
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->loadAssets();
        $this->setCharset();

        if ($isAdmin) {
            $this->addToolbar();
            $this->addFilters();

            if (isset($this->saveOrder) && $this->saveOrder) {
                $saveOrderingUrl = 'index.php?option=' . $this->option . '&task=' . $this->getName() . '.saveOrderAjax&tmpl=component';
                JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($this->listDirn), $saveOrderingUrl);
            }

            RssFactoryHelper::addSubmenu($this->getName());
            if (3 === (int)\Joomla\CMS\Version::MAJOR_VERSION) {
                $this->sidebar = JHtmlSidebar::render();
            }

            $viewHelp = new FactoryViewHelp();
            $viewHelp->render($this->getName());
        } else {
            $this->prepareDocument();
        }

        return parent::display($tpl);
    }

    protected function addToolbar()
    {
        // Set title
        $this->setTitle();

        // Add buttons
        foreach ($this->buttons as $type => $button) {

            if (is_int($type)) {
                $type = $button;
            }

            switch ($type) {
                case '':
                    JToolBarHelper::divider();
                    break;

                case 'add':
                    JToolBarHelper::addNew(rtrim($this->getName(), 's') . '.add');
                    break;

                case 'edit':
                    JToolBarHelper::editList(rtrim($this->getName(), 's') . '.edit');
                    break;

                case 'publish':
                    JToolBarHelper::publishList($this->getName() . '.publish');
                    break;

                case 'unpublish':
                    JToolBarHelper::unpublishList($this->getName() . '.unpublish');
                    break;

                case 'delete':
                    JToolBarHelper::deleteList(FactoryText::_('list_delete'), $this->getName() . '.delete');
                    break;

                case 'apply':
                    JToolBarHelper::apply($this->getName() . '.apply');
                    break;

                case 'save':
                    JToolBarHelper::save($this->getName() . '.save');
                    break;

                case 'save2new':
                    JToolBarHelper::save2new($this->getName() . '.save2new');
                    break;

                case 'save2copy':
                    JToolBarHelper::save2copy($this->getName() . '.save2copy');
                    break;

                case 'close':
                    JToolBarHelper::cancel($this->getName() . '.cancel', (isset($this->item) && $this->item->{$this->id} ? 'JTOOLBAR_CLOSE' : 'JTOOLBAR_CANCEL'));
                    break;

                case 'back':
                    JToolBarHelper::back();
                    break;

                case 'batch':
//                    JHtml::_('bootstrap.modal', 'collapseModal');
                    $title = FactoryText::_($button[0]);
                    $bar = JToolBar::getInstance('toolbar');
                    $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small btn-secondary\"><i class=\"icon-" . $button[1] . "\" title=\"$title\"></i>$title</button>";

                    $bar->appendButton('Custom', $dhtml, 'batch');
                    break;

                default:
                    JToolBarHelper::custom($this->getName() . '.' . $button[0], $button[2], $button[2], FactoryText::_($button[1]), $button[3]);
                    break;
            }
        }

        return true;
    }

    protected function setTitle()
    {
        if (isset($this->item) && $this->item) {
            if ($this->item->{$this->id}) {
                $title = is_null($this->title) ? '' : $this->item->{$this->title};
                JToolBarHelper::title(FactoryText::sprintf('view_title_edit_' . $this->getName(), $title, $this->item->{$this->id}));
            } else {
                JToolBarHelper::title(FactoryText::_('view_title_new_' . $this->getName()));
            }
        } else {
            JToolBarHelper::title(FactoryText::_('view_title_' . $this->getName()));
        }

        return true;
    }

    protected function loadAssets()
    {
        // Initialise variables.
        $prefix = JFactory::getApplication()->isAdmin() ? 'admin/' : '';

        // Load html.
        foreach ($this->html as $html) {
            if (false !== strpos($html, '/')) {
                list($html, $arg) = explode('/', $html);
                JHtml::_($html, $arg);
            } else {
                JHtml::_($html);
            }
        }

        $version = (int)\Joomla\CMS\Version::MAJOR_VERSION;

        // Load css files.
        $this->css[] = $prefix . 'migration' . $version;
        $this->css[] = $prefix . 'views/' . strtolower($this->getName());
        foreach ($this->css as $css) {
            FactoryHtml::stylesheet($css);
        }

        // Load js files.
        $this->js[] = $prefix . 'views/' . strtolower($this->getName());
        foreach ($this->js as $js) {
            FactoryHtml::script($js);
        }

        // Register view html.
        foreach ($this->registerHtml as $html) {
            FactoryHtml::registerHtml($html);
        }

        // Register view html helper.
        JLoader::register('JHtml' . $this->getName(), JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html/' . $this->getName() . '.php');
        JLoader::register('JHtmlRssFactory', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html/rssfactory.php');
    }

    protected function addFilters()
    {
        if (!$this->filters) {
            return true;
        }

        JHtmlSidebar::setAction('index.php?option=' . $this->option . '&view=' . $this->getName());

        foreach ($this->filters as $filter) {
            JHtmlSidebar::addFilter(
                FactoryText::_('list_filter_title_' . $filter),
                'filter_' . $filter,
                JHtml::_('select.options', $this->get('Filter' . ucfirst($filter)), 'value', 'text', $this->state->get('filter.' . $filter), true)
            );
        }
    }

    protected function prepareDocument()
    {
        $app = JFactory::getApplication();
        $menu = JMenu::getInstance('site');
        $active = $menu->getActive();

        $title = $active ? $active->title : null;

        if (empty($title)) {
            $title = $app->get('sitename');
        }
        elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        }
        elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        if (empty($title)) {
            $title = $this->item->title;
        }

        $this->document->setTitle($title);

        return true;
    }

    protected function setCharset()
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');
        $charset = $configuration->get('force_charset', '');

        if ('' != $charset) {
            JFactory::getDocument()->setCharset($charset);
        }
    }
}
