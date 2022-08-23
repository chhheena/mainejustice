<?php
/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */

namespace Facebook\PersistentData;

use InvalidArgumentException;

defined('_JEXEC') or die('Restricted access');

class PersistentDataFactory
{
    private function __construct()
    {
        // a factory constructor should never be invoked
    }

    /**
     * PersistentData generation.
     *
     * @param PersistentDataInterface|string|null $handler
     *
     * @throws InvalidArgumentException If the persistent data handler isn't "session", "memory", or an instance of Facebook\PersistentData\PersistentDataInterface.
     *
     * @return PersistentDataInterface
     */
    public static function createPersistentDataHandler($handler)
    {
        if (!$handler) {
            return session_status() === PHP_SESSION_ACTIVE
                ? new FacebookSessionPersistentDataHandler()
                : new FacebookMemoryPersistentDataHandler();
        }

        if ($handler instanceof PersistentDataInterface) {
            return $handler;
        }

        if ('session' === $handler) {
            return new FacebookSessionPersistentDataHandler();
        }
        if ('memory' === $handler) {
            return new FacebookMemoryPersistentDataHandler();
        }

        throw new InvalidArgumentException('The persistent data handler must be set to "session", "memory", or be an instance of Facebook\PersistentData\PersistentDataInterface');
    }
}
