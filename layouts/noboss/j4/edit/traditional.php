<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	Layouts
 * @author			No Boss Technology <contato@noboss.com.br>
 * @copyright		Copyright (C) 2021 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/**
 * Layout tradicional para utilizar como tmpl de views de edicao de registros
 * 
 * ORIENTACOES DE USO:
 * 
 * Para chamar esse layout, utilize o codigo abaixo:
 *      echo JLayoutHelper::render('noboss.j4.edit.traditional', $this);
 * 
 * No arquivo de view siga o modelo da No Boss para garantir que tenha todas variaveis declaradas que sao necessarias para o funcionamento deste modelo de tmpl.
 * 
 * Mais informacoes podem ser obtidas em (TODO: colocar link aqui)
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$this->document = Factory::getDocument();

// Obtem o formulario
$form = $displayData->getForm();

// Scripts de validacao do form
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

?>

<form name="<?php echo "form-" . $displayData->viewName; ?>" id="<?php echo "form-" . $displayData->viewName; ?>" class="form-validate" method="post" action="<?php echo Route::_($displayData->actionForm); ?>">
    <?php
    // Exibe campos de titulo e alias
    echo LayoutHelper::render('noboss.edit.title_alias', $displayData);
    ?>

    <div class="main-card">
		<?php
        echo HTMLHelper::_('uitab.startTabSet',  $displayData->viewName, array('active' => $displayData->defaultFieldSetName)); 
        
        // Renderiza os campos que nao devem ser exibidos e que estao no fieldset 'hidden'
        echo '<div style="display: none;">'.$form->renderFieldset('hidden').'</div>';

        // Percorre todos fieldsets
        foreach ($form->getFieldsets() as $fieldSet):
            // Fieldset esta entre os a serem ignorados na exibicao automatica
            if(in_array($fieldSet->name, $displayData->fieldsetsIgnore)){
                continue;
            }

            // Fieldset esta setado para ser exibido por blocos
            if((!empty($fieldSet->breakblock)) && ($fieldSet->breakblock == 1)){
                echo LayoutHelper::render('noboss.j4.edit.fields_block', array('form' => $form, 'fieldset' => $fieldSet, 'viewName' => $displayData->viewName));
                continue;
            }

            // Inicia aba
            echo HTMLHelper::_('uitab.addTab', $displayData->viewName, $fieldSet->name, Text::_($fieldSet->label), true);
            ?>
            <div class="row">
                <?php // Se for a aba default, declara classe col-lg-9 p/ poder exibir coluna de detalhes na direita. Demais casos declara como col-lg-12 que ocupa toda largura ?>
                <div class="col-lg-<?php echo (($displayData->defaultFieldSetName == $fieldSet->name) && (!empty($form->getFieldSet('details')))) ? '9' : '12'; ?>">
		            <fieldset class="form-grid">
                        <?php
                        // Carrega description da aba (caso tenha)
                        echo LayoutHelper::render('noboss.edit.component_description', $fieldSet->description);
                        
                        // Percorre cada field
                        foreach ($form->getFieldSet($fieldSet->name) as $field){

                            // Eh field de 'Atribuicao de menus' de modulo
                            if(!empty($field->getAttribute("module"))){
                                // Field de selecao de menus
                                if($field->getAttribute("module") == 'assigment'){
                                    // Obtem o nome do campo p/ usar em seguida no proximo field
                                    $this->assignmentName = $field->getAttribute("name");
                                    continue;
                                }
                                // Field que guarda os menus selecionados
                                elseif($field->getAttribute("module") == 'assigned'){
                                    // Obtem o nome do campo
                                    $this->assignedName = $field->getAttribute("name");
                                    $this->item = $displayData->get('Item');
                                    // Carrega arquivo que ira exibir o campo
                                    require JPATH_ROOT."/libraries/noboss/forms/fields/nobossmodulesposition/assignment.php";
                                    continue;
                                }
                            }

                            // Carrega o campo
                            echo $field->renderField();
                        }
                        ?>
                    </fieldset>
	            </div>
                <?php
                // Aba default: carrega coluna na direita com campos de detalhes
                if (($displayData->defaultFieldSetName == $fieldSet->name) && (!empty($form->getFieldSet('details')))){
                    ?>
                    <div class="col-lg-3">
                        <fieldset class="form-vertical">
                            <?php
                            // Carrega campos do fieldset details
                            echo $form->renderFieldset('details');
                            ?>
                        </fieldset>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            // Encerra aba
            echo HTMLHelper::_('uitab.endTab');

        endforeach;
             
        echo HTMLHelper::_('uitab.endTabSet'); 
        ?>

        <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
