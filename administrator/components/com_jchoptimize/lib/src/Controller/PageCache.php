<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Controller;

use JchOptimize\View\PageCacheHtml;
use JchOptimize\Model\PageCache as PageCacheModel;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use _JchOptimizeVendor\Laminas\Paginator\Adapter\ArrayAdapter;
use _JchOptimizeVendor\Laminas\Paginator\Paginator;
\defined('_JEXEC') or die('Restricted Access');
class PageCache extends AbstractController
{
    /**
     * @var PageCacheHtml
     */
    private $view;
    /**
     * @var PageCacheModel
     */
    private $pageCacheModel;
    public function __construct(PageCacheModel $pageCacheModel, PageCacheHtml $view, ?Input $input = null, ?AbstractApplication $app = null)
    {
        $this->pageCacheModel = $pageCacheModel;
        $this->view = $view;
        parent::__construct($input, $app);
    }
    /**
     * @inheritDoc
     */
    public function execute()
    {
        if ($this->getInput()->get('task') == 'remove') {
            $success = $this->pageCacheModel->delete($this->getInput()->get('cid'));
        }
        if ($this->getInput()->get('task') == 'deleteAll') {
            $success = $this->pageCacheModel->deleteAll();
        }
        if (isset($success)) {
            if ($success) {
                $message = Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETED_SUCCESSFULLY');
                $messageType = 'success';
            } else {
                $message = Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETE_ERROR');
                $messageType = 'error';
            }
            $this->getApplication()->enqueueMessage($message, $messageType);
            $this->getApplication()->redirect(Route::_('index.php?option=com_jchoptimize&view=PageCache', \false));
        }
        if (!PluginHelper::isEnabled('system', 'jchoptimizepagecache')) {
            $editUrl = Route::_('index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[search]=JCH Optimize Page Cache', \false);
            $this->getApplication()->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_PAGECACHE_NOT_ENABLED', $editUrl), 'warning');
        }
        $defaultListLimit = Factory::getApplication()->get('list_limit');
        $paginator = new Paginator(new ArrayAdapter($this->pageCacheModel->getItems()));
        $paginator->setCurrentPageNumber($this->getInput()->get('list_page', '1'))->setItemCountPerPage($this->pageCacheModel->getState()->get('list_limit', $defaultListLimit));
        $this->view->setData(['items' => $paginator, 'view' => 'PageCache', 'paginator' => $paginator->getPages(), 'pageLink' => 'index.php?option=com_jchoptimize&view=PageCache', 'adapter' => $this->pageCacheModel->getAdaptorName(), 'httpRequest' => $this->pageCacheModel->isCaptureCacheEnabled()]);
        $this->view->renderStatefulElements($this->pageCacheModel->getState());
        $this->view->loadResources();
        $this->view->loadToolBar();
        echo $this->view->render();
    }
}
