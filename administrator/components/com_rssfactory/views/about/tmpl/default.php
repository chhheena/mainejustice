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

<div class="row-fluid row">
    <?php if (!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2 col-2">
            <?php echo $this->sidebar; ?>
        </div>
    <?php endif; ?>

    <div id="j-main-container" class="<?php echo !empty($this->sidebar) ? 'span10 col-10' : 'span12 col-12'; ?>">
        <?php echo $this->aboutHelper->render(); ?>
    </div>
</div>
