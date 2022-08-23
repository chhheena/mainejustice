<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout as JLayoutFile;

if (empty($field->value))
{
	return;
}

$layout = new JLayoutFile('plg_fields_articles.articles');

$include_paths   = $layout->getIncludePaths();
$include_paths[] = JPATH_SITE . '/plugins/fields/articles/layouts';
$layout->setIncludePaths($include_paths);

$layout_type  = $field->fieldparams->get('layout', 'title');
$value_layout = new JLayoutFile('plg_fields_articles.' . $layout_type);
$value_layout->setIncludePaths($include_paths);

echo $layout->render([
	'context'     => $context,
	'item'        => $item,
	'field'       => $field,
	'layout_type' => $layout_type,
	'layout'      => $value_layout,
]);
