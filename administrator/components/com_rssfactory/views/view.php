<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.2.4
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.pagination.pagination');

class FactoryView extends JViewLegacy
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
                    return false;
                }
            }
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
            $this->sidebar = JHtmlSidebar::render();

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
                    JHtml::_('bootstrap.modal', 'collapseModal');
                    $title = FactoryText::_($button[0]);
                    $bar = JToolBar::getInstance('toolbar');
                    $dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-" . $button[1] . "\" title=\"$title\"></i>
						$title</button>";

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

        // Load css files.
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

class FactoryText
{
    protected static $option = 'com_rssfactory';

    public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
    {
        $string = strtoupper(self::$option . '_' . str_replace(' ', '_', $string));

        return JText::_($string, $jsSafe, $interpretBackSlashes, $script);
    }

    public static function sprintf()
    {
        $args = func_get_args();
        $args[0] = strtoupper(self::$option . '_' . $args[0]);

        return call_user_func_array(array('JText', 'sprintf'), $args);
    }

    public static function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
    {
        $string = strtoupper(self::$option . '_' . $string);

        return JText::script($string, $jsSafe, $interpretBackSlashes);
    }

    public static function plural($string, $n)
    {
        $args = func_get_args();
        $args[0] = strtoupper(self::$option . '_' . $args[0]);

        return call_user_func_array(array('JText', 'plural'), $args);
    }
}

class FactoryRoute
{
    protected static $option = 'com_rssfactory';

    public static function _($url = '', $xhtml = false, $ssl = null)
    {
        $url = 'index.php?option=' . self::$option . ($url != '' ? '&' . $url : '');

        return JRoute::_($url, $xhtml, $ssl);
    }

    public static function view($view, $xhtml = false, $ssl = null)
    {
        $url = 'view=' . $view;

        return self::_($url, $xhtml, $ssl);
    }

    public static function task($task, $xhtml = false, $ssl = null)
    {
        $url = 'task=' . $task;

        return self::_($url, $xhtml, $ssl);
    }
}

class FactoryHtml
{
    protected static $option = 'com_rssfactory';

    public static function script($file, $framework = false, $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
    {
        $file = self::parsePath($file);

        JHtml::script($file, $framework, $relative, $path_only, $detect_browser, $detect_debug);
    }

    public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
    {
        $file = self::parsePath($file, 'css');

        JHtml::stylesheet($file, $attribs, $relative, $path_only, $detect_browser, $detect_debug);
    }

    public static function registerHtml($html)
    {
        $html = strtolower($html);
        $path = JPATH_COMPONENT_SITE;

        if (false !== strpos($html, 'admin/')) {
            $html = str_replace('admin/', '', $html);
            $path = JPATH_COMPONENT_ADMINISTRATOR;
        }

        $class = 'JHtml' . ucfirst($html);

        return JLoader::register($class, $path . '/helpers/html/' . $html . '.php');
    }

    protected static function parsePath($file, $type = 'js')
    {
        $path = array();
        $parts = explode('/', $file);

        $path[] = 'media';
        $path[] = self::$option;
        $path[] = 'assets';

        if ('admin' == $parts[0]) {
            $path[] = 'backend';
            unset($parts[0]);
            $parts = array_values($parts);
        } else {
            $path[] = 'frontend';
        }

        $path[] = $type;

        $count = count($parts);
        foreach ($parts as $i => $part) {
            if ($i + 1 == $count) {
                $path[] = $part . '.' . $type;
            } else {
                $path[] = $part;
            }
        }

        return implode('/', $path);
    }
}

class FactoryPagination extends JPagination
{
    protected $anchor = null;

    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    public function getAnchor()
    {
        return $this->anchor;
    }

    protected function _buildDataObject()
    {
        $data = parent::_buildDataObject();

        if (null !== $anchor = $this->getAnchor()) {
            $pages = array('start', 'previous', 'next', 'end', 'all');

            foreach ($pages as $page) {
                if (isset($data->$page)) {
                    $data->$page->link .= '#' . $anchor;
                }
            }

            foreach ($data->pages as &$page) {
                $page->link .= '#' . $anchor;
            }
        }

        return $data;
    }
}

class FactoryViewHelp
{
    protected $component;
    protected $override = 'http://wiki.thephpfactory.com/doku.php?id=joomla{major}0:{component}:{keyref}';
    protected $xpath = '//div[@class="dokuwiki"]/div[@class="page"]/div/ul/li/div/a';
    protected $cache = 24;

    public function __construct(array $config = array())
    {
        if (isset($config['component'])) {
            $this->component = $config['component'];
        } else {
            $input = new JInput();
            $this->component = str_replace('com_', '', $input->getString('option'));
        }

        if (isset($config['override'])) {
            $this->override = $config['override'];
        }

        if (isset($config['xpath'])) {
            $this->xpath = $config['xpath'];
        }

        if (isset($config['cache'])) {
            $this->cache = $config['cache'];
        }
    }

    public function render($ref)
    {
        $pages = $this->getAvailablePages();

        if (!$pages || !in_array($ref, $pages)) {
            $ref = $this->component;
        }

        JToolbarHelper::help($ref, false, $this->override, $this->component);
    }

    protected function readUrl($url)
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $hash = md5($url);
        $path = JPATH_ADMINISTRATOR . '/cache/com_' . $this->component;

        if (!JFolder::exists($path)) {
            JFolder::create($path);
        }

        if (!JFile::exists($path . '/' . $hash) || time() - 60 * 60 * $this->cache > filemtime($path . '/' . $hash)) {
            $data = $this->getUrl($url);

            file_put_contents($path . '/' . $hash, $data);
        } else {
            $data = file_get_contents($path . '/' . $hash);
        }

        return $data;
    }

    protected function parseHtml($html)
    {
        $pages = array();

        if ($html == strip_tags($html)) {
            return $pages;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors(false);

        $xpath = new DOMXpath($doc);
        $items = $xpath->query($this->xpath);

        foreach ($items as $item) {
            /** @var DOMElement $item */
            $href = $item->getAttribute('href');
            $explode = explode(':', $href);
            $page = end($explode);

            if (false !== strpos($page, '#')) {
                list($page, $anchor) = explode('#', $page);
            }

            $pages[] = $page;
        }

        return $pages;
    }

    protected function getAvailablePages()
    {
        $url = JHelp::createURL($this->component, false, $this->override, $this->component);
        $html = $this->readUrl($url);

        return $this->parseHtml($html);
    }

    protected function getUrl($url)
    {
        $data = $this->getUrlCurl($url);

        if (false !== $data) {
            return $data;
        }

        $data = $this->getUrlFileOpen($url);

        if (false !== $data) {
            return $data;
        }

        $data = $this->getUrlFSockOpen($url);

        return $data;
    }

    protected function getUrlCurl($url)
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CONNECTTIMEOUT => 5,
        ));

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    protected function getUrlFileOpen($url)
    {
        if (!ini_get('allow_url_fopen')) {
            return false;
        }

        return file_get_contents($url);
    }

    protected function getUrlFSockOpen($url)
    {
        $uri = JUri::getInstance($url);
        $fp = fsockopen($uri->getHost(), 80, $errno, $errstr, 30);

        if (!$fp) {
            return false;
        }

        $data = array();
        $out = array(
            'GET ' . $uri->getPath() . $uri->getQuery() . ' HTTP/1.1' . "\r\n",
            'Host: ' . $uri->getHost() . "\r\n",
            'Connection: Close' . "\r\n\r\n",
        );

        fwrite($fp, implode($out));

        while (!feof($fp)) {
            $data[] = fgets($fp, 128);
        }

        fclose($fp);

        return implode($data);
    }
}
