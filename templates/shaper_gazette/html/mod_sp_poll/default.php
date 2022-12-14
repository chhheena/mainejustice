<?php 
/*------------------------------------------------------------------------
# mod_sp_poll - Ajax poll module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2018 JoomShaper.com. All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/
defined ('_JEXEC') or die('resticted aceess');
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("var base_url = '" . JUri::base() . "index.php?option=com_sppolls'");

$cookie = JFactory::getApplication()->input->cookie;
$vote = $cookie->get('sp_poll_voted_' . $module->id, null);
if(!is_null($vote))	
	$vote = base64_decode($vote);
?>

<div class="mod-sppoll <?php echo $moduleclass_sfx;?>">
	<?php if(isset($poll)) { ?>
		<form class="form-sppoll" data-id="<?php echo $poll->id; ?>" data-module_id="<?php echo $module->id; ?>">
			<div class="sppoll-info-wrap">
				<?php $polls = json_decode($poll->polls); ?>
				<h3 class="sppoll-title"><?php echo $poll->title; ?></h3>
				<?php foreach ($polls as $key=>$value) {?>
					<div class="radio">
						<label>
							<input type="radio" name="question" value="<?php echo $key; ?>" <?php echo !is_null($vote) && ($value->poll == $vote) ? 'checked': ($key==0 ? 'checked': ''); ?>>
							<?php echo $value->poll; ?>
						</label>
					</div>
				<?php } ?>
			</div>
			<div class="sppoll-submit-wrap">
				<input type="submit" class="btn btn-default" value="<?php echo JText::_('MOD_SP_POLL_BUTTON_SUBMIT'); ?>">
				<?php if (!is_null($vote)){ ?>
					<input type="button" class="btn btn-success btn-poll-result" data-result_id="<?php echo $poll->id; ?>" value="<?php echo JText::_('MOD_SP_POLL_BUTTON_RESULT'); ?>">
				<?php } else { ?>
					<input type="button" class="btn btn-success btn-poll-result" value="<?php echo JText::_('MOD_SP_POLL_BUTTON_RESULT'); ?>" disabled>
				<?php } ?>
			</div>
		</form>
		<div class="sppoll-results"></div>
	<?php } else { ?>
		<p class="alert alert-warning"><?php echo JText::_('MOD_SP_POLL_NO_RECORDS'); ?></p>
	<?php } ?>
</div>