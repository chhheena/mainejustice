<?php 
/**
 * @package    YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined( '_JEXEC' ) or die;

?>
<div class="mxyt-flipcards-playlist-item">
    <div class="mxyt-flipcards-front">
        <img class="mxyt-img" src="<?php echo $this->getThumbURL($this->_item,$this->attribs['size']);?>">
        <?php
        echo $this->getVideoHTML($this->_item,$this->attribs);
        $rel = $this->attribs['rel'].'_';
        ?>
    </div>
    <div class="mxyt-flipcards-back">
        <img class="mxyt-img" src="<?php echo $this->getThumbURL($this->_item,$this->attribs['size']);?>">
        <a href="<?php echo $this->getVideoHref($this->_item,$this->attribs);?>" class="mxyt-flipcards-link mxyt-lightbox fancybox.iframe" data-fancybox="<?php echo $rel;?>" data-fancybox-type="iframe" target="_blank">
            <div class="mxyt-flipcards-limit">
                <div class="mxyt-flipcards-title">
                    <?php
                    if($this->showTitle) echo '<h3>'.$this->_item['snippet']['title'].'</h3>';
                    ?>
                </div>
                <div class="mxyt-flipcards-description"><?php echo $this->getLimitVideoDescr($this->_item['snippet']['description'],(int)$this->attribs['max_words']);?></div>
                <div class="mxyt-flipcards-limit-fade"></div>
            </div>
            <div class="mxyt-flipcards-meta">
                <div class="mxyt-views" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_VIEWS');?>"><i class="mxyt-icon mxyt-icon-views"></i> <?php echo $this->_item['statistics']['viewCount'];?></div>
                <div class="mxyt-likes" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_LIKES');?>"><i class="mxyt-icon mxyt-icon-likes"></i> <?php echo (int)@$this->_item['statistics']['likeCount'];?></div>
                <div class="mxyt-comments" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_COMMENTS');?>"><i class="mxyt-icon mxyt-icon-comments"></i> <?php echo $this->_item['statistics']['commentCount'];?></div>
            </div>
        </a>
    </div>
</div>
