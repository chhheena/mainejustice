<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');

class PhocaDownloadCpControllerPhocaDownloadRaFile extends JControllerAdmin
{
	protected	$option 		= 'com_phocadownload';
	
	public function &getModel($name = 'PhocaDownloadRaFile', $prefix = 'PhocaDownloadCpModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function saveOrderAjax() {
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		\Joomla\Utilities\ArrayHelper::toInteger($pks);
		\Joomla\Utilities\ArrayHelper::toInteger($order);
		$model = $this->getModel();
		$return = $model->saveorder($pks, $order);
		if ($return) { echo "1";}
		JFactory::getApplication()->close();
	}
}
?>