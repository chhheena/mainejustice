<?php


/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

?>
<div class="search<?php echo $moduleclass_sfx ?>">
	<form action="<?php echo Route::_('index.php');?>" method="post">
		<?php
			//$output = '<label for="mod-search-searchword" class="element-invisible">' . $label . '</label> ';
			$output = '';
			$output .= '<input name="searchword" maxlength="' . $maxlength . '"  class="mod-search-searchword inputbox search-query" type="text" size="' . $width . '" placeholder="' . $text . '" />';

			echo $output;
		?>
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
	</form>
</div>
