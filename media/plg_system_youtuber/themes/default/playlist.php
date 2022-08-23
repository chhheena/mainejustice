<?php 
/**
 * @package  YouTubeR
 * @license  https://allforjoomla.com/license
 *
 * Created by Oleg Micriucov for Joomla! 3.x
 * https://allforjoomla.com
 *
 */
defined('_JEXEC') or die(':)');
?>
<section class="mxYouTubeR mxYouTubeR_playlist mxYouTubeR_theme_<?php echo $this->attribs['theme'];?>">
    <div class="mxyt-playlist mxyt-cols-<?php echo $this->attribs['responsive_limit'];?>">
    	<div class="mxyt-brow">
			<?php
			$i=0;
			$this->_total = count($this->videos);
			foreach($this->videos as $video){
				$i++;
                $this->_item = $video;
				?>
                <div class="mxyt-bcol-<?php echo $this->attribs['cols'];?>">
                    <?php 
                    $this->_item = $video;
                    $this->_tick = $i;
                    echo $this->loadTemplate('_item');
                    ?>
                </div>
                <?php
				if($i%(int)$this->attribs['cols']==0 && $i<$this->_total){
					echo '</div><div class="mxyt-brow">';
				}
			}
            ?>
        </div>
    </div>
    
    <?php
    $haveMore = (isset($this->playlist) && $this->playlist->haveMoreThan($this->attribs['start']));
    if($this->attribs['load_more']=='true' && $haveMore){
        echo '<div class="mxyt-more"><span class="mxyt-button mxyt-load-more'.($this->attribs['infinite_scroll']=='true'?' mxyt-infinite-scroll':'').'" data-mxyt-havemore="'.($haveMore?'1':'0').'" data-mxyt-start="'.$this->attribs['start'].'" data-mxyt-cfg="'.http_build_query($this->cfg).'">'.$this->attribs['load_more_text'].'...</span></div>';
    }
    ?>
</section>