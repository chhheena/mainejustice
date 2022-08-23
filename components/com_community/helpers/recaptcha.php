<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

class CRecaptchaHelper
{

    public  $enabled = false;
    public  $v3 = false;
    public  $ip;

    private $theme;

    private $privateKey;
    private $publicKey;

    private $apiUrl;
    private $verifyUrl;

    /*
     * Load config data and object vars
     */
    public function __construct()
    {
        // Config data
        $config             = CFactory::getConfig();
        $this->enabled      = $config->get('nocaptcha', false);
        $this->v3           = $config->get('recaptchav3', false);
        $this->privateKey   = $config->get('nocaptchaprivate');
        $this->publicKey    = $config->get('nocaptchapublic');
        $this->theme        = $config->get('nocaptchatheme');
        $this->apiUrl       = $config->get('recaptcha_server');
        $this->verifyUrl    = $config->get('recaptcha_server_verify');

        // Grab the IP, remember load balancers
        // @todo is there a framework way to do it?
        $this->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        // If any of the vital vars is missing, disable the whole thing
        if (!$this->privateKey || !$this->publicKey || !$this->apiUrl || !$this->verifyUrl) {
            $this->enabled = false;
        }
    }

    /*
     * Return the Recaptcha HTML if enabled
     */
    public function html()
    {
        if (!$this->enabled) {
            return '';
        }

        return $this->v3 ? $this->htmlV3() : $this->htmlV2();
    }

    protected function htmlV2()
    {
        // start output buffer, if recaptcha is enabled add HTML and JS to the buffer
        ob_start(); ?>
                <div id="joms-recaptcha"></div>
                <script type="text/javascript">
                    var jomsRecaptchaCallback = function() {
                        grecaptcha.render("joms-recaptcha", {
                            "sitekey": "<?php echo $this->publicKey; ?>",
                            "theme": "<?php echo $this->theme; ?>",
                        })
                    };
                </script>
                <script src="<?php echo $this->apiUrl; ?>?onload=jomsRecaptchaCallback&render=explicit&hl=<?php echo JFactory::getLanguage()->getTag() ?>" async></script>
        <?php

        // get the contents of te buffer and return it
        $html = ob_get_clean();
        return $html;
    }

    protected function htmlV3()
    {
        $doc= JFactory::getDocument();
        $doc->addScriptOptions('joms_recaptcha_v3', true);
        $doc->addScriptOptions('joms_recaptcha_v3_key', $this->publicKey);
        $html = '
            <script src="https://www.google.com/recaptcha/api.js?render='.$this->publicKey.'" async ></script>
        ';

        return $html;
    }

    /*
     * Send a verification request
     */
    public function verify()
    {
        // if Recaptcha is not enabled, return true
        if (!$this->enabled) return true;

        return $this->v3 ? $this->verifyV3() : $this->verifyV2();
    }

    protected function verifyV2()
    {
         // get the Recaptcha response from the form data
         $response = JFactory::getApplication()->input->get('g-recaptcha-response');
         if (!$response) return false;
 
         // send it to verification server for confirmation
         $http = new JHttp();
 
         $result = $http->post(
             $this->verifyUrl,
             array(
                 'secret' => $this->privateKey,
                 'remoteip' => $this->ip,
                 'response' => $response,
             )
         );
 
         $result = json_decode($result->body);
         return ($result->success === true) ? true : false;
    }

    protected function verifyV3()
    {
        // get the Recaptcha response from the form data
        $token = JFactory::getApplication()->input->get('g-recaptcha-response');
        if (!$token) return false;

        // send it to verification server for confirmation
        $http = new JHttp();

        try {
            $response = $http->post(
                $this->verifyUrl,
                array(
                    'secret' => $this->privateKey,
                    'remoteip' => $this->ip,
                    'response' => $token,
                )
            );
        } catch (Exception $e) {
            return false;
        }

        if ($response->code !== 200) {
            return false;
        }

        $result = new JRegistry($response->body);
        if (!$result->get('success')) {
            return false;
        }

        $score = (float) $result->get('score');
        if ($score > 0.5) {
            return true;
        }

        return false;
    }
}
