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
?>
<section class="mxYouTubeR mxYouTubeR_video mxYouTubeR_theme_<?php echo $this->attribs['theme'];?>">
	<div class="mxyt-video">
    	<?php
		echo $this->getVideoHTML($this->video,$this->attribs);
		?>
    </div>
    <?php 
	if($this->showTitle || $this->showChannel || $this->showDate){
		?>
		<div class="mxyt-title">
			<?php
			if($this->showDate) echo '<div class="mxyt-date">'.$this->getVideoDate($this->video['snippet']['publishedAt']).'</div>';
			if($this->showTitle) echo '<h1>'.($this->attribs['title']!=''?$this->attribs['title']:$this->video['snippet']['title']).'</h1>';
			if($this->showChannel){
				?>
                <div class="mxyt-channel">
                	<img src="<?php echo $this->channel['snippet']['thumbnails']['default']['url'];?>" alt="<?php echo $this->channel['snippet']['title'];?>">
                    <h3><?php echo $this->channel['snippet']['title'];?></h3>
                </div>
                <?php
			}
			?>
		</div>
		<?php
	}
	if($this->showDescription){
		?>
        <div class="mxyt-description">
        	<div class="mxyt-text-description mxyt-less"><div class="mxyt-text-description-full"><?php echo $this->getFullVideoDescr($this->attribs['description']?$this->attribs['description']:$this->video['snippet']['description']);?></div></div>
        </div>
		<?php
	}
	if($this->showMeta){
		?>
        <div class="mxyt-meta">
        	<div class="mxyt-views mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_VIEWS');?>"><i class="mxyt-icon mxyt-icon-views"></i> <?php echo $this->video['statistics']['viewCount'];?></div>
        	<div class="mxyt-likes mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_LIKES');?>"><i class="mxyt-icon mxyt-icon-likes"></i> <?php echo $this->video['statistics']['likeCount'];?></div>
        	<div class="mxyt-dislikes mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_DISLIKES');?>"><i class="mxyt-icon mxyt-icon-dislikes"></i> <?php echo $this->video['statistics']['dislikeCount'];?></div>
        	<div class="mxyt-favs mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_ADDED_TO_FAVORITES');?>"><i class="mxyt-icon mxyt-icon-favs"></i> <?php echo $this->video['statistics']['favoriteCount'];?></div>
        	<div class="mxyt-comments mxyt-tip" title="<?php echo JText::_('PLG_SYSTEM_YOUTUBER_NUMBER_OF_COMMENTS');?>"><i class="mxyt-icon mxyt-icon-comments"></i> <?php echo $this->video['statistics']['commentCount'];?></div>
        </div>
		<?php
	}
	?>
</section>