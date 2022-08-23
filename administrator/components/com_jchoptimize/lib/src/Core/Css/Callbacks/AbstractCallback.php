<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\Css\Callbacks;

use Joomla\Registry\Registry;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
\defined('_JCH_EXEC') or die('Restricted access');
abstract class AbstractCallback implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var Registry
     */
    protected $params;
    public function __construct(Registry $params)
    {
        $this->params = $params;
    }
    public abstract function processMatches($matches, $context);
}
