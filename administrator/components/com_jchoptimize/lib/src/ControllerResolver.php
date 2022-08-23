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
namespace JchOptimize;

use Joomla\Input\Input;
use Psr\Container\ContainerInterface;
class ControllerResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Input
     */
    private $input;
    public function __construct(ContainerInterface $container, Input $input)
    {
        $this->container = $container;
        $this->input = $input;
    }
    public function resolve()
    {
        $controller = $this->getController();
        if ($this->container->has($controller)) {
            \call_user_func([$this->container->get($controller), 'execute']);
        } else {
            throw new \InvalidArgumentException(\sprintf('Cannot resolve controller: %s', $controller));
        }
    }
    private function getController()
    {
        return $this->input->get('view', 'ControlPanel');
    }
}
