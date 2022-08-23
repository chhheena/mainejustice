<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	Layouts
 * @author			No Boss Technology <contato@noboss.com.br>
 * @copyright		Copyright (C) 2021 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/* Layout para exibir os campos de titulo e alias (caso definido) no topo de um componente
    - Esse codigo eh baseado no layouts\joomla\edit\title_alias.php do Joomla que adaptamos para permitir definir o name dos dois campos (o joomla fixava)
    
    - Formato correto para chamar esse layout:
        // Exibe campos de titulo e alias
        $this->fieldName = 'name_faqs_group';
        $this->fieldAlias = ''; // Soh prencher qnd tiver campo de alias
        echo JLayoutHelper::render('noboss.edit.title_alias', $this);
*/

$form = $displayData->getForm();

if((empty($displayData->fieldName)) && (empty($displayData->fieldAlias))){
    return;
}

// Joomla 4
if(version_compare(JVERSION, '4', '>=')){
?>
    <div class="row title-alias form-vertical mb-3">
        <?php
        if(!empty($displayData->fieldName)){
        ?>
            <div class="col-12 col-md-6">
                <?php echo $form->renderField($displayData->fieldName); ?>
            </div>
        <?php
        }
        if(!empty($displayData->fieldAlias)){
        ?>
            <div class="col-12 col-md-6">
                <?php echo $form->renderField($displayData->fieldAlias); ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}
// Joomla 3
else {
?>
    <div class="form-inline form-inline-header">
        <?php
        if(!empty($displayData->fieldName)){
            echo $form->renderField($displayData->fieldName);
        }
        if(!empty($displayData->fieldAlias)){
            echo $form->renderField($displayData->fieldAlias);
        }
        ?>
    </div>
<?php
}
