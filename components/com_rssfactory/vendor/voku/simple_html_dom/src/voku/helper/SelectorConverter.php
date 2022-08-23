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

declare(strict_types=1);

namespace voku\helper;

defined('_JEXEC') or die;

use Symfony\Component\CssSelector\CssSelectorConverter;

class SelectorConverter
{
    /**
     * @var array
     */
    protected static $compiled = [];

    /**
     * @param string $selector
     *
     * @throws \RuntimeException
     *
     * @return mixed|string
     */
    public static function toXPath(string $selector)
    {
        if (isset(self::$compiled[$selector])) {
            return self::$compiled[$selector];
        }

        // Select DOMText
        if ($selector === 'text') {
            return '//text()';
        }

        // Select DOMComment
        if ($selector === 'comment') {
            return '//comment()';
        }

        if (\strpos($selector, '//') === 0) {
            return $selector;
        }

        if (!\class_exists(CssSelectorConverter::class)) {
            throw new \RuntimeException('Unable to filter with a CSS selector as the Symfony CssSelector 2.8+ is not installed (you can use filterXPath instead).');
        }

        $converter = new CssSelectorConverter(true);

        $xPathQuery = $converter->toXPath($selector);
        self::$compiled[$selector] = $xPathQuery;

        return $xPathQuery;
    }
}
