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
?>

<div class="joms-page">
    <h3 class="joms-page__title"><?php echo JText::_($isNew ? 'COM_COMMUNITY_PAGES_CREATE_REVIEW' : 'COM_COMMUNITY_PAGES_EDIT_REVIEW'); ?></h3>
    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PAGES_REVIEW_RATINGS'); ?> <span class="joms-required">*</span></span>
            <div class="rate">
                <input type="radio" id="star5" name="rate" value="5" <?php echo ($rating->rating == 5) ? 'checked' : '' ?> />
                <label for="star5" title="text">5 stars</label>
                <input type="radio" id="star4" name="rate" value="4" <?php echo ($rating->rating == 4) ? 'checked' : '' ?> />
                <label for="star4" title="text">4 stars</label>
                <input type="radio" id="star3" name="rate" value="3" <?php echo ($rating->rating == 3) ? 'checked' : '' ?> />
                <label for="star3" title="text">3 stars</label>
                <input type="radio" id="star2" name="rate" value="2" <?php echo ($rating->rating == 2) ? 'checked' : '' ?> />
                <label for="star2" title="text">2 stars</label>
                <input type="radio" id="star1" name="rate" value="1" <?php echo ($rating->rating == 1) ? 'checked' : '' ?> />
                <label for="star1" title="text">1 star</label>
            </div>
        </div>
        <div class="joms-form__group" style="margin-bottom:5px">
            <span><?php echo JText::_('COM_COMMUNITY_PAGES_REVIEW_TITLE'); ?> <span class="joms-required">*</span></span>
            <input type="text" class="joms-input" name="title" required=""
                title="<?php echo JText::_('COM_COMMUNITY_PAGES_REVIEW_TITLE_TIPS'); ?>"
                value="<?php echo $this->escape($rating->title); ?>">
        </div>
        <div class="joms-form__group">
            <span title="<?php echo JText::_('COM_COMMUNITY_PAGES_YOUR_REVIEW_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PAGES_YOUR_REVIEW'); ?> <span class="joms-required">*</span></span>
            <textarea class="joms-textarea" name="review" required=""><?php echo $this->escape($rating->review); ?></textarea>
        </div>
        <div class="joms-form__group">
            <span></span>
            <div>
                <input name="action" type="hidden" value="save">
                <input type="hidden" name="ratingid" value="<?php echo $rating->id; ?>">
                <?php echo JHTML::_('form.token'); ?>
                <input type="button" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" class="joms-button--neutral joms-button--full-small" onclick="history.go(-1); return false;">
                <input type="submit" value="<?php echo JText::_($isNew ? 'COM_COMMUNITY_PAGES_CREATE_REVIEW' : 'COM_COMMUNITY_SAVE_BUTTON'); ?>" class="joms-button--primary joms-button--full-small">
            </div>
        </div>

    </form>
</div>
<script>
    function joms_validate_form() {
        return false;
    }

    (function( w ) {
        w.joms_queue || (w.joms_queue = []);
        w.joms_queue.push(function() {
            joms_validate_form = function( $form ) {
                var errors = 0;

                $form = joms.jQuery( $form );
                $form.find('[required]').each(function() {
                    var $el = joms.jQuery( this );
                    if ( !joms.jQuery.trim( $el.val() ) ) {
                        $el.triggerHandler('blur');
                        errors++;
                    }
                });

                return !errors;
            }
        });
    })( window );
</script>
