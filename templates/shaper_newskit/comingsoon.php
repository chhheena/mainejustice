<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$app = JFactory::getApplication();

//Load Helix
$helix3_path = JPATH_PLUGINS.'/system/helix3/core/helix3.php';
if (file_exists($helix3_path)) {
    require_once($helix3_path);
    $this->helix3 = Helix3::getInstance();
} else {
    die('Please install and activate helix plugin');
}

//custom css file
$custom_css_path = JPATH_ROOT . '/templates/' . $this->template . '/css/custom.css';
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/newskit.css' );

//Comingsoon Logo
if ($logo_image = $this->params->get('comingsoon_logo')) {
	 $logo = JURI::root() . '/' .  $logo_image;
	 $path = JPATH_ROOT . '/' .  $logo_image;
} else {
    $logo 		= $this->baseurl . '/templates/' . $this->template . '/images/presets/preset1/logo.png';
    $path 		= JPATH_ROOT . '/templates/' . $this->template . '/images/presets/preset1/logo.png';
    $ratlogo 	= $this->baseurl . '/templates/' . $this->template . '/images/presets/preset1/logo@2x.png';
}

if(file_exists($path)) {
	$image 		 = new JImage( $path );
	$logo_width  = $image->getWidth();
	$logo_height = $image->getHeight();
} else {
	$logo_width 	= '';
	$logo_height = '';
}

$comingsoon_title = $this->params->get('comingsoon_title');
if( $comingsoon_title ) {
	$doc->setTitle( $comingsoon_title . ' | ' . $app->get('sitename') );
}

$comingsoon_date = explode('-', $this->params->get("comingsoon_date"));

//Load jQuery
JHtml::_('jquery.framework');

?>
<!DOCTYPE html>
<html class="sp-comingsoon" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    if($favicon = $this->helix3->getParam('favicon')) {
        $doc->addFavicon( JURI::base(true) . '/' .  $favicon);
    } else {
        $doc->addFavicon( $this->helix3->getTemplateUri() . '/images/favicon.ico' );
    }
    ?>
    <jdoc:include type="head" />
    <?php
    $this->helix3->addCSS('bootstrap.min.css, font-awesome.min.css')
        ->lessInit()->setLessVariables(array(
            'preset'=>$this->helix3->Preset(),
            'bg_color'=> $this->helix3->PresetParam('_bg'),
            'text_color'=> $this->helix3->PresetParam('_text'),
            'major_color'=> $this->helix3->PresetParam('_major')
            ))
        ->addLess('master', 'template')
        ->addLess('presets',  'presets/'.$this->helix3->Preset())
    	->addJS('jquery.countdown.min.js');
    	// has exist custom.css then load it
    	if (file_exists($custom_css_path)) {
			 $this->helix3->addCSS('custom.css');
		}

		//background image
		$comingsoon_bg = '';
		$hascs_bg = '';
		if ($cs_bg = $this->params->get('comingsoon_bg')) {
			$comingsoon_bg 	= JURI::root() . $cs_bg;
			$hascs_bg 		= 'has-background';
		}
    ?>
</head>
<body>
	<div class="sp-comingsoon-wrap <?php echo $hascs_bg; ?>" style="background-image: url(<?php echo $comingsoon_bg; ?>);">	
		<div class="container">
			<div class="text-center">
				<div id="sp-comingsoon">
					<div class="comingsoon-page-logo">
						<?php if($comingsoon_logo = $this->params->get('comingsoon_logo')){ ?>
							<img class="comingsoon-logo" alt="logo" src="<?php echo $logo; ?>" />
						<?php } else { ?>
							<img class="sp-default-logo comingsoon-logo" alt="logo" src="<?php echo $logo; ?>" />
							<img class="sp-retina-logo comingsoon-logo" alt="logo" src="<?php echo $ratlogo; ?>" width="<?php echo $logo_width; ?>" height="<?php echo  $logo_height; ?>" />
						<?php }?>
					</div>

					<div id="sp-comingsoon-countdown" class="sp-comingsoon-countdown"></div>

					<?php if( $comingsoon_title ) { ?>
						<h1 class="sp-comingsoon-title">
							<?php echo $comingsoon_title; ?>
						</h1>
					<?php } ?>

					<?php if( $this->params->get('comingsoon_content') ) { ?>
						<div class="sp-comingsoon-content">
							<?php echo $this->params->get('comingsoon_content'); ?>
						</div>
					<?php } ?>


					<?php if($this->countModules('comingsoon')) { ?>
					<div class="sp-position-comingsoon">
						<jdoc:include type="modules" name="comingsoon" style="sp_xhtml" />
						<button class="btn btn-primary get-notify-btn" style="display: none;"><?php echo JText::_('COM_GET_NOTIFY_ME'); ?></button>
						<button class="btn btn-default contact-us-btn" style="display: none;"><?php echo JText::_('COM_CONTACT_US'); ?></button>
					</div>
					<?php } ?>

					<div class="copyright">
						<p><?php echo $this->params->get('copyright'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(function($) {
			$(document).ready(function () {
				if($('.contact-us-modal').length>0){
					$('.contact-us-btn').show();

				    var myModal = $('.contact-us-modal');
				    $('<div class="modalOverlay"></div>').appendTo('.sp-module.contact-us-modal');
				    $('<span class="closeModal">&times;</span>').insertBefore('.contact-us-modal .sppb-ajax-contact-content');

				    var myBtn   = $('.contact-us-btn');

				    myBtn.on('click', function(){
				    	myModal.show();
				    	$('.sp-comingsoon body').addClass('active');
				    });

				    $('.modalOverlay, .closeModal').on('click', function(){
				    	myModal.hide();
				    	$('.sp-comingsoon body').removeClass('active');
				    });
				}

				if($('.acymailing-module-modal').length>0){
					$('.get-notify-btn').show();

				    var myModal2 = $('.acymailing-module-modal');
				    $('<div class="modalOver"></div>').appendTo('.sp-module.acymailing-module-modal');
				    $('<span class="closeModal2">&times;</span>').insertBefore('.acymailing-module-modal .acymailing_module_form');

				    var myBtn2   = $('.get-notify-btn');

				    myBtn2.on('click', function(){
				    	myModal2.show();
				    	$('.sp-comingsoon body').addClass('active');
				    });

				    $('.modalOver, .closeModal2').on('click', function(){
				    	myModal2.hide();
				    	$('.sp-comingsoon body').removeClass('active');
				    });
				}
			});


			$('#sp-comingsoon-countdown').countdown('<?php echo trim($comingsoon_date[2]); ?>/<?php echo trim($comingsoon_date[1]); ?>/<?php echo trim($comingsoon_date[0]); ?>', function(event) {
			    $(this).html(event.strftime('<div class="days"><span class="number">%-D</span><span class="string">%!D:<?php echo JText::_("HELIX_DAY"); ?>,<?php echo JText::_("HELIX_DAYS"); ?>;</span></div><div class="hours"><span class="number">%H</span><span class="string">%!H:<?php echo JText::_("HELIX_HOUR"); ?>,<?php echo JText::_("HELIX_HOURS"); ?>;</span></div><div class="minutes"><span class="number">%M</span><span class="string">%!M:<?php echo JText::_("HELIX_MINUTE"); ?>,<?php echo JText::_("HELIX_MINUTES"); ?>;</span></div><div class="seconds"><span class="number">%S</span><span class="string">%!S:<?php echo JText::_("HELIX_SECOND"); ?>,<?php echo JText::_("HELIX_SECONDS"); ?>;</span></div>'));
			});
		});
	</script>

</body>
</html>