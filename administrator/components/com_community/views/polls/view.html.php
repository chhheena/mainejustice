<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewPolls extends JViewLegacy
{
    /**
     * The default method that will display the output of this view which is called by
     * Joomla
     *
     * @param   string template Template file name
     **/
    public function display($tpl = null)
    {
        CAssets::getInstance();
        $document = JFactory::getDocument();

        // Set the titlebar text
        JToolBarHelper::title(JText::_('COM_COMMUNITY_POLLS'), 'polls');

        // Add the necessary buttons
        JToolBarHelper::deleteList(JText::_('COM_COMMUNITY_POLL_DELETION_WARNING'), 'deletePoll', JText::_('COM_COMMUNITY_DELETE'));
        JToolBarHelper::divider();
        JToolBarHelper::publishList('publish', JText::_('COM_COMMUNITY_PUBLISH'));
        JToolBarHelper::unpublishList('unpublish', JText::_('COM_COMMUNITY_UNPUBLISH'));

        // Get required data's
        $polls = $this->get('Polls');
        $categories = $this->get('Categories');
        $pagination = $this->get('Pagination');
        
        // We need to assign the users object to the polls listing to get the users name.
        for ($i = 0; $i < count($polls); $i++) {
            $row =& $polls[$i];
            $row->user = CFactory::getUser($row->creator);
            $row->category = $this->_getCatName($categories, $row->catid);
        }

        $mainframe  = JFactory::getApplication();
        $jinput = $mainframe->input;

        $filter_order = $mainframe->getUserStateFromRequest('com_community.polls.filter_order', 'filter_order', 'a.created', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_community.polls.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $search = $jinput->get('search' ,'', 'STRING');
        
        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        $catHTML = $this->_getCategoriesHTML($categories);

        $this->set('polls', $polls);
        $this->set('categories', $catHTML);
        $this->set('lists', $lists);
        $this->set('search', $search);
        $this->set('pagination', $pagination);

        parent::display( $tpl );
    }

    
    public function _getCategoriesHTML(&$categories)
    {
        $jinput = JFactory::getApplication()->input;
        $category = $jinput->getInt( 'category' , 0 );

        $select = '<select class="no-margin" name="category" onchange="Joomla.submitform();">';
        $select .= ( $category == 0 ) ? '<option value="0" selected="true">' : '<option value="0">';
        $select .= JText::_('COM_COMMUNITY_ALL_CATEGORY') . '</option>';

        for ($i = 0; $i < count( $categories ); $i++) {
            $selected   = ( $category == $categories[$i]->id ) ? ' selected="true"' : '';
            $select .= '<option value="' . $categories[$i]->id . '"' . $selected . '>' . $categories[$i]->name . '</option>';
        }

        $select .= '</select>';

        return $select;
    }


    /**
     * Method to get the publish status HTML
     *
     * @param   object  Field object
     * @param   string  Type of the field
     * @param   string  The ajax task that it should call
     * @return  string  HTML source
     **/
    public function getPublish(&$row, $type ,$ajaxTask)
    {
        $version = new Jversion();
        $currentV = $version->getHelpVersion();
        $class = 'jgrid';
        $alt = $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');
        $state = $row->$type == 1 ? 'publish' : 'unpublish';
        $span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

        if ($currentV >= '0.30') {
            $class = $row->$type == 1 ? 'disabled jgrid': '';
            $span = '<i class="icon-'.$state.'""></i>';
        }

        $href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

        $href .= $span;

        // Check: ACL
        if (!CFactory::getUser()->authorise('community.groupeditstate', 'com_community')) {
            return '<i class="icon-'.$state.'"></i>';
        }

        return $href;
    }

    public function setToolBar()
    {
    }

    private function _getCatName($categories,$id)
    {
        foreach ($categories as $cat) {
            if ($cat->id == $id) {
                return $cat->name;
            }
        }

        return 'No category';
    }

    public function _getStatusHTML()
    {
        $jinput = JFactory::getApplication()->input;
        $status = $jinput->getInt('status' , 2);

        $select = '<select class="no-margin" name="status" onchange="Joomla.submitform();">';
        $statusArray = array(2 => JText::_('COM_COMMUNITY_ALL_STATE'), 0 => JText::_('JUNPUBLISHED'), 1 => JText::_('JPUBLISHED'));

        foreach ($statusArray as $key=>$array) {
            $selected = ($status == $key) ? 'selected="true"' : '';
            $select .='<option value="'.$key.'"'.$selected.' >'.JText::_($array).'</option>';
        }

        $select .= '</select>';

        return $select;
    }
}
