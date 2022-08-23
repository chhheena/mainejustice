<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSEmbed\Pro\Provider;

defined('_JEXEC') or die();

use Embera;
use Embera\Adapters\Service;


class GoogleDocs extends Service
{
    public function validateUrl()
    {
        return preg_match('~http[s]?:\/\/((?:www\.)?docs\.google\.com\/(?:document|presentation|spreadsheets|forms|drawings)\/[a-z0-9\/\?=_\-\.\,&%\$#\@\!\+]*)~i', $this->url);
    }

    /**
     * This method fakes a Oembed response.
     *
     * @return array with data that the oembed response should have
     * @codeCoverageIgnore
     */
    public function fakeResponse()
    {
        $iframeSrc = html_entity_decode($this->url);

        // Check the type of document
        preg_match('~google\.com/(document|presentation|spreadsheets|forms|drawings)/~i', $iframeSrc, $matches);
        $type = $matches[1];

        switch ($type) {
            case 'document':
                // Check if the url still doesn't have the embedded param, and add if needed
                if (!preg_match('~([?&])embedded=true~i', $iframeSrc, $matches)) {
                    if (substr_count($iframeSrc, '?')) {
                        $iframeSrc .= '&embedded=true';
                    } else {
                        $iframeSrc .= '?embedded=true';
                    }
                }
                break;

            case 'presentation':
                // Convert the /pub to /embed if needed
                if (preg_match('~/pub\?~i', $iframeSrc)) {
                    $iframeSrc = str_replace('/pub?', '/embed?', $iframeSrc);
                }
                break;

            case 'spreadsheets':
                if (substr_count($iframeSrc, '?')) {
                    $query = explode('?', $iframeSrc);
                    $query = $query[1];
                    $query = explode('&', $query);

                    if (!empty($query)) {
                        $hasWidgetParam = false;
                        $hasHeadersParam = false;

                        foreach ($query as $param) {
                            if (substr_count($param, 'widget=')) {
                                $hasWidgetParam = true;
                            } elseif (substr_count($param, 'headers=')) {
                                $hasHeadersParam = true;
                            }
                        }

                        if (!$hasWidgetParam) {
                            $iframeSrc .= '&widget=true';
                        }

                        if (!$hasHeadersParam) {
                            $iframeSrc .= '&headers=false';
                        }
                    }
                } else {
                    $iframeSrc .= '?widget=true&headers=false';
                }
                break;

            case 'forms':
            case 'drawings':
                break;
        }


        if ($type !== 'drawings') {
            $html = '<iframe src="' . $iframeSrc . '" frameborder="0" width="600" height="450" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>';
        } else {
            $html = '<img src="' . $iframeSrc . '" width="960" height="720" />';
        }

        return array(
            'type' => 'rich',
            'provider_name' => 'Google Docs',
            'provider_url' => 'http://docs.google.com',
            'title' => 'Unknown title',
            'html' => $html,
            'wrapper_class' => 'ose-google-docs-' . $type
        );
    }
}
