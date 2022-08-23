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
namespace JchOptimize\Core\Html\Callbacks;

\defined('_JCH_EXEC') or die('Restricted access');
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
abstract class AbstractCallback implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @var string        RegEx used to process HTML
     */
    protected $regex;
    /**
     * @var Registry        Plugin parameters
     */
    protected $params;
    /**
     * Constructor
     *
     * @param   Registry  $params
     */
    public function __construct(Registry $params)
    {
        $this->params = $params;
    }
    public function setRegex(string $regex)
    {
        $this->regex = $regex;
    }
    abstract function processMatches($matches);
}
