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

class RssFactoryRuleYouTube extends RssFactoryRule
{
    protected $label = 'YouTube Match';

    public function parse($params, $page, &$content, $debug)
    {
        $html = array();
        $index = $params->get('index', 1);
        $selector = $params->get('selector');
        $height = $params->get('resize.height');
        $width = $params->get('resize.width');
        $counter = 0;

        $dom = HtmlDomParser::str_get_html($page);
        $iframes = $dom->find($selector . ' iframe');

        foreach ($iframes as $iframe) {
            if (false === strpos($iframe->src, 'http://www.youtube.com/')) {
                continue;
            }

            $counter++;

            if ($index && $index != $counter) {
                continue;
            }

            $style = array();

            if ($height) {
                $style[] = 'max-height: ' . $height . 'px;';
            }

            if ($width) {
                $style[] = 'max-width: ' . $width . 'px;';
            }

            $iframe->style = implode(' ', $style);

            $html[] = $iframe->outertext;
        }

        return implode("\n", $html);
    }
}
