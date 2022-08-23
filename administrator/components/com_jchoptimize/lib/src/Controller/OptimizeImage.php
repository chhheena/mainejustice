<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Controller;

use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
class OptimizeImage extends AbstractController
{
    public function execute()
    {
        $status = $this->getInput()->get('status', null);
        if (\is_null($status)) {
            echo AdminAjax::getInstance('OptimizeImage')->run();
            $this->getApplication()->close();
        } else {
            if ($status == 'success') {
                $dir = \rtrim($this->getInput()->get('dir', ''), '/') . '/';
                $cnt = $this->getInput()->get('cnt', '');
                $this->getApplication()->enqueueMessage(\sprintf(JText::_('%1$d images optimized in %2$s'), $cnt, $dir));
            } else {
                $msg = $this->getInput()->get('msg', '');
                $this->getApplication()->enqueueMessage(JText::_('The Optimize Image function failed with message "' . $msg), 'error');
            }
            $this->getApplication()->redirect(JRoute::_('index.php?option=com_jchoptimize&view=OptimizeImages', \false));
        }
    }
}
