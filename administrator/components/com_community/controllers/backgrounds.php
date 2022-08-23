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

jimport( 'joomla.application.component.controller' );

class CommunityControllerBackgrounds extends CommunityController
{
    public function __construct()
    {
        parent::__construct();

        $this->registerTask('publish' , 'savePublish');
        $this->registerTask('unpublish' , 'savePublish');
    }

    public function display( $cachable = false, $urlparams = array() )
    {
        CommunityLicenseHelper::_();
        $jinput = JFactory::getApplication()->input;
        $viewName = $jinput->get( 'view' , 'community' );
        $layout = $jinput->get( 'layout' , 'default' );
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView($viewName, $viewType);
        $model = $this->getModel($viewName ,'CommunityAdminModel');

        if ($model) {
            $view->setModel($model, $viewName);
        }

        $view->setLayout($layout);
        $view->display();
    }

    public function ajaxTogglePublish($id , $type, $viewName = false)
    {
        CommunityLicenseHelper::_();

        return parent::ajaxTogglePublish($id, $type, 'backgrounds');
    }

    public function ajaxReorder()
    {
        CommunityLicenseHelper::_();
        $message = array('success' => 1);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $backgroundids = $jinput->request->get('cid', '', 'ARRAY') ;

        $i = 0;
        $background = JTable::getInstance('Backgrounds', 'CommunityTable');

        if (sizeof($backgroundids)) {
            foreach($backgroundids as $backgroundid) {
                if ($background->load($backgroundid)) {
                    $background->ordering = $i++;
                    $background->store();
                }
            }
        } else {
            $message['success'] = 0;
        }

        echo json_encode($message);

        return;
    }

    public function deleteBackground()
    {   
        CommunityLicenseHelper::_();
        $background = JTable::getInstance('Backgrounds', 'CommunityTable');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $id = $jinput->post->get('cid' , '', 'NONE');

        if (empty($id)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
            return false;
        }

        $skipped = 0;
        $deleted = 0;

        foreach ($id as $data) {
            $background->load($data);
            
            if ($background->custom) {
                $imagePath = COMMUNITY_STATUS_BACKGROUND_PATH;
                $ext = $background->image;
                $mainImage = $imagePath.'/background_'.$background->id.".".$ext;
                $newImage = $imagePath.'/background_'.$background->id."_new.".$ext;
                $thumbImage = $imagePath.'/background_'.$background->id."_thumb.".$ext;

                if ($background->delete($id)) {
                    $deleted++;

                    if (JFile::exists($mainImage)) {
                        JFile::delete($mainImage);
                    }

                    if (JFile::exists($newImage)) {
                        JFile::delete($newImage);
                    }

                    if (JFile::exists($thumbImage)) {
                        JFile::delete($thumbImage);
                    }
                }
            } else {
                $skipped++;
            }
        }

        $message = JText::sprintf(JText::_('COM_COMMUNITY_BACKGROUNDS_DELETED'), $deleted);

        if ($skipped) $message.=JText::sprintf(JText::_('COM_COMMUNITY_BACKGROUNDS_DELETION_SKIPPED'), $skipped);

        $mainframe	= JFactory::getApplication();
        $this->setRedirect( 'index.php?option=com_community&view=backgrounds', $message, 'message');
    }

    public function apply()
    {
        CommunityLicenseHelper::_();
        JSession::checkToken() or jexit( JText::_('COM_COMMUNITY_INVALID_TOKEN'));
        $mainframe = JFactory::getApplication();
        
        $background = $this->store();

        $this->setRedirect('index.php?option=com_community&view=backgrounds&layout=edit&backgroundid='.$background->id, $background->message, 'message');
    }

    public function save()
    {
        CommunityLicenseHelper::_();
        JSession::checkToken() or jexit( JText::_('COM_COMMUNITY_INVALID_TOKEN'));
        $mainframe = JFactory::getApplication();
        
        $background = $this->store();
        
        $this->setRedirect('index.php?option=com_community&view=backgrounds', $background->message, 'message');
    }

    public function store()
    {
        CommunityLicenseHelper::_();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if (JString::strtoupper($jinput->getMethod()) != 'POST') {
            return $this->setRedirect('index.php?option=com_community&view=backgrounds' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ), 'error');
        }

        $background = JTable::getInstance('Backgrounds', 'CommunityTable');
        $background->load($jinput->getInt('backgroundid'));

        $isNew = $background->id < 1;

        $background->title = $jinput->post->get('title', '', 'STRING');
        $background->published = $jinput->post->get('published', '', 'NONE');
        $background->description = $jinput->post->get('description', '', 'STRING');
        
        if ($isNew) {
            $background->custom = 1;

            // re-order
            $backgroundsTable = JTable::getInstance('Backgrounds', 'CommunityTable');
            $backgroundids = $backgroundsTable->getBackgrounds();

            $i = 1;
            foreach($backgroundids as $backgroundid) {
                if ($backgroundsTable->load($backgroundid->id)) {
                    $backgroundsTable->ordering = $i++;
                    $backgroundsTable->store();
                }
            }
        }

        $scss = $jinput->post->get('scss',null,'array');
        $background->textcolor = $scss['scss-color-text'];
        $background->placeholdercolor = $scss['scss-color-placeholder'];
        
        // handle image upload
        $backgroundImage = $jinput->files->get('background_image', '', 'NONE');

        if (!empty($backgroundImage['tmp_name']) && isset($backgroundImage['name']) && !empty($backgroundImage['name'])) {
            $imagePath = COMMUNITY_STATUS_BACKGROUND_PATH;

            if (!JFolder::exists($imagePath)) {
                JFolder::create($imagePath);
            }

            //check the file extension first and only allow jpg or png
            $ext = strtolower(pathinfo($backgroundImage['name'], PATHINFO_EXTENSION));

            if (!in_array( $ext, array('jpeg', 'jpg','png')) || ($backgroundImage['type'] != 'image/png' && $backgroundImage['type'] != 'image/jpeg')) {
                return $this->setRedirect('index.php?option=com_community&view=backgrounds&layout=edit&id=' . $element, JText::_('COM_COMMUNITY_BACKGROUNDS_ERROR_IMAGE_TYPE'), 'error' );
            } else {
                $background->image=$ext;
                $background->store();
            }

            //check if existing image exist, if yes, delete it
            $finalPath = $imagePath.'/background_'.$background->id.".".$ext;

            if (file_exists($finalPath)) {
                unlink($finalPath);
            }

            //move the tmp image to the actual path
            move_uploaded_file($backgroundImage['tmp_name'], $finalPath);

            require(JPATH_ROOT."/components/com_community/helpers/image.php");
            CImageHelper::resizeProportional($finalPath, $finalPath, "image/$ext", 900);

            //create thumb
            $storageThumbnail = $imagePath.'/background_'.$background->id."_thumb.".$ext;
            copy($finalPath, $storageThumbnail);
            CImageHelper::resizeProportional($storageThumbnail, $storageThumbnail, "image/$ext", 64, 64);
        }

        $background->store();

        $background->message = $isNew ? JText::_('COM_COMMUNITY_BACKGROUNDS_CREATED') : JText::_('COM_COMMUNITY_BACKGROUNDS_UPDATED');
        
        return $background;
    }
}