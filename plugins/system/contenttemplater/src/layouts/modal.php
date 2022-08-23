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
use Joomla\CMS\Uri\Uri as JUri;

$filter_category = JFactory::getApplication()->getUserState('contenttemplater_catid', '');
$filter_category = JFactory::getApplication()->input->getString('catid', $filter_category);

?>

<div class="header">
	<h1 class="page-title">
		<span class="icon-reglab icon-contenttemplater"></span>
		<?php echo JText::_('INSERT_TEMPLATE'); ?>
	</h1>
</div>

<?php echo $displayData['toolbar'] ?: '<br>'; ?>

<div id="<?php echo $displayData['form_id']; ?>" tabindex="-1" class="contenttemplater-modal">

	<div class="container-fluid container-main">

		<form class="float-right" action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
			<?php if ( ! empty($displayData['categories'])) : ?>
				<select name="catid" onchange="document.adminForm.submit();">
					<option value="">-- <?php echo JText::_('JCATEGORY'); ?> --</option>
					<?php foreach ($displayData['categories'] as $cat) : ?>
						<option value="<?php echo $cat; ?>"<?php echo $filter_category == $cat ? ' selected="selected"' : ''; ?>>
							<?php echo $cat; ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<ul class="list list-striped">
				<li>
					<?php echo implode('</li><li>', $displayData['options']); ?>
				</li>
			</ul>
		</form>

	</div>
</div>
