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

use voku\helper\HtmlDomParser;

class RssFactoryRuleDomParser extends RssFactoryRule
{
    protected $label = 'Dom Parser';

    public function parse($params, $page, &$content, $debug)
    {
        $selector = $params->get('selector');
        $index = $params->get('index', 0);
        $stripHtml = $params->get('strip_html', 0);
        $allowedTags = $params->get('allowed_tags', '');

        $html = HtmlDomParser::str_get_html($page);
        $text = $html->find($selector, $index);

        if (!$text) {
            return false;
        }

        $text = $text->innertext;

        // Strip tags.
        $text = $this->stripTags($stripHtml, $allowedTags, $text);

        return $text;
    }
}
