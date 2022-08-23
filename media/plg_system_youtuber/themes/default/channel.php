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
<section class="mxYouTubeR mxYouTubeR_channel mxYouTubeR_theme_<?php echo $this->attribs['theme'];?>">
	<?php
	if($this->showHeader){
		?>
		<div class="mxyt-channel-head">
			<div class="mxyt-channel-head-cover" style="background-image: url('<?php echo (!empty($this->channel['brandingSettings']['image']['bannerExternalUrl'])?$this->channel['brandingSettings']['image']['bannerExternalUrl']:$this->channel['brandingSettings']['image']['bannerImageUrl']); ?>');">
				<div class="mxyt-channel-logo"><img src="<?php echo $this->channel['snippet']['thumbnails']['medium']['url'];?>" alt="<?php echo $this->channel['snippet']['title'];?>" /></div>
				<div class="mxyt-channel-info">
					<span><?php printf( JText::_('PLG_SYSTEM_YOUTUBER_SVIDEOS'), $this->channel['statistics']['videoCount'] ); ?></span>
					<span><?php printf( JText::_('PLG_SYSTEM_YOUTUBER_SVIEWS'), $this->channel['statistics']['viewCount'] ); ?></span>
					<span><?php printf( JText::_('PLG_SYSTEM_YOUTUBER_SSUBSCRIBERS'), $this->channel['statistics']['subscriberCount'] ); ?></span>
				</div>
			</div>
			<div class="mxyt-channel-title">
				<div class="mxyt-channel-subscr-btn">
					<script src="https://apis.google.com/js/platform.js"></script>
					<div class="g-ytsubscribe" data-channelid="<?php echo $this->channel['id']; ?>" data-layout="default" data-count="default"></div>
				</div>
				<?php echo $this->channel['snippet']['title'];?>
			</div>
			<div class="mxyt-channel-descr">
				<div class="mxyt-text-description mxyt-less"><div class="mxyt-text-description-full"><?php echo $this->getFullVideoDescr($this->channel['snippet']['description']);?></div></div>
			</div>
		</div>
		<?php
	}
	?>
    <div class="mxyt-channel-videos">
        <?php
        if($this->showPlaylists){
            ?>
            <div class="mxyt-channel-playlist-chooser">
                <div class="mxyt-playlist-select">
                    <?php 
                    $cfg = $this->cfg;
                    $cfg['start'] = 0;
                    $list = '<ul>';
                    $totalPls = count($this->playlists);
                    foreach($this->playlists as $plID=>$plTitle){
                        $cfg['playlist_id'] = $plID;
                        if($plID==$this->attribs['playlist_id']){
                            echo '<b>'.$plTitle.($totalPls>1?' <i class="mxyt-icon mxyt-icon-angle-down" aria-hidden="true"></i>':'').'</b>';
                        }
                        else{
                            $list.= '<li><span data-mxyt-cfg="'.http_build_query($cfg).'">'.$plTitle.'</span></li>';
                        }
                    }
                    $list.= '</ul>';
                    echo $list;
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="mxyt-playlist mxyt-cols-<?php echo $this->attribs['responsive_limit'];?>">
			<?php
            $i=0;
            $this->_total = count($this->videos);
            if($this->_total==0){
                echo '<p align="center">'.JText::_('PLG_SYSTEM_YOUTUBER_NO_VIDEOS_IN_PLAYLIST').'</p>';
            }
			echo '<div class="mxyt-brow">';
            foreach($this->videos as $video){
                $i++;
				echo '<div class="mxyt-bcol-'.$this->attribs['cols'].'">';
                
                    $this->_item = $video;
                    $this->_tick = $i;
                    echo $this->loadTemplate('_item');
                
				echo '</div>';
				if($i%(int)$this->attribs['cols']==0 && $i<$this->_total){
					echo '</div><div class="mxyt-brow">';
				}
            }
			echo '</div>';
            ?>
        </div>
        
        <?php
        $haveMore = (isset($this->playlist) && $this->playlist->haveMoreThan($this->attribs['start']));
        if($this->attribs['load_more']=='true' && $haveMore){
            echo '<div class="mxyt-more"><span class="mxyt-button mxyt-load-more'.($this->attribs['infinite_scroll']=='true'?' mxyt-infinite-scroll':'').'" data-mxyt-havemore="'.($haveMore?'1':'0').'" data-mxyt-start="'.$this->attribs['start'].'" data-mxyt-cfg="'.http_build_query($this->cfg).'">'.$this->attribs['load_more_text'].'...</span></div>';
        }
        ?>
    </div>
</section>