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

class RssFactoryRuleReplace extends RssFactoryRule
{
    protected $label = 'Search & Replace';

    public function parse($params, $page, &$content, $debug)
    {
        $search = $params->get('search');
        $replace = $params->get('replace');
        $caseSensitive = $params->get('case_sensitive', 0);

        if ($debug) {
            return FactoryTextRss::sprintf('rule_replace_debug_info', $search, $replace);
        } else {
            foreach ($content as &$text) {
                if ($caseSensitive) {
                    $text = str_replace($search, $replace, $text);
                } else {
                    $text = str_ireplace($search, $replace, $text);
                }
            }
        }
    }
}
