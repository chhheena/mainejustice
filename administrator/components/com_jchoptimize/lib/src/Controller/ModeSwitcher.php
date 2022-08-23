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
use Joomla\Application\AbstractApplication;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use JchOptimize\Model\ModeSwitcher as ModeSwitcherModel;
use Joomla\Input\Input;
class ModeSwitcher extends AbstractController
{
    /**
     * @var ModeSwitcherModel
     */
    private $model;
    public function __construct(ModeSwitcherModel $model, ?Input $input = null, ?AbstractApplication $application = null)
    {
        $this->model = $model;
        parent::__construct($input, $application);
    }
    public function execute()
    {
        $action = $this->getInput()->get('task');
        $this->model->{$action}();
        $mode = \str_replace('set', '', $action);
        $this->getApplication()->enqueueMessage(\sprintf('JCH Optimize set in %s mode', $mode));
        $this->getApplication()->redirect(\base64_decode($this->getInput()->get('return', '', 'base64')));
    }
}
