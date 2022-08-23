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
$doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobosssubform.min.js");
// adiciona o css do campo
$doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobosssubform.min.css");
$doc->addStylesheet(JURI::base()."../libraries/noboss/assets/plugins/stylesheets/css/material-icons.css");

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
$identifier = $displayData['field']->identifier;
$htmlButtons = $displayData['field']->htmlButtons;
$colsClass = $displayData['field']->cols_class;

?>

<div class="subform-repeatable-wrapper subform-layout" data-subform-collapse-wrapper>
	<joomla-field-subform class="subform-repeatable" name="<?php echo $name; ?>"
        button-add=".group-add" 
        button-remove=".group-remove" 
        button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
        repeatable-element=".subform-repeatable-group" 
        minimum="<?php echo $min; ?>" 
        maximum="<?php echo $max; ?>">

        <?php if (!empty($buttons['add'])) : ?>
        <div class="btn-toolbar">
            <div class="btn-group">
                <a class="group-add btn button btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>" tabindex="0">
                    <span class="icon-plus icon-white" aria-hidden="true"></span>
                </a>
                <?php echo $htmlButtons; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $fieldIsEditor = false;
        foreach ($forms as $k => $form) :

            $formData = $form->getData();
            $formDataArray = $formData->toArray();

            //extrai o valor do identificador que vem como objeto
            if($identifier !== null){
                $identifier = (array) $identifier;
                $identifier = implode('', $identifier);
            }

            //caso o identificador nao seja valido
            if(!$formData->exists($identifier) && $identifier !== "none"){
                //pega os fields
                $fieldset = $form->getFieldset();
                //para cada field verifica se o tipo eh text ou textarea
                $defaultField = array_filter($fieldset, function($field){
                    //caso seja, retorna o field
                    if(strtolower($field->type) == 'text' || strtolower($field->type) == 'textarea' || strtolower($field->type) == 'nobosseditor'){
                        return $field;
                    }
                });

                //caso nao tenha sido possivel achar um campo de texto
                if(!$defaultField){
                    //seta o identifier como none
                    $identifier = 'none';
                }else{
                    //pega o name do elemento que servira como default
                    $identifier = reset($defaultField)->getAttribute('name');
                }
            }else{
                if($identifier !== "none"){
                    $fieldIsEditor = $form->getField($identifier)->type === 'nobosseditor';
                }
            }          
            
            echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons, 'colsclass' => $colsClass));
        endforeach;
        ?>
        
        <?php if ($multiple) : ?>
		<template class="subform-repeatable-template-section"><?php
			echo trim($this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 'X', 'buttons' => $buttons, 'colsclass' => $colsClass)));
		?></template>
		<?php endif; ?>
	</joomla-field-subform>
    
    <span class='hidden' data-is-editor="<?php echo $fieldIsEditor?>" data-collapse-label='<?php echo $identifier;?>' data-collapse-default-value='<?php echo JText::_('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_COLLAPSE_DEFAULT_VALUE_TEXT'); ?>'></span>
</div>
