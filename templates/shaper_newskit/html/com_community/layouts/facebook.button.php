<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** detect and display facebook language **/
if (!defined('FACEBOOK_LANG_AVAILABLE')) {
define('FACEBOOK_LANG_AVAILABLE', 1);
}

$lang = JFactory::getLanguage();
$currentLang =  $lang->get('tag');

$fbLang =   explode(',', trim(FACEBOOK_LANGUAGE) );
$currentLang = str_replace('-','_',$currentLang);
$fbLangScript = '<script src="https://connect.facebook.net/en_US/sdk.js" type="text/javascript"></script>';

if(in_array($currentLang,$fbLang)==FACEBOOK_LANG_AVAILABLE){
    $fbLangScript = '<script src="https://connect.facebook.net/'.$currentLang.'/sdk.js" type="text/javascript"></script>';
}

$fbLangScript = CUrlHelper::httpsURI($fbLangScript);
?>

<script>
// fix multiple #fb-root in a document
(function( w, d ) {
    if ( !d.getElementById('fb-root') ) {
        if ( !w.jQuery ) {
            d.write('<div id="fb-root"></div>');
        } else {
            w.jQuery( d.body ).append('<div id="fb-root"></div>');
        }
    }
}( window, document ));
</script>
<?php echo $fbLangScript; ?>
<script type="text/javascript">
function jomFbButtonInit(){
    FB.init({
        appId: '<?php echo $config->get('fbconnectkey');?>',
        status: true,
        cookie: true,
        oauth: true,
        xfbml: true,
        version: 'v3.2'
    });
}

if( typeof window.FB != 'undefined' ) {
    jomFbButtonInit();
} else {
    window.fbAsyncInit = jomFbButtonInit;
}
<?php

    $fbScope = array('email');
    $config = CFactory::getConfig();
    if($config->get('fbsignupimport') || $config->get('fbloginimportprofile')){
        //$fbScope[] = 'user_birthday';
        $fbScope[] = 'public_profile';
    }
?>
</script>

<!-- validate w3c -->
<div class="joms-login-facebook">
    <div class="login-facebook">
        <span class="btn-sign-with-facebook"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_FACEBOOK') ?></span>
        <div class="joms-fb-login-button" data-scope="<?php echo implode(',', $fbScope)?>"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_FACEBOOK') ?></div>
    </div>
</div>
<script>(function(d) {
    var o = d.getElementsByClassName('joms-fb-login-button')[0];
    o.setAttribute('onlogin', 'joms.api.fbcUpdate();');
    o.setAttribute('class', 'fb-login-button');
}(document));</script>
