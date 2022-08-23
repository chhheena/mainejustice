<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

$virtuemart = JPATH_ROOT.DS.'components'.DS.'com_virtuemart';
if (file_exists($virtuemart.DS.'virtuemart.php'))
{
	if(!class_exists('vObject'))require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vobject.php');
	if(!class_exists('VmModel'))require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmmodel.php');
	if(!class_exists('VmTable'))require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmtable.php');

	jimport('joomla.form.formfield');
	defined('DS') or define('DS', DIRECTORY_SEPARATOR);
	if (!class_exists( 'VmConfig' )) require(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');

	if (!class_exists('ShopFunctions'))
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'shopfunctions.php');
	if (!class_exists('VirtueMartModelManufacturer'))
	JLoader::import('manufacturer', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');

	if(!class_exists('TableManufacturers')) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'tables'.DS.'manufacturers.php');
	if (!class_exists( 'VirtueMartModelManufacturer' ))
	JLoader::import( 'manufacturer', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' );

	/**
	 * Supports a modal Manufacturer picker.
	 */
	class JFormFieldVMManufacturer extends JFormField
	{
		var $type = 'VMManufacturer';

		function getInput()
		{
			return self::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}

		function fetchElement($name, $value, &$node, $control_name)
		{
			$doc = JFactory::getDocument();
			$js = "
				jQuery.noConflict();
				jQuery(document).ready(function(){

					jQuery('#jform_params_manufacturerfilter0').click(function(){
						jQuery('#jform_params_manufacturers').attr('disabled', 'disabled');
						jQuery('#jform_params_manufacturers option').each(function() {
							jQuery(this).attr('selected', 'selected');
						});
						jQuery('#jform_params_manufacturers').trigger('liszt:updated');
					});

					jQuery('#jform_params_manufacturerfilter1').click(function(){
						jQuery('#jform_params_manufacturers').removeAttr('disabled');
						jQuery('#jform_params_manufacturers option').each(function() {
							jQuery(this).removeAttr('selected');
						});
						jQuery('#jform_params_manufacturers').trigger('liszt:updated');
					});

					if (jQuery('#jform_params_manufacturerfilter0').attr('checked')) {
						jQuery('#jform_params_manufacturers').attr('disabled', 'disabled');
						jQuery('#jform_params_manufacturers option').each(function() {
							jQuery(this).attr('selected', 'selected');
						});
						jQuery('#jform_params_manufacturers').trigger('liszt:updated');
					}

					if (jQuery('#jform_params_manufacturerfilter1').attr('checked')) {
						jQuery('#jform_params_manufacturers').removeAttr('disabled');
						jQuery('#jform_params_manufacturers').trigger('liszt:updated');
					}

				});
			";
			$doc->addScriptDeclaration($js);

			$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
			$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
			$model = VmModel::getModel('Manufacturer');
			$manufacturers = $model->getManufacturers(true, true, false);

			return JHtml::_('select.genericlist', $manufacturers, $this->name, 'class="inputbox"  size="1" multiple="multiple"', 'virtuemart_manufacturer_id', 'mf_name', $this->value, $this->id);
		}
	}
}
