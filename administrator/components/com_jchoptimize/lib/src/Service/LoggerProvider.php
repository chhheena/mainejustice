<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/wordpress-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Service;

use JchOptimize\Core\Interfaces\MvcLoggerInterface;
use JchOptimize\Log\JoomlaLogger;
use Joomla\CMS\Log\Log;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
class LoggerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias(MvcLoggerInterface::class, LoggerInterface::class)->share(LoggerInterface::class, function (Container $container) : LoggerInterface {
            JoomlaLogger::addLogger(['text_file' => 'com_jchoptimize.logs.php'], Log::ALL, ['com_jchoptimize']);
            return JoomlaLogger::createDelegatedLogger();
        });
    }
}
