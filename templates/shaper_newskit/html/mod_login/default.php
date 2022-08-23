<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$user = JFactory::getUser();
?>
<div class="sp-newskit-login sp-mod-login">
    <span class="sp-login">
        <span class="info-text">
            <a href="#" role="button" data-toggle="modal" data-target="#login">
                <span class="info-content">
                    <i class='newskit newskit-user'></i>
                    <span><?php echo JText::_('NEWSKIT_LOGIN'); ?></span>
                </span>
            </a>
        </span>

        <!--Modal-->
        <div id="login" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i></button>
                        <h3><?php echo ($user->id > 0) ? JText::_('MY_ACCOUNT') : JText::_('NEWSKIT_LOGIN'); ?></h3>
                    </div>
                    <div class="modal-body">

                        <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
                            <?php if ($params->get('pretext')): ?>
                                <div class="pretext">
                                    <p><?php echo $params->get('pretext'); ?></p>
                                </div>
                            <?php endif; ?>
                            <fieldset class="userdata">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <p><?php echo JText::_('MOD_LOGIN_ENTER_USERNAME') ?></p>
                                        <input id="modlgn-username" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>" type="text" name="username" class="input-block-level"  />
                                        <p><?php echo JText::_('ENTER YOUR PASSWORD') ?></p>
                                        <input id="modlgn-passwd" type="password" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" name="password" class="input-block-level" />
                                    </div>
                                </div>
                                <p></p>
                                <div class="clearfix">
                                    <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                                        <div class="modlgn-remember remember-wrap">
                                            <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
                                            <label for="modlgn-remember"><?php echo JText::_('NEWSKIT_REMEMBER_ME') ?></label>
                                        </div>
                                    <?php endif; ?>
                                    <input type="submit" name="Submit" class="button pull-right" value="<?php echo JText::_('NEWSKIT_LOGIN') ?>" />
                                </div>
                                <div class="forgot-password text-center">

<!--                                    <a href="<?php //echo JRoute::_('index.php?option=com_users&view=remind');                        ?>">

</a> -->

                                    <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                                        <?php echo JText::_('Forgot'); ?>
                                        <?php echo JText::_('Password'); ?>
                                        <?php echo jText::_('or'); ?>
                                        <?php echo JText::_('Username'); ?>
                                    </a>
                                </div>

                                <input type="hidden" name="option" value="com_users" />
                                <input type="hidden" name="task" value="user.login" />
                                <input type="hidden" name="return" value="<?php echo $return; ?>" />
                                <?php echo JHtml::_('form.token'); ?>
                            </fieldset>
                            <?php if ($params->get('posttext')): ?>
                                <div class="posttext">
                                    <p><?php echo $params->get('posttext'); ?></p>
                                </div>
                            <?php endif; ?>
                        </form>
                        <div class="create-account text-center">
                            <?php
                            $usersConfig = JComponentHelper::getParams('com_users');
                            if ($usersConfig->get('allowUserRegistration')) :
                                ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
                                    <?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
                            <?php endif; ?>
                        </div><!--/.create-account-->
                    </div>
                    <!--/Modal body-->

                </div> <!-- Modal content-->
            </div> <!-- /.modal-dialog -->
        </div><!--/Modal-->
</div>