<script type="text/javascript">
    var googleUser = {};
    var startApp = function() {
        gapi.load('auth2', function(){
            // Retrieve the singleton for the GoogleAuth library and set up the client.
            auth2 = gapi.auth2.init({
                client_id: '<?php echo $config->get('googleclientid') ?>',
                cookiepolicy: 'single_host_origin',
                // Request scopes in addition to 'profile' and 'email'
                //scope: 'additional_scope'
            });
            attachSignin(document.getElementById('btn-google<?php echo $prefix ?>'));
        });
    };

    function addOrUpdateUrlParam(uri, paramKey, paramVal) {
        var re = new RegExp("([?&])" + paramKey + "=[^&#]*", "i");

        if (re.test(uri)) {
            uri = uri.replace(re, '$1' + paramKey + "=" + paramVal);
        } else {
            var separator = /\?/.test(uri) ? "&" : "?";
            uri = uri + separator + paramKey + "=" + paramVal;
        }

        return uri;
    }

    function attachSignin(element) {
        console.log(element.id);
        auth2.attachClickHandler(element, {},

        function(googleUser) {
            var redirectURI = '<?php echo JURI::root().'index.php?option=com_community&view=oauth&task=callback&app=google' ?>';
            
            redirectURI = addOrUpdateUrlParam(redirectURI, 'googleid', googleUser.getBasicProfile().getId());
            redirectURI = addOrUpdateUrlParam(redirectURI, 'googlename', encodeURIComponent(googleUser.getBasicProfile().getName()));
            redirectURI = addOrUpdateUrlParam(redirectURI, 'googleemail', encodeURIComponent(googleUser.getBasicProfile().getEmail()));
            redirectURI = addOrUpdateUrlParam(redirectURI, 'googlepic', encodeURIComponent(googleUser.getBasicProfile().getImageUrl())); 
            
            window.location.href = redirectURI;
        }, function(error) {
            //console.log(JSON.stringify(error, undefined, 2));
        });
    }
</script>
<div id="btn-google<?php echo $prefix ?>" class="btn-sign-with-google"><?php echo JText::_('COM_COMMUNITY_SIGN_IN_WITH_GOOGLE') ?></div>
<script type="text/javascript">startApp();</script>