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

class RssFactoryRuleEmail extends RssFactoryRule
{
    protected $label = 'Email';

    public function parse($params, $page, &$content, $debug)
    {
        $groups = $params->get('groups', array());
        $emails = $params->get('emails');
        $text = implode("\n", $content);

        if ($groups) {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true)
                ->select('u.email')
                ->from('#__users u')
                ->leftJoin('#__user_usergroup_map m ON m.user_id = u.id')
                ->where('m.group_id IN (' . implode(',', $groups) . ')');
            $results = $dbo->setQuery($query)
                ->loadAssocList('email');
        } else {
            $results = array();
        }

        if ('' != trim($emails)) {
            $emails = explode("\n", $emails);
            foreach ($emails as &$email) {
                $email = trim($email);
            }
        } else {
            $emails = array();
        }

        $emails = array_unique(array_merge(array_keys($results), $emails));

        if (!$emails) {
            return true;
        }

        if (!$debug) {
            $config = JFactory::getConfig();
            $mailer = JFactory::getMailer();

            foreach ($emails as $email) {
                $mailer->addRecipient($email);
            }

            $mailer->setSender($config->get('mailfrom'));
            $mailer->setBody($text);
            $mailer->setSubject(FactoryTextRss::_('rule_email_subject'));
            $mailer->IsHTML(true);

            $mailer->send();
        } else {
            return FactoryTextRss::sprintf('rule_email_debug_info', '<ul><li>' . implode('</li><li>', $emails) . '</li></ul>');
        }
    }
}
