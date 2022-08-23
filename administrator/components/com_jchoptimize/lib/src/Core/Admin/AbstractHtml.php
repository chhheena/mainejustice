<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Core\Admin;

use JchOptimize\Core\Interfaces\Html;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
abstract class AbstractHtml implements Html, LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;
    /**
     * JCH Optimize settings
     *
     * @var Registry
     */
    protected $params;
    /**
     * Http client transporter object
     *
     * @var ClientInterface|null
     */
    protected $http;
    public function __construct(Registry $params, ?ClientInterface $http)
    {
        $this->params = $params;
        $this->http = $http;
    }
}
