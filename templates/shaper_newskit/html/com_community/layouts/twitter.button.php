<?php
    if ($authurl) {
?>
        <a href="<?php echo $authurl;?>" class="btn-sign-with-twitter"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_TWITTER') ?></a>
<?php
    } else {
        echo JText::_('COM_COMMUNITY_TWITTER_FAILED_REQUEST_TOKEN');
    }
?>