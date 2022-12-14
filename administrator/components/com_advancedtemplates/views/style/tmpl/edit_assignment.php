<?php
/**
 * @package         Advanced Template Manager
 * @version         4.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\ShowOn as RL_ShowOn;

jimport('joomla.filesystem.file');

$this->config->show_assignto_groupusers = (int) (
	$this->config->show_assignto_usergrouplevels
);


$assignments = [
	'menuitems',
	'homepage',
	'date',
	'languages',
	'content',
	'tags',
	'usergrouplevels',
	'components',
	'templates',
	'urls',
	'devices',
	'os',
	'browsers',
];
foreach ($assignments as $i => $ass)
{
	if ($ass != 'menuitems' && ( ! isset($this->config->{'show_assignto_' . $ass}) || ! $this->config->{'show_assignto_' . $ass}))
	{
		unset($assignments[$i]);
	}
}

$html = [];

$html[] = $this->render($this->assignments, 'assignments');

$html[] = RL_ShowOn::open('home:0', 'jform');

if (count($assignments) > 1)
{
	$html[] = $this->render($this->assignments, 'match_method');
	$html[] = $this->render($this->assignments, 'show_assignments');
}
else
{
	$html[] = '<input type="hidden" name="show_assignments" value="1">';
}

foreach ($assignments as $ass)
{
	$html[] = $this->render($this->assignments, 'assignto_' . $ass);
}

$html[] = RL_ShowOn::close();

echo implode("\n\n", $html);
