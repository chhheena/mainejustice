<?php
/**
 * @package         Content Templater
 * @version         10.2.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

$user    = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
$contact = (object) [];

$db         = JFactory::getDbo();
$table_name = $db->getPrefix() . 'contact_details';

if (in_array($table_name, $db->getTableList()))
{
	$query = 'SHOW FIELDS FROM ' . $db->quoteName($table_name);
	$db->setQuery($query);
	$columns = $db->loadColumn();

	if (in_array('misc', $columns))
	{
		$query = $db->getQuery(true)
			->select('c.misc')
			->from('#__contact_details as c')
			->where('c.user_id = ' . (int) $user->id);
		$db->setQuery($query);
		$contact = $db->loadObject();
	}
}
?>
<div class="container-fluid">

	<div class="alert alert-danger">
		<?php echo JText::_('RL_ONLY_AVAILABLE_IN_PRO'); ?>
	</div>

	<p><?php echo JText::_('CT_DYNAMIC_TAGS_DESC'); ?></p>

	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('RL_INPUT_SYNTAX'); ?></th>
				<th class="left">
					<span><?php echo JText::_('JGLOBAL_DESCRIPTION'); ?></span></th>
				<th class="left">
					<span><?php echo JText::_('RL_OUTPUT_EXAMPLE'); ?></span></th>
				<th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="font-family:monospace">[[user:id]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_USER_ID'); ?></td>
				<td><?php echo $user->id; ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[user:username]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_USER_USERNAME'); ?></td>
				<td><?php echo $user->username; ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[user:name]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_USER_NAME'); ?></td>
				<td><?php echo $user->name; ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[user:...]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_USER_OTHER'); ?></td>
				<td><?php echo $contact->misc ?? ''; ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[article:id]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_ID'); ?></td>
				<td>123</td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[article:title]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_TITLE'); ?></td>
				<td>My Article</td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[article:&#8230;]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_ARTICLE_OTHER'); ?></td>
				<td>my-article</td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[date:...]]</td>
				<td><?php echo JText::sprintf('RL_DYNAMIC_TAG_DATE', '<a href="http://www.php.net/manual/function.strftime.php" target="_blank">', '</a>', '<span style="font-family:monospace">[[date: %A, %d %B %Y]]</span>'); ?></td>
				<td><?php echo strftime('%A, %d %B %Y'); ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[random:...-...]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_RANDOM'); ?></td>
				<td><?php echo rand(0, 100); ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[text:MY_STRING]]</td>
				<td><?php echo JText::_('RL_DYNAMIC_TAG_TEXT'); ?></td>
				<td><?php echo JText::_('RL_MY_STRING'); ?></td>
			</tr>
			<tr>
				<td style="font-family:monospace">[[template:...]]</td>
				<td><?php echo JText::_('CT_DYNAMIC_TAG_TEMPLATE'); ?></td>
				<td><?php echo JText::_('CT_DYNAMIC_TAG_TEMPLATE_OUTPUT'); ?></td>
			</tr>
		</tbody>
	</table>
</div>
