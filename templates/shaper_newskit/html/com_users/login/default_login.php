<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

//print_r(get_class_methods($this->form));
//die;

$doc = JFactory::getDocument();
$app = JFactory::getApplication();

$tmp_params = JFactory::getApplication()->getTemplate('true')->params;

?>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3 text-center">
        <div class="reg-login-form-wrap">

            <div class="reg-login-title">
                <h2><?php echo JText::_('COM_USERS_LOGIN_TITLE'); ?></h2>
            </div>

            <div class="login<?php echo $this->pageclass_sfx ?>">
                <?php if ($this->params->get('show_page_heading')) : ?>
                    <h1>
                        <?php echo $this->escape($this->params->get('page_heading')); ?>
                    </h1>
                <?php endif; ?>
                <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
                    <div class="login-description">
                    <?php endif; ?>
                    <?php if ($this->params->get('logindescription_show') == 1) : ?>
                        <?php echo $this->params->get('login_description'); ?>
                    <?php endif; ?>
                    <?php if (($this->params->get('login_image') != '')) : ?>
                        <img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USERS_LOGIN_IMAGE_ALT') ?>"/>
                    <?php endif; ?>
                    <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-validate">
                    <?php
                    /* Set placeholder for username, password and secretekey */
                    $this->form->setFieldAttribute('username', 'hint', JText::_('COM_USERS_LOGIN_USERNAME_LABEL'));
                    $this->form->setFieldAttribute('password', 'hint', JText::_('JGLOBAL_PASSWORD'));
                    $this->form->setFieldAttribute('secretkey', 'hint', JText::_('JGLOBAL_SECRETKEY'));
                    ?>
                    <?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
                        <?php if (!$field->hidden) : ?>
                            <div class="form-group">
                                <p><?php echo $field->label; ?></p>
                                <div class="group-control">
                                    <?php echo $field->input; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($this->tfa): ?>
                        <div class="form-group">
                            <div class="group-control">
                                <?php echo $this->form->getField('secretkey')->input; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="submit-wrap">
                        <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                            <div class="checkbox">
                               <label>
                               <input id="remember" type="checkbox" name="remember" class="inputbox" value="yes">
                            <?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME')  ?>
                               </label>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <?php echo JText::_('JLOGIN'); ?>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
            </div>
            <div class="form-links">
                <ul>
                    <li>
                        <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                            <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a>
                    </li>
                    <li>
                      <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind');   ?>">
                        <?php echo JText::_('COM_USERS_LOGIN_REMIND');  ?></a>
                   </li>
                </ul>

            </div>
        </div>

        <?php
        $usersConfig = JComponentHelper::getParams('com_users');
        if ($usersConfig->get('allowUserRegistration')) :
            ?>
            <div class="new-account-link">
                <span><?php echo JText::_('COM_USERS_LOGIN_DONT_HAVE_AN_ACCOUNT'); ?></span>
                <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
                    <?php echo JText::_('COM_USERS_NEW_ACCOUNT'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
