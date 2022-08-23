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

class RssFactoryRuleSimpleMatch extends RssFactoryRule
{
    protected $label = 'Simple Match';

    public function parse($params, $page, &$content, $debug)
    {
        // Initialise variables.
        $cs_start = $params->get('start.case_sensitive', 0);
        $cs_end = $params->get('end.case_sensitive', 0);
        $start_from = $params->get('start.position');
        $end_at = $params->get('end.position');
        $stripHtml = $params->get('strip_html', 0);
        $allowedTags = $params->get('allowed_tags', '');

        // Get start position.
        if ($cs_start) {
            $start = strpos($page, $start_from);
        } else {
            $start = stripos($page, $start_from);
        }

        // Get end position.
        if ($cs_end) {
            $end = strpos($page, $end_at);
        } else {
            $end = stripos($page, $end_at);
        }

        // Check if positions are valid.
        if (false === $start || false === $end || $end < $start) {
            return '';
        }

        // Get the text.
        $text = substr($page, $start, $end - $start);

        // Strip tags.
        $text = $this->stripTags($stripHtml, $allowedTags, $text);

        return $text;
    }
}
