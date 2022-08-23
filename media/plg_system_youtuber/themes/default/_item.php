<?php 
/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die(':)');

$isVideoSingle = (!$this->showTitle && !$this->showDescription && !$this->showMeta);
?>
<div class="mxyt-playlist-item">
    <div class="mxyt-video <?php echo ($isVideoSingle?'mxyt-single':'');?>">
        <?php
        echo $this->getVideoHTML($this->_item,$this->attribs);
        ?>
    </div>
    <?php 
    if($this->showTitle){
        ?>
        <div class="mxyt-title">
            <?php
            echo '<h3>'.$this->_item['snippet']['title'].'</h3>';
            ?>
        </div>
        <?php
    }
    if($this->showDate){
        ?>
        <div class="mxyt-date">
            <?php
            echo $this->getVideoDate($this->_item['snippet']['publishedAt']);
            ?>
        </div>
        <?php
    }
    if($this->showDescription){
        ?>
        <div class="mxyt-description">
            <div class="mxyt-text-description mxyt-less"><div class="mxyt-text-description-full"><?php echo $this->getFullVideoDescr($this->_item['snippet']['description']);?></div></div>
        </div>
        <?php
    }
    if($this->showMeta){
        ?>
        <div class="mxyt-meta">
            <div class="mxyt-views mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_VIEWS');?>"><i class="mxyt-icon mxyt-icon-views"></i> <?php echo (int)@$this->_item['statistics']['viewCount'];?></div>
            <div class="mxyt-likes mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_LIKES');?>"><i class="mxyt-icon mxyt-icon-likes"></i> <?php echo (int)@$this->_item['statistics']['likeCount'];?></div>
            <div class="mxyt-comments mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_COMMENTS');?>"><i class="mxyt-icon mxyt-icon-comments"></i> <?php echo (int)@$this->_item['statistics']['commentCount'];?></div>
        </div>
        <?php
    }
?>
</div>
