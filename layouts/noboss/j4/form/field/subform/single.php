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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   JForm   $tmpl             The Empty form for template
 * @var   array   $forms            Array of JForm instances for render the rows
 * @var   bool    $multiple         The multiple state for the form field
 * @var   int     $min              Count of minimum repeating in multiple mode
 * @var   int     $max              Count of maximum repeating in multiple mode
 * @var   string  $name             Name of the input field.
 * @var   string  $fieldname        The field name
 * @var   string  $control          The forms control
 * @var   string  $label            The field label
 * @var   string  $description      The field description
 * @var   array   $buttons          Array of the buttons that will be rendered
 * @var   bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */

// Add script
if ($multiple)
{
	Factory::getDocument()->getWebAssetManager()
		->useScript('webcomponent.field-subform');
}


// Adiciona JS e CSS para customizacao do subform
$doc = JFactory::getDocument();
$doc->addStylesheet(JURI::base()."../libraries/noboss/assets/plugins/stylesheets/css/material-icons.css");

$sublayout = 'section';

?>

<div class="subform-repeatable-wrapper subform-layout" data-subform-single-wrapper>
    <joomla-field-subform class="subform-repeatable" name="<?php echo $name; ?>">
        <?php
        foreach ($forms as $k => $form) :
			echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons)); 
        endforeach;
		?>
    </joomla-field-subform>
</div>
