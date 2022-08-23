<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

include_once JPATH_ROOT.'/components/com_community/libraries/core.php';
include_once JPATH_ROOT.'/components/com_community/libraries/userpoints.php';

if (!class_exists('plgSystemguruuserpoints')) {
    class plgSystemguruuserpoints extends JPlugin
    {
        public function __construct($subject, $config)
        {
            parent::__construct($subject, $config);
        }

        public function onAfterGuruCourseCreated($id = null)
        {
            if ($id) {
                CuserPoints::assignPoint('guru.create');
            }

            return true;
        }

        public function onAfterGuruCourseDeleted($id = null)
        {
            if ($id) {
                CuserPoints::assignPoint('guru.delete');
            }

            return true;
        }

        public function onAfterGuruCourseBuy($id = null)
        {
            if ($id) {
                CuserPoints::assignPoint('guru.purchase');
            }

            return true;
        }
    }
}