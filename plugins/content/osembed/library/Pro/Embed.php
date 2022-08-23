<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSEmbed\Pro;

defined('_JEXEC') or die();

use Embera\Embera;
use Embera\Formatter;
use Alledia\OSEmbed\Free\Embed as FreeEmbed;

jimport('joomla.log.log');


abstract class Embed extends FreeEmbed
{
    public static function parseContent($content, $stripNewLine = false)
    {
        // Initialise the Embera library
        if (!isset(static::$embera)) {
            static::$embera = new Embera;
        }

        // Add Google Maps support
        static::$embera->addProvider('google.com', '\\Alledia\\OSEmbed\\Pro\\Provider\\GoogleMaps');
        static::$embera->addProvider('google.com.*', '\\Alledia\\OSEmbed\\Pro\\Provider\\GoogleMaps');
        static::$embera->addProvider('maps.google.com', '\\Alledia\\OSEmbed\\Pro\\Provider\\GoogleMaps');

        // Add Google Docs support
        static::$embera->addProvider('docs.google.com', '\\Alledia\\OSEmbed\\Pro\\Provider\\GoogleDocs');

        // Convert any missed ::__at__:: to @, a workaround for the autolink issue in the JCE editor
        $content = str_replace('::__at__::', '@', $content);

        return parent::parseContent($content, $stripNewLine);
    }

    protected static function replaceParseTokens($content)
    {
        $content = preg_replace('~osembed(s?)://~i', 'http$1://', $content);
        $content = str_replace('::__at__::', '@', $content);

        return $content;
    }

    public static function onContentBeforeSave($article)
    {
        if (isset($article->introtext)) {
            $article->introtext = static::replaceParseTokens($article->introtext);
            $article->introtext = static::removeEmbedWrapperFromContent($article->introtext);
        }

        if (isset($article->text)) {
            $article->text = static::replaceParseTokens($article->text);
            $article->text = static::removeEmbedWrapperFromContent($article->text);
        }

        return parent::onContentBeforeSave($article);
    }

    /**
     * This method removes the wrapper div from around the url. This wrapper
     * can be left as a leftover if the embed preview was being loaded and
     * didn't finish before the form was saved. So the save action, will
     * register the wrapper in the code.
     *
     * @param  string $content The content
     * @return string          The content cleaned up
     */
    protected static function removeEmbedWrapperFromContent($content)
    {
        $content = preg_replace(
            '~<div\sid="osembed_wrapper_[^"]+"\sclass="[^"]*osembed_wrapper[^"]*"\s[^"]*'
                . 'data-url="([^"]+)"[^>]+>[^<]*</div>~',
            '$1',
            $content
        );

        return $content;
    }

    /**
     * Get the list of tags to ignore. Override the function from the Free lib.
     *
     * @return array
     */
    public static function getIgnoreTags()
    {
        if (!isset(static::$ignoreTags)) {
            $plugin = \JPluginHelper::getPlugin('content', 'osembed');
            $params = new \JRegistry($plugin->params);

            static::$ignoreTags = $params->get('ignore_tags', 'pre, code, a, img, iframe');
            static::$ignoreTags = explode(',', static::$ignoreTags);

            if (!empty(static::$ignoreTags)) {
                static::$ignoreTags = array_map('trim', static::$ignoreTags);
            }
        }

        return static::$ignoreTags;
    }
}
