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
class CommunityViewBackgrounds extends JViewLegacy
{
    public function display($tpl = null)
    {
        if ($this->getLayout() == 'edit') {
            $this->_displayEditLayout($tpl);
            return;
        }

        // Set the titlebar text
        JToolBarHelper::title(JText::_('COM_COMMUNITY_CONFIGURATION_STATUS_BACKGROUNDS'), 'backgrounds');

        // Add the necessary buttons
        JToolBarHelper::addNew('newBackground', JText::_('COM_COMMUNITY_BACKGROUNDS_NEW_BACKGROUND'));
        JToolBarHelper::deleteList(JText::_('COM_COMMUNITY_BACKGROUNDS_DELETION_WARNING'), 'deleteBackground', JText::_('COM_COMMUNITY_DELETE'));
        JToolBarHelper::divider();
        JToolBarHelper::publishList('publish', JText::_('COM_COMMUNITY_PUBLISH'));
        JToolBarHelper::unpublishList('unpublish', JText::_('COM_COMMUNITY_UNPUBLISH'));

        $backgroundsTable = JTable::getInstance('Backgrounds', 'CommunityTable');
        $this->set('backgrounds', $this->prepare($backgroundsTable->getBackgrounds()));

        $mainframe	= JFactory::getApplication();
        $filter_order = $mainframe->getUserStateFromRequest('com_community.backgrounds.filter_order', 'filter_order', 'a.title', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest('com_community.backgrounds.filter_order_Dir', 'filter_order_Dir', '', 'word' );

        // table ordering
        $lists['order_Dir']	= $filter_order_Dir;
        $lists['order'] = $filter_order;

        $this->set('lists', $lists);

        parent::display($tpl);
    }

    public function _displayEditLayout($tpl)
    {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JUri::root() . 'administrator/components/com_community/assets/css/preview.css');

        // Load frontend language file.
        $lang = JFactory::getLanguage();
        $lang->load('com_community', JPATH_ROOT );
        $jinput = JFactory::getApplication()->input;

        // Add the necessary buttons
        JToolBarHelper::back(JText::_('COM_COMMUNITY_BACK'), 'index.php?option=com_community&view=backgrounds');
        JToolBarHelper::divider();
        JToolBarHelper::apply();
        JToolBarHelper::save();

        $background = JTable::getInstance('Backgrounds', 'CommunityTable');
        $background->load($jinput->getInt('backgroundid'));

        // Set the titlebar text
        JToolBarHelper::title(JText::_('COM_COMMUNITY_BACKGROUND_ADD'), 'backgrounds');
        if($background->id) {
            JToolBarHelper::title( JText::_('COM_COMMUNITY_BACKGROUND_EDIT'), 'backgrounds');
            $background->image = $this->getImage($background);
        }

        $scss['colors'] = array('scss-color-text' => $background->textcolor, 'scss-color-placeholder' => $background->placeholdercolor);
        $this->set('scss', $scss);

        $scss_default['colors'] = array('scss-color-text' => '000000', 'scss-color-placeholder' => 'dddddd');
        $this->set('scss_default', $scss_default);

        $post = $jinput->post->getArray();
        $background->bind($post);
        $this->set('background', $background);

        parent::display($tpl);
    }

    public function getImage($background)
    {   
        $filename = "/background_".$background->id.".".$background->image;

        if ($background->custom) {
            if (file_exists(COMMUNITY_STATUS_BACKGROUND_PATH.$filename)) {
                return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH).$filename;
            }
        } else {
            if (file_exists(COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS.$filename)) {
                return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS).$filename;
            }
        }

        return null;
    }

    public function getImageThumb($background)
    {
        $filename = "/background_".$background->id."_thumb.".$background->image;

        if ($background->custom) {
            if (file_exists(COMMUNITY_STATUS_BACKGROUND_PATH.$filename)) {
                return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH).$filename;
            }
        } else {
            if (file_exists(COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS.$filename)) {
                return JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_STATUS_BACKGROUND_PATH_ASSETS).$filename;
            }
        }

        return null;
    }

    /**
     * Method to get the publish status HTML
     *
     * @param	object	Field object
     * @param	string	Type of the field
     * @param	string	The ajax task that it should call
     * @return	string	HTML source
     **/
    public function getPublish(&$row , $type , $ajaxTask)
    {
        $version = new Jversion();
        $currentV = $version->getHelpVersion();
        $class = 'jgrid';
        $alt = $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');
        $state = $row->$type == 1 ? 'publish' : 'unpublish';
        $span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

        if ($currentV >= '0.30') {
            $class = $row->$type == 1 ? 'jgrid': '';
            $span = '<i class="icon-'.$state.'""></i>';
        }

        $href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

        $href .= $span;

        return $href;
    }

    /**
     * Loop through mood array and apply translations and image URLs to titles and descriptions
     *
     * @param $backgrounds
     * @return mixed
     */
    private function prepare($backgrounds)
    {
        // @todo maybe the model should handle the translations?
        $lang = JFactory::getLanguage();
        $lang->load('com_community', JPATH_ROOT);

        foreach ($backgrounds as $id => $background) {
            $backgrounds[$id]->title = JText::_($background->title);

            $backgrounds[$id]->thumb = $this->getImageThumb($background);
            $backgrounds[$id]->image = $this->getImage($background);

            $backgrounds[$id]->description = JText::_($background->description);
        }

        return $backgrounds;
    }

    public function renderField($key)
    {
        $isDefault = false;

        // if the key is empty, load from the defaults
        if(!isset($this->scss['colors'][$key]) && isset($this->scss_default['colors'][$key])) {
            $this->scss['colors'][$key] = $this->scss_default['colors'][$key];
            $isDefault = true;
        }

        // if the value is identical as defaults
        if(isset($this->scss['colors'][$key]) && isset($this->scss_default['colors'][$key])) {
            if($this->scss['colors'][$key] == $this->scss_default['colors'][$key]) $isDefault = true;
        }

        // if both values are empty, means there is no default
        if(!isset($this->scss['colors'][$key]) && !isset($this->scss_default['colors'][$key])) {
            $isDefault=true;
        }
        ?>
        <input type="hidden" class="default" id="default-<?php echo $key;?>" value="<?php echo (isset($this->scss_default['colors'][$key])) ? $this->scss_default['colors'][$key] : '';?>" />
        <input type="text" maxlength="6" value="<?php

        echo isset($this->scss['colors'][$key]) ? $this->scss['colors'][$key] : "";

        ?>"  id="<?php

        echo $key;

        ?>" name="scss[<?php

        echo $key;

        ?>]" class="color resettable {required:false}">

            <a href="#"
               class="reset"
               id="reset-<?php echo $key;?>"
               style="display:<?php echo ($isDefault) ? "none" : "inline"; ?>">
                <?php echo JText::_('COM_COMMUNITY_THEME_COLORS_RESET_FIELD'); ?>
            </a>
    <?php
    }
}