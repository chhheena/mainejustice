<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');

class CAdvanceSearch
{
	var $_filter = null;
	var $_data = null;
	var $_pagination = null;

	static public function &getFields($profileType = 0)
	{
		$model = CFactory::getModel('profile');

        if($profileType){
            $fields = $model->getAllFields( array('published'=>'1') , $profileType );
        }else{
            $fields =  $model->getAllFields(array('searchable' => '1'));
        }


		JFactory::getLanguage()->load(COM_USER_NAME, JPATH_ROOT);
		// we need to get the user name / email seprately as these data did not
		// exists in custom profile fields.
		$config = CFactory::getConfig();
		$nameOptGroup = new stdClass();
		$nameOptGroup->type = "group";
		$nameOptGroup->published = 1;
		$nameOptGroup->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME');
		$nameOptGroup->visible = 1;

		$fields[JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME')] = $nameOptGroup;

		$obj = new stdClass();
		$obj->type = "text";
		$obj->searchable = true;
		$obj->published = 1;
		$obj->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_NAME');
		$obj->visible = 1;
		$obj->fieldcode = "username";
		$fields[$nameOptGroup->name]->fields[] = $obj;

		//@since 4.3 Radius search field
		if($config->get('advanced_search_radius') && CMapsHelper::mapSetup()){
			$obj = new stdClass();
			$obj->fieldcode = "FIELD_RADIUS_SEARCH";
			$obj->visible = 1;
			$obj->type = "select";
			$obj->name = JText::_('COM_COMMUNITY_SEARCH_RADIUS');
			$obj->searchable = true;
			$obj->published = 1;

			switch($config->get('advanced_search_units')){
				case 'metric':
					//list in kilometer
					$obj->options = array('10 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'), '20 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'), '50 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'),'100 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'),'200 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'),'500 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_METRIC'));
					break;
				default:
					//imperial
					$obj->options = array('10 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'), '20 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'), '50 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'),'100 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'),'200 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'),'500 '.JText::_('COM_COMMUNITY_SORT_BY_DISTANCE_IMPERIAL'));
			}

			$selectName = JText::_('COM_COMMUNITY_PROFILE');
			$fields[$selectName] = new stdClass;
			$fields[$selectName]->fields = array();
			$fields[$selectName]->fields[] = $obj;
			$fields[$selectName]->published = 1;
			$fields[$selectName]->visible = 1;
		}


		if ($config->get('privacy_search_email') != 2) {
			$obj = new stdClass();
			$obj->type = "email";
			$obj->searchable = true;
			$obj->published = 1;
			$obj->name = JText::_('COM_COMMUNITY_ADVANCEDSEARCH_EMAIL');
			$obj->visible = 1;
			$obj->fieldcode = "useremail";
			$fields[$nameOptGroup->name]->fields[] = $obj;
		}

        if($config->get('profile_multiprofile') && $profileType == 0){
            //lets get the available profile
            $profileModel = CFactory::getModel('Profile');
            $profiles = $profileModel->getProfileTypes();
            if($profiles){
                $options = array();
                foreach($profiles as $profile){
                    $options[$profile->id] = $profile->name;
                }
                $obj = new stdClass();
                $obj->type = "select";
                $obj->searchable = true;
                $obj->published = 1;
                $obj->name = JText::_('COM_COMMUNITY_PROFILE_TYPE');
                $obj->visible = 1;
                $obj->fieldcode = "FIELD_PROFILE_ID_SPECIAL";
                $obj->options = $options;
                $selectName = JText::_('COM_COMMUNITY_PROFILE');
				
				if (empty($fields[$selectName])) {
					$fields[$selectName] = new stdClass;
					$fields[$selectName]->fields = array();
				}
                $fields[$selectName]->fields[] = $obj;
                $fields[$selectName]->published = 1;
                $fields[$selectName]->visible = 1;
            }
        }

        return $fields;
	}

	public function &getFieldList($fieldId)
	{
		$model = CFactory::getModel('search');
		$fieldList = $model->getFieldList($fieldId);
		return $fieldList;
	}

	static public function getResult($filter = array(), $join = 'and', $avatarOnly = '', $sorting = '', $profileType = 0)
	{
		$model = CFactory::getModel('search');
		$result = $model->getAdvanceSearch($filter, $join, $avatarOnly, $sorting, $profileType);
		$pagination = $model->getPagination();

		$obj = new stdClass();
		$obj->result = $result;
		$obj->pagination = $pagination;
		$obj->operator = $join;

		return $obj;
	}

	public function setFilter()
	{
	}

	/**
	 * method used to return the required condition selection.
	 * param - field type - string
	 * return - assoc array
	 */
	public function &getFieldCondition($type)
	{
		$cond = array();

		switch ($type) {
			case 'date':
				$cond = array(
					'between' => JText::_('COM_COMMUNITY_CUSTOM_BETWEEN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL'),
					'lessthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_LESS_THAN_OR_EQUAL'),
					'greaterthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_GREATER_THAN_OR_EQUAL')
				);
				break;
			case 'birthdate':
				$cond = array(
					'between' => JText::_('COM_COMMUNITY_CUSTOM_BETWEEN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'lessthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_LESS_THAN_OR_EQUAL'),
					'greaterthanorequal' => JText::_('COM_COMMUNITY_CUSTOM_GREATER_THAN_OR_EQUAL')
				);
				break;
			case 'checkbox':
			case 'radio':
			case 'select':
			case 'singleselect':
			case 'list':
				$cond = array(
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL')
				);
				break;
			case 'email':
				$cond = array(
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL')
				);
				break;
			case 'textarea':
			case 'text':
			default:
				$cond = array(
					'contain' => JText::_('COM_COMMUNITY_CUSTOM_CONTAIN'),
					'equal' => JText::_('COM_COMMUNITY_CUSTOM_EQUAL'),
					'notequal' => JText::_('COM_COMMUNITY_CUSTOM_NOT_EQUAL')
				);
				break;
		}

		return $cond;
	}

	/**
	 * Method used to return the current MySQL version that running
	 * return - float
	 */
	static public function getMySQLVersion()
	{
		$db = JFactory::getDBO();

		$query = 'SELECT VERSION()';
		$db->setQuery($query);
		$result = $db->loadResult();

		preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $result, $version);

		if (function_exists('floatval')) {
			return floatval($version[0]);
		} else {
			return doubleval($version[0]);
		}
	}
}
