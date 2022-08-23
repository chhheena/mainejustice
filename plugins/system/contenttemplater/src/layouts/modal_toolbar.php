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

use Joomla\CMS\Language\Text as JText;

?>

<nav class="navbar">
	<div class="navbar-inner">
		<div class="container-fluid">
			<div class="btn-toolbar" id="toolbar">
				<div class="btn-wrapper" id="toolbar-new">
					<button onclick="window.open('index.php?option=com_contenttemplater&view=item&layout=edit');" class="btn btn-small">
						<span class="icon-save-new"></span>
						<?php echo JText::_('CT_CREATE_NEW_TEMPLATE'); ?>
					</button>
				</div>

				<div class="btn-wrapper" id="toolbar-options">
					<button onclick="window.open('index.php?option=com_contenttemplater');" class="btn btn-small">
						<span class="icon-options"></span>
						<?php echo JText::_('CT_MANAGE_TEMPLATES'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</nav>
