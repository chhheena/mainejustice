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
namespace JchOptimize\Core\Admin\Ajax;

use JchOptimize\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Input\Input;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
\defined('_JCH_EXEC') or die('Restricted access');
abstract class Ajax implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    /**
     * @var Input
     */
    protected $input;
    private function __construct()
    {
        \ini_set('pcre.backtrack_limit', 1000000);
        \ini_set('pcre.recursion_limit', 1000000);
        if (\version_compare(\PHP_VERSION, '7.0.0', '>=')) {
            \ini_set('pcre.jit', 0);
        }
        $this->setContainer(Container::getInstance());
        $this->setLogger($this->container->get(LoggerInterface::class));
        $this->input = $this->container->get(Input::class);
    }
    public static function getInstance($sClass)
    {
        $sFullClass = 'JchOptimize\\Core\\Admin\\Ajax\\' . $sClass;
        return new $sFullClass();
    }
    abstract function run();
}
