<?php
defined('_JEXEC') or die;

$app			= JFactory::getApplication();
$doc			= JFactory::getDocument();
$tplParams 		= $app->getTemplate(true)->params;
$templateName 	= $app->getTemplate();
$templateUrl  	= JURI::base(true) . '/templates/' . $templateName;
$moduleUrl  	= JURI::base(true) . '/modules/mod_helix3_options';

if( isset( $_COOKIE[$templateName . '_preset'] )) {
	$current_preset = $_COOKIE[$templateName . '_preset'];
} else {
	$current_preset = $tplParams->get('preset');
}

//Add scripts and styles
$doc->addStylesheet( $moduleUrl . '/assets/css/helix3-options.css' );
$doc->addScript( $moduleUrl . '/assets/js/jquery.cookie.js' );
$doc->addScript( $moduleUrl . '/assets/js/helix3-options.js' );
$doc->addScriptdeclaration('var helix3_template = "'. $templateName .'";');
$doc->addScriptdeclaration('var helix3_template_uri = "'. $templateUrl .'";');
?>

<div class="template-options">
	<div class="options-inner">

		<a href="#" class="helix3-toggler">
			<i class="fa fa-cog fa-spin"></i>
		</a>

		<div class="option-section">
			<h4>Presets Color</h4>
			<ul class="helix3-presets clearfix">
				<?php
				for ($i=1; $i < $params->get('presets')+1; $i++) {
					$preset_color = $tplParams->get('preset'. $i .'_major');
					if($current_preset == 'preset' . $i) {
						$active = ' active';
					} else {
						$active = '';
					}

					?>
						<li class="helix3-preset<?php echo $i . $active; ?>" data-preset="<?php echo $i; ?>">
							<a style="background-color: <?php echo $preset_color; ?>" href="#"></a>
						</li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
</div>
