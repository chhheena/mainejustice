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

class RssFactoryRuleImage extends RssFactoryRule
{
    protected $label = 'Image Match';

    public function parse($params, $page, &$content, $debug)
    {
        $html = array();
        $counter = 0;
        $index = $params->get('index', '');
        $filter = $params->get('filter');
        $selector = $params->get('selector');
        $maxWidth = $params->get('resize.width');
        $maxHeight = $params->get('resize.height');
        $srcPrepend = $params->get('src.prepend', '');

        $dom = HtmlDomParser::str_get_html($page);

        if (!$dom) {
            return '';
        }

        $images = $dom->find($selector . ' img');

        foreach ($images as $image) {
            if ('' != $filter) {
                if (false === stripos($image->src, $filter) &&
                    false === stripos($image->alt, $filter) &&
                    false === stripos($image->title, $filter)
                ) {
                    continue;
                }
            }

            $counter++;

            if ($index && $index != $counter) {
                continue;
            }

            $style = array();

            if ($maxWidth) {
                $style[] = 'max-width: ' . $maxWidth . 'px;';
            }

            if ($maxHeight) {
                $style[] = 'max-height: ' . $maxHeight . 'px;';
            }

            $src = $this->getSource($image->src, $srcPrepend);

            $html[] = '<img src="' . $src . '" style="' . implode(' ', $style) . '" />';
        }

        return implode("\n", $html);
    }

    private function getSource($source, $prepend)
    {
        if ('' === $prepend) {
            return $source;
        }

        if (0 === strpos($source, $prepend)) {
            return $source;
        }

        return $prepend . $source;
    }
}
