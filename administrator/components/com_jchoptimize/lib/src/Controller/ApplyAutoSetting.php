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

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Router\Route as JRoute;
use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use Joomla\Input\Input;
use JchOptimize\Model\Configure;
use function json_decode;
class ApplyAutoSetting extends AbstractController
{
    /**
     * @var Configure
     */
    private $model;
    public function __construct(Configure $model, ?Input $input = null, ?AbstractApplication $app = null)
    {
        $this->model = $model;
        parent::__construct($input, $app);
    }
    public function execute()
    {
        $this->model->applyAutoSettings($this->getInput()->get('autosetting', 's1'));
        $body = \json_encode(['success' => \true]);
        $this->getApplication()->clearHeaders();
        $this->getApplication()->setHeader('Content-Type', 'application/json');
        $this->getApplication()->setHeader('Content-Length', \strlen($body));
        $this->getApplication()->setBody($body);
        $this->getApplication()->allowCache(\false);
        echo $this->getApplication()->toString();
        $this->getApplication()->close();
    }
}
