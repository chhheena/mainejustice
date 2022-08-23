<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Controller;

\defined('_JEXEC') or die('Restricted Access');
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\Model\Cache;
use JchOptimize\Model\OrderPlugins;
use Joomla\Application\AbstractApplication;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use JText;
use Joomla\CMS\Router\Route as JRoute;
class Utility extends AbstractController
{
    /**
     * Message to enqueue by application
     *
     * @var string;
     */
    private $message;
    /**
     * Message type
     *
     * @var string
     */
    private $messageType = 'success';
    /**
     * Url to redirect to
     *
     * @var string
     */
    private $redirectUrl;
    /**
     * @var OrderPlugins
     */
    private $orderPluginsModel;
    /**
     * @var Cache
     * @since version
     */
    private $cacheModel;
    /**
     * Constructor
     *
     * @param   OrderPlugins              $orderPluginsModel
     * @param   Input|null                $input
     * @param   AbstractApplication|null  $app
     */
    public function __construct(OrderPlugins $orderPluginsModel, Cache $cacheModel, ?Input $input = null, ?AbstractApplication $app = null)
    {
        $this->orderPluginsModel = $orderPluginsModel;
        $this->cacheModel = $cacheModel;
        parent::__construct($input, $app);
    }
    public function execute()
    {
        $this->{$this->getInput()->get('task', 'default')}();
        $this->getApplication()->enqueueMessage($this->message, $this->messageType);
        $this->getApplication()->redirect($this->redirectUrl);
    }
    private function browsercaching()
    {
        $expires = Tasks::leverageBrowserCaching();
        if ($expires === \false) {
            $this->message = JText::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FAILED');
            $this->messageType = 'error';
        } elseif ($expires === 'FILEDOESNTEXIST') {
            $this->message = JText::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FILEDOESNTEXIST');
            $this->messageType = 'warning';
        } elseif ($expires === 'CODEUPDATEDSUCCESS') {
            $this->message = JText::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDSUCCESS');
        } elseif ($expires === 'CODEUPDATEDFAIL') {
            $this->message = JText::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDFAIL');
            $this->messageType = 'notice';
        } else {
            $this->message = JText::_('COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_SUCCESS');
        }
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize', \false);
    }
    private function cleancache()
    {
        $deleted = $this->cacheModel->cleanCache();
        if (!$deleted) {
            $this->message = JText::_('COM_JCHOPTIMIZE_CACHECLEAN_FAILED');
            $this->messageType = 'error';
        } else {
            $this->message = JText::_('COM_JCHOPTIMIZE_CACHECLEAN_SUCCESS');
        }
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize', \false);
    }
    private function keycache()
    {
        Tasks::generateNewCacheKey();
        $this->message = JText::_('COM_JCHOPTIMIZE_CACHE_KEY_GENERATED');
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize', \false);
    }
    private function orderplugins()
    {
        $saved = $this->orderPluginsModel->orderPlugins();
        if ($saved === \false) {
            $this->message = JText::_('JLIB_APPLICATION_ERROR_REORDER_FAILED');
            $this->messageType = 'error';
        } else {
            $this->message = JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED');
        }
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize', \false);
    }
    private function restoreimages()
    {
        $mResult = Tasks::restoreBackupImages();
        if ($mResult === 'SOMEIMAGESDIDNTRESTORE') {
            $this->message = JText::_('COM_JCHOPTIMIZE_SOMERESTOREIMAGE_FAILED');
            $this->messageType = 'warning';
        } elseif ($mResult === 'BACKUPPATHDOESNTEXIST') {
            $this->message = JText::_('COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST');
            $this->messageType = 'warning';
        } else {
            $this->message = JText::_('COM_JCHOPTIMIZE_RESTOREIMAGE_SUCCESS');
        }
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize&view=OptimizeImages', \false);
    }
    private function deletebackups()
    {
        $mResult = Tasks::deleteBackupImages();
        if ($mResult === \false) {
            $this->message = JText::_('COM_JCHOPTIMIZE_DELETEBACKUPS_FAILED');
            $this->messageType = 'error';
        } elseif ($mResult === 'BACKUPPATHDOESNTEXIST') {
            $this->message = JText::_('COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST');
            $this->messageType = 'warning';
        } else {
            $this->message = JText::_('COM_JCHOPTIMIZE_DELETEBACKUPS_SUCCESS');
        }
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize&view=OptimizeImages', \false);
    }
    private function default()
    {
        $this->redirectUrl = JRoute::_('index.php?option=com_jchoptimize', \false);
    }
}
