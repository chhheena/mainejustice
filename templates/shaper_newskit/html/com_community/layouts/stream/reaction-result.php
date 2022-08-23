<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();
$reactData = CStringHelper::getReactionData();
$count = 0;
?>
<div class="joms-stream__reactions">
	<ul class="joms-reactions__list">
	<?php foreach ($reactions as $react): ?>
		<li style="z-index: <?php echo 100 - $count ?>;">
			<?php
			if ($count > 2) {
				break;
			}
			
			$current = array_filter( $reactData, function($item) use ($react) {
				return $react->reaction_id == $item->id;
			});
			$current = array_shift($current);
			//$onclick = $my->id ? "joms.popup.reaction('$element', $uid, $react->reaction_id)" : 'javascript:;';
			$onclick = "joms.popup.reaction('$element', $uid, $react->reaction_id)";
			$count++;
			?>
			<div 
				class="joms-reactions__item reaction-<?php echo $current->name ?>"
				title="<?php echo JText::sprintf('COM_COMMUNITY_REACTION_PEOPLE', $react->count) ?>"
				data-count="<?php echo $react->count ?>"
				data-reactid="<?php echo $react->reaction_id ?>"
				data-uid="<?php echo $uid ?>"
				onclick="<?php echo $onclick ?>">
			</div>
		</li>
	<?php endforeach ?>
	</ul>
	<?php 
		//$onclick = $my->id ? "joms.popup.reaction('$element', $uid)" : 'javascript:;' 
		$onclick = "joms.popup.reaction('$element', $uid)"; 
	?>
	<a 	class="joms-stream__reactions-text" 
		href="javascript:;"
		onclick="<?php echo $onclick ?>">
		<?php echo $reactionText ?></a>
</div>

