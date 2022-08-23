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

use Joomla\CMS\{Language\Text, Factory, Helper\ModuleHelper};

require_once 'helper.php';

$start = new ModVisitorcounterHelper();
$start->countRequest($params);

$cleanDb = (bool)$params->get('cleanDb', true);

if ($cleanDb) {
    $start->cleanDatabase();
}

$whoisonline = (int)$params->get('whoisonline', 0);
$whoisonlineLinknames = (int)$params->get('whoisonlineLinknames', 0);
$whoisonlineSession = (int)$params->get('whoisonlineSession', 10);

if (!empty($whoisonline)) {
    $usersOnline = $start->whoIsOnline($whoisonlineSession);

    if (!empty($whoisonlineLinknames)) {
        $itemId = $start->getItemId($whoisonlineLinknames);
    }
}

$showAllowedUser = $start->showAllowedUser($params);

if ($showAllowedUser == 1) {
    $today = $params->get('today', Text::_('MOD_VISITORCOUNTER_TODAY'));
    $yesterday = $params->get('yesterday', Text::_('MOD_VISITORCOUNTER_YESTERDAY'));
    $week = $params->get('week', Text::_('MOD_VISITORCOUNTER_WEEK'));
    $month = $params->get('month', Text::_('MOD_VISITORCOUNTER_MONTH'));
    $all = $params->get('all', Text::_('MOD_VISITORCOUNTER_ALL'));
    $showToday = (bool)$params->get('showToday', true);
    $showYesterday = (bool)$params->get('showYesterday', true);
    $showWeek = (bool)$params->get('showWeek', true);
    $showMonth = (bool)$params->get('showMonth', true);
    $showAll = (bool)$params->get('showAll', true);
    $linkToProject = (bool)$params->get('linktoproject', true);
    $horizontal = (int)$params->get('horizontal', 0);
    $separator = $params->get('separator', '');
    $horizontalText = $params->get('horizontalText', '');
    $moduleclassSfx = htmlspecialchars($params->get('moduleclassSfx', ''));

    [$allVisitors, $todayVisitors, $yesterdayVisitors, $weekVisitors, $monthVisitors] = $start->getData($params);
    Factory::getDocument()->addStyleSheet('modules/mod_visitorcounter/css/visitorcounter.css');

    require ModuleHelper::getLayoutPath('mod_visitorcounter', 'default');
}
