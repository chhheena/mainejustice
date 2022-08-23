<?php
/**
* @copyright (C) 2016 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgContentArticleComments extends JPlugin
{

    function onContentAfterDisplay($context, &$article, &$params, $limitstart = null)
    {   
        // ignore this plugin at joomla backend
        $mainframe = JFactory::getApplication();
        if($mainframe->isClient('administrator')) return;

        if($context == 'com_content.article')
        {
            $categoryIds =  $this->params->get('category',0);
            $app = JFactory::getApplication();
            $menu = (isset($app->getMenu()->getActive()->id)) ? $app->getMenu()->getActive()->id : null;

            if(!$categoryIds || (count($categoryIds) && !in_array($article->catid, $categoryIds)) || !$article->catid || !$article->id){
               return;
            }
        
            JPlugin::loadLanguage( 'plg_content_articlecomments', JPATH_ADMINISTRATOR );
            require_once JPATH_BASE.'/components/com_community/libraries/core.php';
            $svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
            include_once $svgPath;
            $commentsHeader = '<h3 class="article'.$article->id.' jom-artilecommentstitle">'.JText::sprintf('PLG_CONTENT_ARTICLECOMMENTS_ARTICLE_TITLE', $article->title.'</h3>');
            $comments = $commentsHeader.CServiceCommentHelper::renderComment('joomla.article.comments'.'.'.$article->id, $article->id, 'articles', 'article_comment', 'index.php?option=com_content&view=article&id='.$article->id.'&catid='.$article->catid.'&Itemid='.$menu, '',$article->title);
        } else {
            $comments = '';
        }
        return $comments;
    }

}


