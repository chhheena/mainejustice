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
namespace JchOptimize\Log;

use Joomla\CMS\Log\Log;
class JoomlaLogger extends Log
{
    public static function createDelegatedLogger()
    {
        // Ensure a singleton instance has been created first
        if (empty(static::$instance)) {
            static::setInstance(new static());
        }
        return new \JchOptimize\Log\DelegatingPsrLoggerExtended(static::$instance);
    }
}
