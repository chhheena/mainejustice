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

namespace Facebook\GraphNodes;

use DateTime;

defined('_JEXEC') or die('Restricted access');

/**
 * Birthday object to handle various Graph return formats
 *
 * @package Facebook
 */
class Birthday extends DateTime
{
    /**
     * @var bool
     */
    private $hasDate = false;

    /**
     * @var bool
     */
    private $hasYear = false;

    /**
     * Parses Graph birthday format to set indication flags, possible values:
     *
     *  MM/DD/YYYY
     *  MM/DD
     *  YYYY
     *
     * @link https://developers.facebook.com/docs/graph-api/reference/user
     *
     * @param string $date
     */
    public function __construct($date)
    {
        $parts = explode('/', $date);

        $this->hasYear = count($parts) === 3 || count($parts) === 1;
        $this->hasDate = count($parts) === 3 || count($parts) === 2;

        parent::__construct($date);
    }

    /**
     * Returns whether date object contains birth day and month
     *
     * @return bool
     */
    public function hasDate()
    {
        return $this->hasDate;
    }

    /**
     * Returns whether date object contains birth year
     *
     * @return bool
     */
    public function hasYear()
    {
        return $this->hasYear;
    }
}
