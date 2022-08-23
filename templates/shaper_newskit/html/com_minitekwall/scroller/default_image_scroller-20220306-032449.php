<?php
/**
* @title        Minitek Wall
* @copyright    Copyright (C) 2011-2021 Minitek, All rights reserved.
* @license      GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

if (!empty($this->wall) ||  $this->wall!== 0)
{
	foreach ($this->wall as $key=>$item)
	{
		?><div class="mnwall-scr-item <?php 
			echo $this->hoverEffectClass; 
			?>" style="padding-left:<?php echo $this->gutter; ?>px; padding-right:<?php echo $this->gutter; ?>px;"><?php 

			?><div class="mnwall-scr-item-outer-cont" style="<?php 
				if ($this->border_radius) 
				{
					?>border-radius: <?php echo $this->border_radius; ?>px; <?php 
				}
				if ($this->border) 
				{
					?>border: <?php echo $this->border; ?>px solid <?php echo $this->border_color; ?>; <?php 
				}
				echo $this->animated_flip; 
				?>"><?php 

				?><div class="mnwall-scr-item-inner-cont">
					<div class="mnwall-scr-item-cover"> </div><?php 

					if (isset($item->itemImage) && $item->itemImage && $this->scr_images) 
					{
						if (isset($item->itemLink) && $this->scr_image_link) 
						{
							?><a href="<?php echo $item->itemLink; ?>" class="mnwall-scr-photo-link">
								<img src="<?php echo $item->itemImage; ?>" alt="<?php echo htmlspecialchars($item->itemTitleRaw); ?>" />
							</a><?php 
						} 
						else 
						{
							?><div class="mnwall-scr-photo-link">
								<img src="<?php echo $item->itemImage; ?>" alt="<?php echo htmlspecialchars($item->itemTitleRaw); ?>" />
							</div><?php 
						}
					}

					if ($this->detailBox) 
					{
						?><div class="mnwall-scr-detail-box <?php 
							echo $this->detailBoxTextColor; 
							if (!$item->itemImage || !$this->scr_images) 
							{
								echo ' no_image';
							} 
							?>" style="background-color: rgba(<?php echo $this->detailBoxBackground; ?>,<?php echo $this->detailBoxBackgroundOpacity; ?>);"><?php 
							
							if ($this->detailBoxDate && isset($item->itemDate)) 
							{
								?><div class="mnwall-date"><?php 
									echo $item->itemDate; 
								?></div><?php 
							}

							if ($this->detailBoxTitle) 
							{
								?><h3 class="mnwall-title"><?php 
									if (isset($item->itemLink) && $this->detailBoxTitleLink) 
									{
										?><a href="<?php echo $item->itemLink; ?>"><?php 
											echo $item->itemTitle; 
										?></a><?php 
									} 
									else 
									{
										?><span><?php echo $item->itemTitle; ?></span><?php 
									}
								?></h3><?php 
							}

							if (($this->detailBoxCategory && ((isset($item->itemCategoryRaw) && $item->itemCategoryRaw) || (isset($item->itemCategoriesRaw) && $item->itemCategoriesRaw))) || ($this->detailBoxLocation && isset($item->itemLocationRaw)) || ($this->detailBoxAuthor && ((isset($item->itemAuthorRaw) && $item->itemAuthorRaw) || (isset($item->itemAuthorsRaw) && $item->itemAuthorsRaw))) || $this->detailBoxType) 
							{ 
								?><div class="mnwall-item-info"><?php 
									if ($this->detailBoxCategory && ((isset($item->itemCategoryRaw) && $item->itemCategoryRaw) || (isset($item->itemCategoriesRaw) && $item->itemCategoriesRaw))) 
									{
										?><p class="mnwall-item-category">
											<span><?php echo JText::_('COM_MINITEKWALL_IN'); ?> </span><?php 
											echo $item->itemCategory; 
										?></p><?php 
									}

									if ($this->detailBoxLocation && isset($item->itemLocationRaw) && $item->itemLocationRaw) 
									{
										?><p class="mnwall-item-location">
											<span><?php echo JText::_('COM_MINITEKWALL_IN'); ?> </span><?php 
											echo $item->itemLocation; 
										?></p><?php 
									}

									if ($this->detailBoxType) 
									{
										?><p class="mnwall-item-type"><?php 
											echo $item->itemType; 
										?></p><?php 
									}

									if ($this->detailBoxAuthor && ((isset($item->itemAuthorRaw) && $item->itemAuthorRaw) || (isset($item->itemAuthorsRaw) && $item->itemAuthorsRaw))) 
									{
										?><p class="mnwall-item-author">
											<span><?php echo JText::_('COM_MINITEKWALL_BY'); ?> </span><?php 
											echo $item->itemAuthor; 
										?></p><?php 
									}
								?></div><?php 
							}

							if ($this->detailBoxIntrotext && isset($item->itemIntrotext) && $item->itemIntrotext) 
							{
								?><div class="mnwall-desc"><?php 
									echo $item->itemIntrotext; 
								?></div><?php 
							}

							if ($this->detailBoxPrice && isset($item->itemPrice)) 
							{
								?><div class="mnwall-price"><?php 
									echo $item->itemPrice; 
								?></div><?php 
							}

							if ($this->detailBoxHits && isset($item->itemHits)) 
							{
								?><div class="mnwall-hits">
									<p><?php echo $item->itemHits; ?>&nbsp;<?php echo JText::_('COM_MINITEKWALL_HITS'); ?></p>
								</div><?php 
							}

							if ($this->detailBoxCount && isset($item->itemCount)) 
							{
								?><div class="mnwall-count">
									<p><?php echo $item->itemCount; ?></p>
								</div><?php 
							}

							if ($this->detailBoxReadmore) 
							{
								if (isset($item->itemLink)) 
								{
									?><div class="mnwall-readmore">
										<a href="<?php echo $item->itemLink; ?>"><?php echo JText::_('COM_MINITEKWALL_READ_MORE'); ?></a>
									</div><?php 
								}
							}
						?></div><?php 
					}
				?></div><?php 
				
				if ($this->hoverBox) 
				{
					?><div class="mnwall-hover-box" style="<?php 
						echo $this->animated; 
						?> background-color: rgba(<?php echo $this->hb_bg_class; ?>,<?php echo $this->hb_bg_opacity_class; ?>);"><?php 

						?><div class="mnwall-hover-box-content <?php echo $this->hoverTextColor; ?>"><?php 
							if ($this->hoverBoxDate && isset($item->itemDate)) 
							{
								?><div class="mnwall-date"><?php 
									echo $item->itemDate; 
								?></div><?php 
							}

							if ($this->hoverBoxTitle) 
							{
								?><h3 class="mnwall-title"><?php 
									if (isset($item->itemLink) && $this->detailBoxTitleLink) 
									{
										?><a href="<?php echo $item->itemLink; ?>"><?php 
											echo $item->hover_itemTitle; 
										?></a><?php 
									} 
									else 
									{
										?><span><?php echo $item->hover_itemTitle; ?></span><?php 
									}
								?></h3><?php 
							}

							if ($this->hoverBoxCategory || $this->hoverBoxLocation || $this->hoverBoxType || $this->hoverBoxAuthor) 
							{
								?><div class="mnwall-item-info"><?php 
									if (((isset($item->itemCategoryRaw) && $item->itemCategoryRaw) || (isset($item->itemCategoriesRaw) && $item->itemCategoriesRaw)) && $this->hoverBoxCategory) 
									{
										?><p class="mnwall-item-category">
											<span><?php echo JText::_('COM_MINITEKWALL_IN'); ?> </span><?php 
											echo $item->itemCategory; 
										?></p><?php 
									}

									if (isset($item->itemLocationRaw) && $item->itemLocationRaw && $this->hoverBoxLocation) 
									{
										?><p class="mnwall-item-category">
											<span><?php echo JText::_('COM_MINITEKWALL_IN'); ?> </span><?php 
											echo $item->itemLocation; 
										?></p><?php 
									}

									if ($this->hoverBoxType) 
									{
										?><p class="mnwall-item-category"><?php 
											echo $item->itemType; 
										?></p><?php 
									}

									if (((isset($item->itemAuthorRaw) && $item->itemAuthorRaw) || (isset($item->itemAuthorsRaw) && $item->itemAuthorsRaw)) && $this->hoverBoxAuthor) 
									{
										?><p class="mnwall-item-author">
											<span><?php echo JText::_('COM_MINITEKWALL_BY'); ?> </span><?php 
											echo $item->itemAuthor; 
										?></p><?php 
									}
								?></div><?php 
							}

							if ($this->hoverBoxIntrotext) 
							{
								if (isset($item->hover_itemIntrotext) && $item->hover_itemIntrotext) 
								{
									?><div class="mnwall-desc"><?php 
										echo $item->hover_itemIntrotext; 
									?></div><?php 
								}
							}

							if ($this->hoverBoxPrice && isset($item->itemPrice)) 
							{
								?><div class="mnwall-price"><?php 
									echo $item->itemPrice; 
								?></div><?php 
							}

							if ($this->hoverBoxHits && isset($item->itemHits)) 
							{
								?><div class="mnwall-hits">
									<p><?php echo $item->itemHits; ?>&nbsp;<?php echo JText::_('COM_MINITEKWALL_HITS'); ?></p>
								</div><?php 
							}

							if ($this->hoverBoxLinkButton || $this->hoverBoxLightboxButton) 
							{
								?><div class="mnwall-item-icons"><?php 
									if ($this->hoverBoxLinkButton) 
									{
										if (isset($item->itemLink)) 
										{
											?><a href="<?php echo $item->itemLink; ?>" class="mnwall-item-link-icon">
												<i class="fa fa-link"></i>
											</a><?php 
										}
									}

									if ($this->hoverBoxLightboxButton && (isset($item->itemImage) && $item->itemImage && $this->scr_images)) 
									{
										?><a href="<?php echo $item->itemImage; ?>" class="mnwall-lightbox mnwall-item-lightbox-icon" data-lightbox="lb-<?php echo $this->widgetID; ?>" data-title="<?php echo htmlspecialchars($item->itemTitleRaw); ?>">
											<i class="fa fa-search"></i>
										</a><?php 
									}
								?></div><?php 
							}
						?></div>
					</div><?php 
				}
			?></div>
		</div><?php 
	}
} 
else 
{
	echo '-'; // =0 // for javascript purposes
}
