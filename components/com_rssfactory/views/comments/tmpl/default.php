<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

?>

<div class="rssfactory-story">
    <h2><?php echo $this->story->item_title; ?></h2>
    <?php echo $this->story->item_description; ?>
</div>

<br/>

<div class="rssfactory-comment">
    <h3><?php echo FactoryTextRss::_('comments_your_comment_title'); ?></h3>
    <hr/>
    <?php if (!$this->createCommentsEnabled): ?>
        <p><?php echo FactoryTextRss::_('comments_not_allowed_to_add_comments'); ?></p>
    <?php else: ?>
        <?php $this->commentView->display(); ?>
    <?php endif; ?>
</div>

<br/>

<div class="rssfactory-comments"
     data-prototype-update="<?php echo htmlentities('<div><textarea rows="5" ></textarea><br /><a href="#" class="btn btn-primary btn-small comment-update">' . FactoryTextRss::_('comments_comment_button_update') . '</a>&nbsp;<a href="#" class="btn btn-small comment-cancel">' . FactoryTextRss::_('comments_comment_button_cancel') . '</a></div>'); ?>">
    <h3><?php echo FactoryTextRss::_('comments_comments_title'); ?></h3>
    <hr/>

    <?php if ($this->items): ?>
        <?php foreach ($this->items as $this->item): ?>
            <div class="well" id="comment-<?php echo $this->item->id; ?>">
                <div class="comment">
                    <p><?php echo nl2br($this->item->text); ?></p>

                    <div class="muted row-fluid">
                        <div class="span8">
                            <?php if ($this->item->user_id): ?>
                                <i class="icon-user"></i>&nbsp;<?php echo $this->item->username; ?>
                            <?php else: ?>
                                <i class="icon-question-sign"></i>&nbsp;<?php echo FactoryTextRss::_('comments_comment_anonymous'); ?>
                            <?php endif; ?>

                            <i class="icon-calendar"></i>
                            <?php echo JHtml::date($this->item->created_at, $this->dateFormat); ?>
                        </div>

                        <?php if ($this->commentsManage): ?>
                            <div class="span4" style="text-align: right;">
                                <a href="#" class="btn btn-small comment-edit"><i
                                        class="icon-edit"></i>&nbsp;<?php echo FactoryTextRss::_('comments_comment_edit'); ?>
                                </a>
                                <a href="<?php echo FactoryRouteRss::task('comment.delete&format=raw&comment_id=' . $this->item->id); ?>"
                                   class="btn btn-small btn-danger comment-delete"><i
                                        class="icon-delete"></i>&nbsp;<?php echo FactoryTextRss::_('comments_comment_delete'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="pagination">
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php else: ?>
        <p><?php echo FactoryTextRss::_('comments_no_comments_found'); ?></p>
    <?php endif; ?>
</div>
