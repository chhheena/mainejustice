<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Layouts
 * @version			1.0
 * @author			No Boss Technology <contato@noboss.com.br>
 * @copyright		Copyright (C) 2021 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

?>

<div class="noboss-subform-single form-grid" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
<?php foreach ($form->getGroup('') as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>

