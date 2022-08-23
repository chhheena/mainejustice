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

/**
 * JomSocial Component Controller
 */
class CommunityControllerPages extends CommunityController
{
    public function __construct()
    {
        parent::__construct();

        $this->registerTask( 'publish' , 'savePublish' );
        $this->registerTask( 'unpublish' , 'savePublish' );
    }

    public function display( $cachable = false, $urlparams = array() )
    {
        $jinput = JFactory::getApplication()->input;

        $viewName   = $jinput->get( 'view' , 'community' );

        // Set the default layout and view name
        $layout     = $jinput->get( 'layout' , 'default' );

        // Get the document object
        $document   = JFactory::getDocument();

        // Get the view type
        $viewType   = $document->getType();

        // Get the view
        $view       = $this->getView( $viewName , $viewType );

        $model      = $this->getModel( $viewName ,'CommunityAdminModel' );

        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();
    }

    public function ajaxTogglePublish( $id , $type, $viewName = false )
    {
        // Send email notification to owner when a page is published.
        $config = CFactory::getConfig();
        $page  = JTable::getInstance( 'Page' , 'CTable' );
        $page->load( $id );

        if( $type == 'published' && $page->published == 0 && $config->get( 'moderatepagecreation' ) )
        {
           $this->notificationApproval($page);
        }

        return parent::ajaxTogglePublish( $id , $type , 'pages' );
    }

    public function ajaxChangePageOwner( $pageId )
    {
        $response   = new JAXResponse();

        $page      = JTable::getInstance( 'Page' , 'CTable' );
        $page->load( $pageId );
        $model          = CFactory::getModel( 'Pages' );

        $page->owner   = JFactory::getUser( $page->ownerid );
        $rows           = $model->getMembers( $page->id , NULL , true , false , true );
        ob_start();
?>
<div class="alert alert-info">
    <?php echo JText::_('COM_COMMUNITY_PAGES_CHANGE_OWNERSHIP');?>
</div>
<form name="editpage" method="post" action="">
<table width="100%">
    <tbody>
        <tr>
            <td class="key"><?php echo JText::_('COM_COMMUNITY_PAGES_OWNER');?></td>
            <td align="left">
                <?php echo $page->owner->name; ?>
            </td>
        </tr>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_PAGES_NEW_OWNER_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_PAGES_NEW_OWNER');?></span></td>
            <td align="left">
                <?php
                if($rows)
                {
                ?>
                <select name="ownerid">
                    <?php
                        foreach( $rows as $row )
                        {
                            $user   = CFactory::getUser( $row->id );
                    ?>
                        <option value="<?php echo $user->id;?>"><?php echo JText::sprintf('%1$s [ %2$s ]' , $user->name , $user->email );?></option>
                    <?php
                        }
                    ?>
                </select>
                <?php
                }
                else
                {
                ?>
                <div><?php echo JText::_('COM_COMMUNITY_PAGES_CHANGE_OWNER_WARN');?></div>
                <?php
                }
                ?>
            </td>
        </tr>
    </tbody>
</table>
<input name="id" value="<?php echo $page->id;?>" type="hidden" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="updatePageOwner" />
<input type="hidden" name="view" value="pages" />
</form>
<?php
        $contents   = ob_get_contents();
        ob_end_clean();

        $response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

        $action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.savePageOwner();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
        $action .= '<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
        $response->addScriptCall( 'cWindowActions' , $action );

        return $response->sendResponse();
    }

    public function ajaxAssignPage( $memberId )
    {
        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
        $response   = new JAXResponse();

        $model      = $this->getModel( 'pages', 'CommunityAdminModel' );
        $pages     = $model->getAllPages();
        $user       = CFactory::getUser( $memberId );
        ob_start();
?>
<form name="assignPage" action="" method="post" id="assignPage">
<div class="alert alert-info">
    <?php echo JText::sprintf('COM_COMMUNITY_PAGE_ASSIGN_MEMBER', $user->getDisplayName() );?>
</div>
<table width="100%">
    <tbody>
        <tr>
            <td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_ASSIGN_PAGES_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_PAGES');?></span></td>
            <td>
                <select name="pageid[]" id="pageid" multiple="true">
                    <!--option value="-1" selected="selected"><?php echo JText::_('COM_COMMUNITY_PAGES_SELECT');?></option-->
                <?php
                    foreach($pages as $row )
                    {
                        $selected = $model->isMember($user->id , $row->id)?'selected="true"':'';
                ?>
                    <option value="<?php echo $row->id;?>" <?php echo $selected?> ><?php echo $row->name;?></option>
                <?php

                    }
                ?>
                </select>
            </td>
        </tr>
    </tbody>
</table>
<div id="page-error-message" style="color: red;font-weight:700;"></div>
<input type="hidden" name="memberid" value="<?php echo $user->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="addmember" />
<input type="hidden" name="view" value="pages" />
<?php
        $contents   = ob_get_contents();
        ob_end_clean();

        $response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

        $action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.saveAssignPage();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
        $action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
        $response->addScriptCall( 'cWindowActions' , $action );
        $response->addScriptCall( 'joms.jQuery("#cwin_logo").html("' . JText::_('COM_COMMUNITY_PAGES_ASSIGN_USER') . '");');
        return $response->sendResponse();
    }

    public function ajaxEditPage( $pageId )
    {
        $response   = new JAXResponse();
        $model      = $this->getModel( 'pagecategories' );
        $categories = $model->getCategories();

        $page      = JTable::getInstance( 'Page' , 'CTable' );
        $page->load( $pageId );

        $requireApproval    = ($page->approvals) ? ' checked="true"' : '';
        $noApproval         = (!$page->approvals) ? '' : ' checked="true"';

        // Escape the output
        $page->name    = CStringHelper::escape($page->name);
        $page->description = CStringHelper::escape($page->description);

        $params = $page->getParams();
        $config = CFactory::getConfig();

        ob_start();
?>
<form name="editpage" action="" method="post" id="editpage">
<div class="alert alert-info">
    <?php echo JText::_('COM_COMMUNITY_PAGES_EDIT_PAGE');?>
</div>
<table cellspacing="0" class="admintable" border="0" width="100%">
    <tbody>
        <tr>
            <td class="key" width="100"><?php echo JText::_('COM_COMMUNITY_PAGES_TITLE'); ?></td>
            <td>
                <input type="text" name="name" value="<?php echo $page->name; ?>" style="width: 200px;" />
            </td>
        </tr>
        <tr>
            <td class="key"><?php echo JText::_('COM_COMMUNITY_AVATAR');?></td>
            <td>
                <img width="90" src="<?php echo $page->getThumbAvatar();?>" style="border: 1px solid #eee;"/>
            </td>
        </tr>
        <tr>
            <td class="key"><?php echo JText::_('COM_COMMUNITY_PUBLISH_STATUS');?></td>
            <td>
                <?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , $page->get('published') ); ?>
            </td>
        </tr>
        <tr>
            <td class="key"><?php echo JText::_('COM_COMMUNITY_CATEGORY');?></td>
            <td>
                <select name="categoryid">
                <?php

                    for( $i = 0; $i < count( $categories ); $i++ )
                    {
                        $selected   = ($page->categoryid == $categories[$i]->id ) ? ' selected="selected"' : '';
                ?>
                        <option value="<?php echo $categories[$i]->id;?>"<?php echo $selected;?>><?php echo $categories[$i]->name;?></option>
                <?php
                    }
                ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="key"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?></td>
            <td>
                <textarea name="description" style="width: 250px;" rows="5"
                    data-wysiwyg="trumbowyg" data-btns="viewHTML,|,bold,italic,underline,|,unorderedList,orderedList,|,link,image"><?php
                        echo $page->description;?></textarea>
            </td>
        </tr>

    <?php if ($config->get('enablephotos') && $config->get('pagephotos')) { ?>
    <?php $photoAllowed = $params->get('photopermission', 1) >= 1; ?>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td class="key">
            <span>
                <?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?>
            </span>
        </td>
        <td>
            <label>
                <input type="checkbox" name="photopermission-admin" class="joms-js--page-photo-flag" style="position:relative;opacity:1" value="1"
                    <?php echo $photoAllowed ? 'checked' : '' ?>> <?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_UPLOAD_ALOW_ADMIN'); ?>
            </label>
            <div class="joms-js--page-photo-setting" style="display:none">
                <label>
                    <input type="checkbox" name="photopermission-member" class="joms-js--page-photo-setting" style="position:relative;opacity:1" value="1"
                        <?php echo $photoAllowed ? '' : ' disabled="disabled"' ?>
                        <?php echo $photoAllowed && $params->get('photopermission') > 1 ? 'checked' : '' ?>
                    > <?php echo JText::_('COM_COMMUNITY_PAGES_PHOTO_UPLOAD_ALLOW_MEMBER'); ?>
                </label>
                <select name="pagerecentphotos">
                    <?php for ($i = 2; $i <= 10; $i += 2) { ?>
                    <option value="<?php echo $i; ?>"
                        <?php echo ($params->get('pagerecentphotos') == $i || ($i == 6 && $params->get('pagerecentphotos')==0)) ? 'selected': ''; ?>
                        ><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
    </tr>
    <?php } ?>

    <?php if ($config->get('enablevideos') && $config->get('pagevideos')) { ?>
    <?php $videoAllowed = $params->get('videopermission', 1) >= 1; ?>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td class="key">
            <span>
                <?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?>
            </span>
        </td>
        <td>
            <label>
                <input type="checkbox" name="videopermission-admin" class="joms-js--page-video-flag" style="position:relative;opacity:1" value="1"
                    <?php echo $videoAllowed ? 'checked' : '' ?>> <?php echo JText::_('COM_COMMUNITY_PAGES_VIDEO_UPLOAD_ALLOW_ADMIN'); ?>
            </label>
            <div class="joms-js--page-video-setting" style="display:none">
                <label>
                    <input type="checkbox" name="videopermission-member" class="joms-js--page-video-setting" style="position:relative;opacity:1" value="1"
                        <?php echo $videoAllowed ? '' : ' disabled="disabled"' ?>
                        <?php echo $videoAllowed && $params->get('videopermission') > 1 ? 'checked' : '' ?>
                    > <?php echo JText::_('COM_COMMUNITY_PAGES_VIDEO_UPLOAD_ALLOW_MEMBER'); ?>
                </label>
                <select name="pagerecentvideos">
                    <?php for ($i = 2; $i <= 10; $i += 2) { ?>
                    <option value="<?php echo $i; ?>"
                        <?php echo ($params->get('pagerecentvideos') == $i || ($i == 6 && $params->get('pagerecentvideos')==0)) ? 'selected': ''; ?>
                        ><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
    </tr>
    <?php } ?>
    
    <?php if ($config->get('enableevents') && $config->get('page_events')) { ?>
    <?php $eventAllowed = $params->get('eventpermission', 1) >= 1; ?>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td class="key">
            <span>
                <?php echo JText::_('COM_COMMUNITY_EVENTS'); ?>
            </span>
        </td>
        <td>
            <label>
                <input type="checkbox" name="eventpermission-admin" class="joms-js--page-event-flag" style="position:relative;opacity:1" value="1"
                    <?php echo $eventAllowed ? 'checked' : '' ?>> <?php echo JText::_('COM_COMMUNITY_PAGE_EVENTS_ADMIN_CREATION'); ?>
            </label>
            <div class="joms-js--page-event-setting" style="display:none">
                <label>
                    <input type="checkbox" name="eventpermission-member" class="joms-js--page-event-setting" style="position:relative;opacity:1" value="1"
                        <?php echo $eventAllowed ? '' : ' disabled="disabled"' ?>
                        <?php echo $eventAllowed && $params->get('eventpermission') > 1 ? 'checked' : '' ?>
                    > <?php echo JText::_('COM_COMMUNITY_PAGE_EVENTS_MEMBERS_CREATION'); ?>
                </label>
                <select name="pagerecentevents">
                    <?php for ($i = 2; $i <= 10; $i += 2) { ?>
                    <option value="<?php echo $i; ?>"
                        <?php echo ($params->get('pagerecentevents') == $i || ($i == 6 && $params->get('pagerecentevents')==0)) ? 'selected': ''; ?>
                        ><?php echo $i; ?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
    </tr>
    <?php } ?>

    <tr><td colspan="2">&nbsp;</td></tr>

    <?php if ($config->get('file_sharing_page')) { ?>
    <?php $filesharingAllowed = $params->get('filesharingpermission') >= 1; ?>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td class="key">
            <span>
                <?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_FILESHARING'); ?>
            </span>
        </td>
        <td>
            <label>
                <input type="checkbox" name="filesharingpermission-admin" class="joms-js--page-filesharing-flag" style="position:relative;opacity:1" value="1"
                    <?php echo $filesharingAllowed ? 'checked' : '' ?>> <?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_ALOW_ADMIN'); ?>
            </label>
            <div class="joms-js--page-filesharing-setting" style="display:none">
                <label>
                    <input type="checkbox" name="filesharingpermission-member" class="joms-js--page-filesharing-setting" style="position:relative;opacity:1" value="1"
                        <?php echo $filesharingAllowed ? '' : ' disabled="disabled"' ?>
                        <?php echo $filesharingAllowed && $params->get('filesharingpermission') > 1 ? 'checked' : '' ?>
                    > <?php echo JText::_('COM_COMMUNITY_PAGES_FILESHARING_ALLOW_MEMBER'); ?>
                </label>
            </div>
        </td>
    </tr>
    <?php } ?>

    <tr><td colspan="2">&nbsp;</td></tr>
    <?php if ($config->get('page_polls')) { ?>
    <?php $pollsAllowed = $params->get('pollspermission') >= 1; ?>
    <tr><td colspan="2">&nbsp;</td></tr>
    <tr>
        <td class="key">
            <span>
                <?php echo JText::_('COM_COMMUNITY_PAGES_RECENT_POLLS'); ?>
            </span>
        </td>
        <td>
            <label>
                <input type="checkbox" name="pollspermission-admin" class="joms-js--page-polls-flag" style="position:relative;opacity:1" value="1"
                    <?php echo $pollsAllowed ? 'checked' : '' ?>> <?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_ALOW_ADMIN'); ?>
            </label>
            <div class="joms-js--page-polls-setting" style="display:none">
                <label>
                    <input type="checkbox" name="pollspermission-member" class="joms-js--page-polls-setting" style="position:relative;opacity:1" value="1"
                        <?php echo $pollsAllowed ? '' : ' disabled="disabled"' ?>
                        <?php echo $pollsAllowed && $params->get('pollspermission') > 1 ? 'checked' : '' ?>
                    > <?php echo JText::_('COM_COMMUNITY_PAGES_POLLS_ALLOW_MEMBER'); ?>
                </label>
            </div>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<input type="hidden" name="id" value="<?php echo $page->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="savepage" />
<input type="hidden" name="view" value="pages" />
<?php
        $contents   = ob_get_contents();
        ob_end_clean();

        $response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

        $action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.savePage();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
        $action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
        $response->addScriptCall( 'cWindowActions' , $action );
        $response->addScriptCall( 'joms.util.wysiwyg.start' );

        return $response->sendResponse();
    }

    public function updatePageOwner()
    {
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $page  = JTable::getInstance( 'Pages' , 'CommunityTable' );

        $pageId    = $jinput->post->get('id', '', 'INT');
        $page->load( $pageId );

        $oldOwner   = $page->ownerid;
        $newOwner   = $jinput->get('ownerid', NULL, 'INT');

        // Add member if member does not exist.
        if( !$page->isMember( $newOwner , $page->id ) )
        {
            $data   = new stdClass();
            $data->pageid          = $page->id;
            $data->memberid     = $newOwner;
            $data->approved     = 1;
            $data->permissions  = 1;

            // Add user to page members table
            $page->addMember( $data );

            // Add the count.
            $page->addMembersCount( $page->id );

            $message    = JText::_('COM_COMMUNITY_PAGE_SAVED');
        }
        else
        {
            // If member already exists, update their permission
            $member = JTable::getInstance( 'PageMembers' , 'CTable' );
            $keys = array('pageId'=>$page->id, 'memberId'=>$newOwner);
            $member->load( $keys );
            $member->permissions    = '1';

            $member->store();
        }

        $page->ownerid = $newOwner;
        $page->store();

        $message    = JText::_('COM_COMMUNITY_PAGE_OWNER_SAVED');

        $mainframe  = JFactory::getApplication();
        $this->setRedirect( 'index.php?option=com_community&view=pages' , $message ,'message');
    }

    /**
     *  Adds a user to an existing page
     **/
    public function addMember()
    {
        require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $pageId = $jinput->request->get('pageid', array(), 'array');
        $memberId = $jinput->request->get('memberid', '', 'INT');

        if (empty($memberId) || $pageId == '-1') {
            $message = JText::_('COM_COMMUNITY_INVALID_ID');
            return $this->setRedirect('index.php?option=com_community&view=users', $message, 'error');
        }

        $page = JTable::getInstance('Page', 'CTable');
        $model = $this->getModel('pages', 'CommunityAdminModel');
        $user = CFactory::getUser($memberId);


        $all_pages = $model->getAllPages();
        $skipThisPage = array(); //list of user not to be removed this page in reset loop

        // let the user join the page if assigned.
        if (!empty($pageId)) {
            foreach ($pageId as $key => $pageId_row) {
                $page->load($pageId_row);

                $data = new stdClass();
                $data->pageid = $pageId_row;
                $data->memberid = $user->id;
                $data->approved = 1;
                $data->permissions = 0;
                
                // Add user to page members table
                if(!$page->isMember($data->memberid, null)){
                    $page->addMember($data);

                    $page->updateStats();
                    $page->store();
                    //triggers onPageLeave
                    $this->triggerEvent('onPageJoin', $page, $user->id);
                } else {
                    // approving member
                    $member = JTable::getInstance('PageMembers', 'CTable');
                    $keys = array('pageId' => $pageId_row, 'memberId' => $memberId);
                    $member->load($keys);
                    $member->approve();
                }

                //remove the pageid from being looped in removing user
                $skipThisPage[] = $pageId_row;

                // Add the count.
                //$page->addMembersCount( $pageId_row );

                $pages_name_array[] = $page->name;
            }

            $pages_name = implode($pages_name_array, ', ');

            $message = JText::sprintf('COM_COMMUNITY_PAGE_USER_ASSIGNED', $user->getDisplayName(), $pages_name);
            $user->updatePageList(true);
        } else {
            $message = JText::sprintf('COM_COMMUNITY_PAGE_USER_UNASSIGNED', $user->getDisplayName());
        }

        // reset the current pages
        foreach ($all_pages as $page_row) {
            if (in_array($page_row->id, $skipThisPage)) {
                continue;
            }

            $data = new stdClass();
            $data->pageid = $page_row->id;
            $data->memberid = $user->id;

            // Store the page and update the data
            $page->load($page_row->id);

            //only remove if this is a member of this page
            if ($page->isMember($user->id)) {
                $model->removeMember($data);

                //triggers onPageLeave
                $this->triggerEvent('onPageLeave', $page, $user->id);
            }

            $page->updateStats();
            $page->store();
        }

        $this->setRedirect('index.php?option=com_community&view=users', $message, 'message');

    }

    public function savePage()
    {
        $page  = JTable::getInstance( 'Pages' , 'CommunityTable' );
        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;
        $id         = $jinput->post->get('id' , '', 'INT');

        if( empty($id) )
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
        }

        $postData   = $jinput->post->getArray();
        $description = $jinput->post->get('description', '', 'RAW');
        $postData['description'] = $description;

        if ($postData['photopermission-admin'] == 1) {
            if ($postData['photopermission-member'] == 1) {
                $postData['photopermission'] = PAGE_PHOTO_PERMISSION_ALL;
            } else {
                $postData['photopermission'] = PAGE_PHOTO_PERMISSION_ADMINS;
            }
        } else {
            $postData['photopermission'] = PAGE_PHOTO_PERMISSION_DISABLE;
        }

        if ($postData['videopermission-admin'] == 1) {
            if ($postData['videopermission-member'] == 1) {
                $postData['videopermission'] = PAGE_VIDEO_PERMISSION_ALL;
            } else {
                $postData['videopermission'] = PAGE_VIDEO_PERMISSION_ADMINS;
            }
        } else {
            $postData['videopermission'] = PAGE_VIDEO_PERMISSION_DISABLE;
        }

        if ($postData['filesharingpermission-admin'] == 1) {
            if ($postData['filesharingpermission-member'] == 1) {
                $postData['filesharingpermission'] = PAGE_FILESHARING_PERMISSION_ALL;
            } else {
                $postData['filesharingpermission'] = PAGE_FILESHARING_PERMISSION_ADMINS;
            }
        } else {
            $postData['filesharingpermission'] = PAGE_FILESHARING_PERMISSION_DISABLE;
        }

        if ($postData['pollspermission-admin'] == 1) {
            if ($postData['pollspermission-member'] == 1) {
                $postData['pollspermission'] = PAGE_POLLS_PERMISSION_ALL;
            } else {
                $postData['pollspermission'] = PAGE_POLLS_PERMISSION_ADMINS;
            }
        } else {
            $postData['pollspermission'] = PAGE_POLLS_PERMISSION_DISABLE;
        }

        if ($postData['eventpermission-admin'] == 1) {
            if ($postData['eventpermission-member'] == 1) {
                $postData['eventpermission'] = PAGE_EVENT_PERMISSION_ALL;
            } else {
                $postData['eventpermission'] = PAGE_EVENT_PERMISSION_ADMINS;
            }
        } else {
            $postData['eventpermission'] = PAGE_EVENT_PERMISSION_DISABLE;
        }

        if (!isset($postData['pagediscussionfilesharing'])) {
            $postData['pagediscussionfilesharing'] = 0;
        }

        if (!isset($postData['pageannouncementfilesharing'])) {
            $postData['pageannouncementfilesharing'] = 0;
        }

        $page->load( $id );

        $pageParam = new CParameter($page->params);
        $page->bind( $postData );

        foreach($postData as $key=>$data){

            if(!is_null($pageParam->get($key,NULL))) {
                $pageParam->set($key,$data);
            }
        }

        $page->params = $pageParam->toString();

        $message    = '';
        if( $page->store() )
        {
            $message    = JText::_('COM_COMMUNITY_PAGE_SAVED');
        }
        else
        {
            $message    = JText::_('COM_COMMUNITY_PAGE_SAVE_ERROR');
        }

        $mainframe  = JFactory::getApplication();

        $this->setRedirect( 'index.php?option=com_community&view=pages' , $message ,'message' );
    }

    public function deletePage()
    {
        require_once(JPATH_ROOT . '/components/com_community/libraries/featured.php');
        require_once(JPATH_ROOT . '/components/com_community/defines.community.php');

        $featured   = new CFeatured(FEATURED_PAGES);

        $pageWithError = array();

        $page  = JTable::getInstance( 'Page' , 'CTable' );

        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        $id         = $jinput->get('cid' , '', 'NONE');

        if( empty($id) )
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_COMMUNITY_INVALID_ID'), 'error');
        }

        foreach($id as $data)
        {
            require_once( JPATH_ROOT . '/components/com_community/models/pages.php' );

            //delete page members
            CommunityModelPages::deletePageMembers($data);

            //delete page wall
            CommunityModelPages::deletePageWall($data);

            //delete page media files
            CommunityModelPages::deletePageMedia($data);

            //load page data before delete
            $page->load( $data );
            $pageData = $page;

            //delete page avatar.
            jimport( 'joomla.filesystem.file' );
            if( !empty( $pageData->avatar) )
            {
                //images/avatar/pages/d203ccc8be817ad5b6a8335c.png
                $path = explode('/', $pageData->avatar);
                $file = JPATH_ROOT .'/'. $path[0] .'/'. $path[1] .'/'. $path[2] .'/'. $path[3];
                if(file_exists($file))
                {
                    JFile::delete($file);
                }
            }

            if( !empty( $pageData->thumb ) )
            {
                //images/avatar/pages/thumb_d203ccc8be817ad5b6a8335c.png
                $path = explode('/', $pageData->thumb);
                $file = JPATH_ROOT .'/'. $path[0] .'/'. $path[1] .'/'. $path[2] .'/'. $path[3];
                if(file_exists($file))
                {
                    JFile::delete($file);
                }
            }

            if( !$page->delete( $data ) )
            {
                array_push($pageWithError, $data.':'.$pageData->name);
            }

            $featured->delete( $data );
        }

        $message    = '';
        if( empty($error) )
        {
            $message    = JText::_('COM_COMMUNITY_PAGE_DELETED');
        }
        else
        {
            $error = implode(',', $pageWithError);
            $message    = JText::sprintf('COM_COMMUNITY_PAGES_DELETE_PAGE_ERROR' , $error);
        }

        $mainframe  = JFactory::getApplication();

        $this->setRedirect( 'index.php?option=com_community&view=pages' , $message ,'message');
    }

    /**
     *  Responsible to save an existing or a new page.
     */
    public function save()
    {
        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

        $mainframe  = JFactory::getApplication();
        $jinput     = $mainframe->input;

        if( StringHelper::strtoupper($jinput->getMethod()) != 'POST')
        {
            return $this->setRedirect( 'index.php?option=com_community&view=pages' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        // Load frontend language file.
        $lang   = JFactory::getLanguage();
        $lang->load( 'com_community' , JPATH_ROOT );

        $page          = JTable::getInstance( 'Page' , 'CTable' );
        $id             = $jinput->getInt( 'pageid' );
        $page->load( $id );

        $tmpPublished   = $page->published;
        $name           = $jinput->post->get('name' , '', 'STRING') ;
        $published      = $jinput->post->get('published' , '', 'NONE') ;
        $description    = $jinput->post->get('description' , '', 'STRING') ;
        $categoryId     = $jinput->post->get('categoryid' , '', 'INT') ;
        $creator        = $jinput->post->get( 'creator' , 0 );
        $website        = $jinput->post->get('website' , '', 'STRING') ;
        $validated      = true;
        $model          = $this->getModel( 'Pages','CommunityAdminModel' );
        $isNew          = $page->id < 1;
        $ownerChanged   = $page->ownerid != $creator && $page->id >= 1 ;

        // @rule: Test for emptyness
        if( empty( $name ) )
        {
            $validated  = false;
            $mainframe->enqueueMessage( JText::_('COM_COMMUNITY_PAGES_EMPTY_NAME_ERROR'), 'error');
        }

        // @rule: Test if page exists
        if( $model->pageExist( $name , $page->id ) )
        {
            $validated  = false;
            $mainframe->enqueueMessage( JText::_('COM_COMMUNITY_PAGES_NAME_TAKEN_ERROR'), 'error');
        }

        // @rule: Test for emptyness
        if( empty( $description ) )
        {
            $validated  = false;
            $mainframe->enqueueMessage( JText::_('COM_COMMUNITY_PAGES_DESCRIPTION_EMPTY_ERROR'), 'error');
        }

        if( empty( $categoryId ) )
        {
            $validated  = false;
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PAGES_CATEGORY_ERROR'), 'error');
        }

        if($validated)
        {
            // Get the configuration object.
            $config = CFactory::getConfig();

            $page->bindRequestParams();

            // Bind the post with the table first
            $page->name        = $name;
            $page->published       = $published;
            $page->description = $description;
            $page->categoryid  = $categoryId;
            $page->website     = $website;
            $page->approvals   = $jinput->post->get('approvals' , '0');
            $oldOwner           = $page->ownerid;
            $page->ownerid     = $creator;
            if( $isNew )
            {
                $page->created     = gmdate('Y-m-d H:i:s');
            }

            $page->store();

            if( $isNew )
            {
                // Since this is storing pages, we also need to store the creator / admin
                // into the pages members table
                $member             = JTable::getInstance( 'PageMembers' , 'CTable' );
                $member->pageid    = $page->id;
                $member->memberid   = $page->ownerid;

                // Creator should always be 1 as approved as they are the creator.
                $member->approved   = 1;

                // @todo: Setup required permissions in the future
                $member->permissions    = '1';
                $member->store();
            }

            if( !$isNew && $ownerChanged )
            {
                $page->updateOwner( $oldOwner , $creator );
            }

            // send notification if necessary
            if($tmpPublished==0 && $page->published == 1 && $config->get( 'moderatepagecreation' )){
                $this->notificationApproval($page);
            }

            $message    = $isNew ? JText::_( 'COM_COMMUNITY_PAGES_CREATED' ) : JText::_( 'COM_COMMUNITY_PAGES_UPDATED' );
            return $this->setRedirect( 'index.php?option=com_community&view=pages' , $message, 'message' );
        }

        $document   = JFactory::getDocument();

        $viewName   = $jinput->get( 'view' , 'community' );

        // Get the view type
        $viewType   = $document->getType();

        // Get the view
        $view       = $this->getView( $viewName , $viewType );

        $view->setLayout( 'edit' );

        $model      = $this->getModel( $viewName ,'CommunityAdminModel' );

        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

        $view->display();
    }

    public function notificationApproval($page)
    {
        $lang = JFactory::getLanguage();
        $lang->load( 'com_community', JPATH_ROOT );

        $my         = CFactory::getUser();

        // Add notification
        //Send notification email to owner
        $params = new CParameter( '' );
        $params->set('url' , 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id );
        $params->set('pageName' , $page->name );
        $params->set('page' , $page->name );
        $params->set('page_url' , 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id );

        CNotificationLibrary::add( 'pages_notify_creator' , $my->id , $page->ownerid , JText::_( 'COM_COMMUNITY_PAGES_PUBLISHED_MAIL_SUBJECT') , '' , 'pages.notifycreator' , $params );

    }

    public function triggerEvent( $eventName, &$args, $target = null)
    {
        CError::assert( $args , 'object', 'istype', __FILE__ , __LINE__ );

        require_once( COMMUNITY_COM_PATH.'/libraries/apps.php' );
        $appsLib    = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $params     = array();
        $params[]   = $args;

        if(!is_null($target))
            $params[]   = $target;

        $appsLib->triggerEvent( $eventName , $params);
        return true;
    }
}
