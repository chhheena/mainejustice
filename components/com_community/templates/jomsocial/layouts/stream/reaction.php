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
$reactions = CStringHelper::getReactionData();
?>
<div class="joms-reactions">
	<ul class="joms-reactions__list">
		<?php foreach ($reactions as $item): ?>
		<li>
			<div class="joms-reactions__inner">
				<div
					class="joms-reactions__item reaction-<?php echo strtolower($item->name) ?>"
					data-react-id="<?php echo $item->id ?>"
					data-name="<?php echo $item->name ?>"
					data-text="<?php echo $item->text ?>" ></div>
				<div class="joms-reactions__text"><?php echo $item->text ?></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>