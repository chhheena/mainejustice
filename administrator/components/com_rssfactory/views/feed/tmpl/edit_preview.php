<?php

/**
-------------------------------------------------------------------------
rssfactory - Rss Factory 4.3.6
-------------------------------------------------------------------------
 * @author thePHPfactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thePHPfactory.com
 * Technical Support: Forum - http://www.thePHPfactory.com/forum/
-------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

?>

<div class="modal hide fade" id="collapseModal2" style="width: 90%; left: 5%; margin: 0; top: 5%; height: 90%;">
    <div class="modal-header">
        <button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
        <h3><?php echo FactoryTextRss::_('feed_preview_modal_title'); ?></h3>
    </div>

    <div class="modal-body" style="max-height: inherit; padding: 20px;"></div>
</div>

<div class="modal hide" tabindex="-1" role="dialog" id="collapseModal4">
    <div class="modal-dialog" role="document" style="width: 90%; left: 5%; margin: 0; top: 5%; height: 90%; max-width: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo FactoryTextRss::_('feed_preview_modal_title'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;"></div>
        </div>
    </div>
</div>
