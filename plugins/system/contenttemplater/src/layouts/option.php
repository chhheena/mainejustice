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

<a class="hasPopover"
   data-trigger="hover"
   title="<?php echo $displayData['text']; ?>"
   data-content="<?php echo $displayData['description']; ?>"
   href="javascript:"
   onclick="<?php echo $displayData['onclick']; ?>"
>
	<?php echo $displayData['image'] . $displayData['text']; ?>
</a>
