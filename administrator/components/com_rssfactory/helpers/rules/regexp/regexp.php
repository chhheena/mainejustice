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

class RssFactoryRuleRegExp extends RssFactoryRule
{
    protected $label = 'Regular Expression';

    public function parse($params, $page, &$content, $debug)
    {
        $expression = $params->get('expression');
        $match = $params->get('match', 0);
        $stripHtml = $params->get('strip_html', 0);
        $allowedTags = $params->get('allowed_tags', '');

        if (!preg_match($expression, $page, $matches)) {
            return false;
        }

        if (!isset($matches[$match])) {
            return false;
        }

        // Strip tags.
        $text = $this->stripTags($stripHtml, $allowedTags, $matches[$match]);

        return $text;
    }
}
