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
<div class="mxyt-playlist-item-blank">
    <div class="ch-item">
        <div class="ch-info">
            <h3><?php echo $this->_item['snippet']['title'];?></h3>
            <a href="<?php echo $this->getVideoHref($this->_item,$this->attribs);?>" class="mxyt-lightbox fancybox.iframe" data-fancybox-type="iframe" data-fancybox="<?php echo $this->attribs['rel'];?>" target="_blank"><span class="mxyt-icon mxyt-icon-play"></span></a>
        </div>
        <div class="ch-thumb" style="background-image:url(<?php echo $this->getThumbURL($this->_item,$this->attribs['size']);?>);z-index:<?php echo $this->_total+100-$this->_tick;?>"></div>
    </div>
</div>
