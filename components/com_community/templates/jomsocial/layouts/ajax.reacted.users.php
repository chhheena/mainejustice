<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
$reactions = $displayData['reactions'];
$total = $displayData['total'];
$reactId = $displayData['reactId'];
$element = $displayData['element'];
$uid = $displayData['uid'];

$reactionData = CStringHelper::getReactionData();
$active = array_filter($reactions, function($i) use ($reactId) {
	return $i->reaction_id == $reactId;
});

$active = array_shift($active);
$users = array();
foreach ($active->userids as $id) {
	$users[] = CFactory::getUser($id);
}

$tmpl = new CTemplate();
$tmpl->set('users', $users);
$content = $tmpl->fetch('ajax.stream.showothers');
?>
<?php if ($displayData): ?>
<div class="joms-reacted__list joms-stream__reactions">
	<ul>
		<?php foreach ($reactions as $item): ?>
			<?php 
			$activeClass =  $item->reaction_id == $reactId ? 'active' : '';
			if ($item->reaction_id == 0) {
				$text = JText::_('COM_COMMUNITY_ALL');
				$name = 'all';
				$reactClass = '';
				$count = $item->count;
			} else {
				$react = array_filter($reactionData, function($i) use ($item) {
					return $i->id == $item->reaction_id;
				});

				$react = array_shift($react);
				$text = '';
				$name = $react->name;
				$reactClass = "joms-reactions__item reaction-$name";
				$count = $item->count;
			}
			?>
			<li class="joms-reacted__item item-<?php echo $name ?> <?php echo $activeClass ?>" 
				data-element="<?php echo $element ?>"
				data-reactid="<?php echo $item->reaction_id ?>"
				data-uid="<?php echo $uid ?>">

				<div class="<?php echo $reactClass ?>"><?php echo $text ?></div>
				<div><?php echo $count ?></div>
			</li>
		<?php endforeach ?>
	</ul>
	
</div>
<div class="joms-reacted__content">
	<?php echo $content ?>
</div>
<div class="joms-js--loading" style="display:none; text-align:center;">
    <img src="<?php echo JUri::root() ?>components/com_community/assets/ajax-loader.gif" alt="loader">
</div>
<?php endif ?>