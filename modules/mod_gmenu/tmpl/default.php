<?php
// die("hello");
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if($module->position == 'offcanvas') {
	
}

// Note. It is important to remove spaces between elements.
?>
<?php // The menu class is deprecated. Use nav instead. ?>

<style>
    .outer-container {
        position:relative;
        padding: 30px;
        /* top: 5000px; */
    }
    .inner-container{
        height: 214px;
        background-color: #fff;
        border-radius: 25px;
        box-shadow: 2px 2px 4px 1px grey;
    }
    .page-top{
        border-top-left-radius: 24px;
        border-top-right-radius: 24px;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center center;
        height: 120px;
        overflow: hidden;
        border-bottom: 1px solid #e5e5e5;
    }
    .page-top h3{
        color: #142ba1;
        font-size: 22px;
        margin: 20px;
        padding: 0;
        text-transform: initial;
    }
    /* .page-bottom {
        margin: 20px;
    } */
    .page-bottom  h4{
        color: #ffffff;
        font-size: 18px;
        margin: 20px;
        padding: 0;
        text-transform: initial;
    }
    .page-bottom  p{
        color: #565656;
        font-size: 12px;
        margin: 0 6px;
    }
    .inner-container{
        transition: transform .2s; /* Animation */
    }
    .inner-container:hover {
        transform: scale(1.1); /* (150% zoom - Note: if the zoom is too large, it will go outside of the viewport) */
    }
    .pull-right {
    margin-right: 10px;
	font-size: 30px;
    color: #ffffff;
	}
	.inner-container:hover {
		margin-right: 0px;
		color: #142ba1;
		font-size: 25px;
		/* border: 1px solid #088dff;
		padding: 5px;
		border-radius: 7px; */
	}
	.back-btn.pull-right {
		position: relative;
		bottom: 10px;
		font-size: 30px;
		color: #fff;
	}
	.new-back-btn.pull-right {
		position: relative;
		bottom: 10px;
		font-size: 30px;
		color: #fff;
	}
		
</style>
<div class="container outer-container animate__animated animate__bounceInUp hili">
	<div class="row">
		<div class="back-btn pull-right">
			<i class="fa fa-hand-o-left" aria-hidden="true"></i>
		</div>
        <div class="new-back-btn pull-right">
			<i class="fa fa-hand-o-left" aria-hidden="true"></i>
		</div>
	</div>

    <div class="row">
    <?php 
	 $menu = JFactory::getApplication()->getMenu();
	 $list = $menu->getMenu();
     $menutype =$params->get('menutype');
	foreach ($list as $i => $item)
	{
        if($item->parent_id=="1" && $item->menutype == $menutype){  

                ?>
      
            <div class="col-md-3 col-sm-6 menu" style="margin-bottom: 25px;" id="<?php echo $item->id ?>">
                <div class="inner-container ">
                    <div class="page-top" style="background-image: url('<?php echo ($item->params['menu_image'])?($item->params['menu_image']):('/images/nopreview.png') ;?>');">
                    </div>
                    <div class="page-bottom">
                        <div class="sub-btn pull-right" id="<?php echo $item->id?>">
                        <i class="fa fa-bars"  style="display: none;"></i>
                        </div>
                        <a href="<?php echo $item->route?>"><h4><?php echo $item->title ?></h4></a>
                    </div>
                   
                </div>
            </div>

			<?php foreach ($list as $i => $subitem){
				    if($item->id==$subitem->parent_id){ ?>
			<script>
				jQuery(function ($) {
					var ppd = $('.sm_'+<?php echo $item->id;?>).attr('pid') ;
					$('#'+ppd).find('i').css('display' , 'block');
				});
			</script>
			<div class="col-md-3 col-sm-6 sub-menu sm_<?php echo $item->id;?>" style="margin-bottom: 25px; display:none;" pid="<?php echo $item->id;?>" id="<?php echo $subitem->id; ?>">
			
				<div class="inner-container ">
                    <div class="page-top" style="background-image: url('<?php echo ($subitem->params['menu_image'])?($subitem->params['menu_image']):('/images/nopreview.png') ;?>');">
                    </div>
                    <div class="page-bottom">
                        <div class="sub-sub-btn pull-right" pid="<?php echo $item->id?>" id="<?php echo $subitem->id?>">
                        	<i class="fa fa-bars" style="display: none;" ></i>
                        </div>
                        <a href="<?php echo $subitem->route?>"><h4><?php echo $subitem->title ?></h4></a>
                    </div>
                   
                </div>
			</div>

			<?php
				foreach ($list as $i => $subsubitem){
					if($subitem->id==$subsubitem->parent_id){ ?>
					<script>
				jQuery(function ($) {
					var ppd = $('.ssm_'+<?php echo $subitem->id;?>).attr('pid') ;
					 //alert(ppd)
					$('#'+ppd).find('i').css('display' , 'block');
				});
			</script>
						<div class="col-md-3 col-sm-6 sub-sub-menu ssm_<?php echo $subitem->id;?>" style="margin-bottom: 25px; display:none" pid="<?php echo $subitem->id;?>" id="<?php echo $subsubitem->id ?>">
							<div class="inner-container ">
								<div class="page-top" style="background-image: url('<?php echo ($subsubitem->params['menu_image'])?($subsubitem->params['menu_image']):('/images/nopreview.png') ;?>');">
								</div>
								<div class="page-bottom">
									<div class="sub-btn pull-right" id="<?php echo $subsubitem->id?>" >
									<!-- <i class="fa fa-bars" ></i> -->
									</div>
									<a href="<?php echo $subsubitem->route?>"><h4><?php echo $subsubitem->title ?></h4></a>
								</div>
							
							</div>
						</div>
					<?php }
				}
			?>

        <?php } }?>
        <?php }
    }?>
    </div>
    
</div>
<script>
	jQuery(function ($) {
        $('.fa-hand-o-left').hide();
    	$('.close-offcanvas').click(function(){
			$('.menu').show(); 
			$('.sub-menu').hide();
			$('.sub-sub-menu').hide();
		});
    	$('.back-btn').click(function(){
			$('.menu').show();
			$('.sub-menu').hide();
			$('.sub-sub-menu').hide();
			$(this).hide();
		});
        $('.new-back-btn').click(function(){
            var id = this.id;
            var pid = $(this).attr('pid');
			$('.menu').hide();
			$('.sub-sub-menu').hide();
			$('.ssm_'+id).hide();
			$('.sm_'+pid).show();
            $('.back-btn').show();
            $('.new-back-btn').hide();
		});
		$('.sub-btn').click(function(){
			var id = this.id;
            var pid = $(this).attr('pid');
			$('.menu').hide();
            $('.fa-hand-o-left').show();
			$('.sm_'+id).show();
			$('.new-back-btn').hide();
            $('.back-btn').show();
            $('.new-back-btn').attr('id' , id );
            $('.new-back-btn').attr('pid' , pid );
		});

		$('.sub-sub-btn').click(function(){
			var id = this.id;
            var pid = $(this).attr('pid');
            $('.new-back-btn').attr('id' , id );
            $('.new-back-btn').attr('pid' , pid );
			$('.sub-sub-menu').hide();
            $('.back-btn').hide();
            $('.new-back-btn').show();
			$('.sub-menu').hide();
			$('.ssm_'+id).show();
		});
	});

</script>