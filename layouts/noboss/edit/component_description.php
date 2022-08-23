<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	Layouts
 * @author			No Boss Technology <contato@noboss.com.br>
 * @copyright		Copyright (C) 2021 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/* Layout para exibir um description em cada fieldset do componente (similar ao que eh feito nos modulos do Joomla)

    - Formato correto para chamar esse layout:
        echo JLayoutHelper::render('noboss.edit.component_description', $fieldSet->description);
*/

use Joomla\CMS\Language\Text;

// Joomla 4
if(version_compare(JVERSION, '4', '>=')){
    // Fieldset possui description
    if(!empty($displayData)){
        ?>
        <legend class='alert alert-info'>
            <span class="icon-info-circle" aria-hidden="true"></span>
            <?php echo Text::_($displayData); ?>
        </legend>
        <?php
    }
}
// Joomla 3
else {
    // Fieldset possui description
    if(!empty($displayData)){
        ?>
        <div class='alert alert-info'>
            <span class='icon-joomla icon-info'></span>
            <?php echo JText::_($displayData); ?>
        </div>
        <?php
    }
}
