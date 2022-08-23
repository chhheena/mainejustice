<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

$showAvatar = $params->get('show_avatar', 1);
$showKarma  = $params->get('enablekarma', 1);

?>

<div>
    <?php if ( !empty($users) ) { ?>
    <ul class="joms-list">
        <?php foreach ($users as $user) { ?>
            <li>
                <?php if ( $showAvatar ) { ?>
                <div class="joms-popover__avatar">
                    <div class="joms-avatar">
                        <img src="<?php echo $user->avatar; ?>"
                            title="<?php echo JText::sprintf('MOD_TOPMEMBERS_GO_TO_PROFILE', CStringHelper::escape( $user->name ) ); ?>"
                            alt="<?php echo CStringHelper::escape( $user->name ); ?>"
                            data-author="<?php echo $user->id; ?>">
                    </div>
                </div>
                <?php } ?>
                <div class="joms-popover__content">
                    <h5><a href="<?php echo $user->link; ?>"><?php echo $user->name; ?></a></h5>
                    <?php if ( $showKarma == 1 ) { ?>
                    <?php
                        $badge = new CBadge( CFactory::getUser($user->id) );
                        $badge = $badge->getBadge();
                    ?>
                    <img src="<?php echo $badge->current->image; ?>" alt="<?php echo JText::_('MOD_HELLOME_KARMA'); ?>" style="margin-left:-3px;">
                    <?php } else if ( $showKarma == 2 ) { ?>
                    <small><?php echo JText::_('MOD_TOPMEMBERS_POINTS') , ': ', $user->userpoints; ?></small>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <?php echo JText::_('MOD_TOPMEMBERS_NO_MEMBERS'); ?>
    <?php } ?>
</div>
