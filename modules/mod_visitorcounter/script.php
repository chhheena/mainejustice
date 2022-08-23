<?php

/**
 * @Copyright
 *
 * @package    Visitorcounter Pro - VCNT for Joomla! 3
 * @author     Viktor Vogel <admin@kubik-rubik.de>
 * @version    3.3.2-FREE - 2020-02-29
 * @link       https://kubik-rubik.de/vcnt-visitorcounter
 *
 * @license    GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') || die('Restricted access');

use Joomla\CMS\Factory;

class Mod_VisitorcounterInstallerScript
{
    const MIN_VERSION_JOOMLA = '3.9.0';
    const MIN_VERSION_PHP = '7.3.0';

    /**
     * Name of extension that is used in the error message
     *
     * @var string
     */
    protected $extensionName = 'Visitorcounter';

    /**
     * Checks compatibility in the preflight event
     *
     * @param $type
     * @param $parent
     *
     * @return bool
     * @throws Exception
     */
    public function preflight($type, $parent)
    {
        if (!$this->checkVersionJoomla()) {
            return false;
        }

        if (!$this->checkVersionPhp()) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the Joomla! version meets the requirement
     *
     * @return bool
     * @throws Exception
     */
    private function checkVersionJoomla()
    {
        // Using deprecated JVersion, JFactory and JText classes to avoid exceptions in old Joomla! versions
        $version = new JVersion();

        if (!$version->isCompatible(self::MIN_VERSION_JOOMLA)) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('KRJE_FREE_ERROR_JOOMLA_VERSION', $this->extensionName, self::MIN_VERSION_JOOMLA), 'error');

            return false;
        }

        return true;
    }

    /**
     * Checks whether the PHP version meets the requirement
     *
     * @return bool
     * @throws Exception
     */
    private function checkVersionPhp()
    {
        if (!version_compare(phpversion(), self::MIN_VERSION_PHP, 'ge')) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('KRJE_FREE_ERROR_PHP_VERSION', $this->extensionName, self::MIN_VERSION_PHP), 'error');

            return false;
        }

        return true;
    }

    /**
     * Creates required tables to store the visitorcounter data automatically in the postflight hook
     *
     * @param $type
     * @param $parent
     */
    public function postflight($type, $parent)
    {
        $db = Factory::getDbo();

        // Max. IPv6 string length is 45 - IPv4-mapped IPv6
        $query = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__vcnt') . " (" . $db->quoteName('tm') . " INT NOT NULL, " . $db->quoteName('ip') . " VARCHAR(45) NOT NULL DEFAULT '0.0.0.0')";
        $db->setQuery($query);
        $db->execute();

        $query = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__vcnt_pc') . " (" . $db->quoteName('cnt') . " INT NOT NULL DEFAULT '0')";
        $db->setQuery($query);
        $db->execute();

        $query = "SELECT count(*) FROM " . $db->quoteName('#__vcnt_pc');
        $db->setQuery($query);
        $numRows = $db->loadResult();

        if (empty($numRows)) {
            $query = "INSERT INTO " . $db->quoteName('#__vcnt_pc') . " VALUES(0)";
            $db->setQuery($query);
            $db->execute();
        }
    }
}
