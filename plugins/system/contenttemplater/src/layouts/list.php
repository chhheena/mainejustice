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

?>
<div id="<?php echo $displayData['id']; ?>" class="contenttemplater-list">
	<ul role="menu" class="dropdown-menu">
		<li>
			<?php echo implode('</li><li>', $displayData['options']); ?>
		</li>
	</ul>
</div>
