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

class Import2ContentHelper
{
    public static function storeArticle($feed, $cache)
    {
        /* @var $cache RssFactoryTableCache */
        // Initialise variables.
        $configuration = JComponentHelper::getParams('com_rssfactory');

        // Check if global and feed's I2C are enabled.
        if (!$configuration->get('enable_import2content', 0) || !$feed->i2c_enabled) {
            return false;
        }

        if ($feed->i2c_full_article) {
            try {
                $cache->item_description = RssFactoryHelper::parseFullArticle($cache->item_link, $feed->getI2CRules());
            }
            catch (Exception $e) {
                $cache->item_description = 'Unable to parse full article. Test full Import 2 Content rules in preview mode.';
            }
        }

        $publishing_period = intval($feed->i2c_publishing_period) ? $feed->i2c_publishing_period : $configuration->get('i2c_default_publishing_period');
        $publish_down      = $publishing_period > 0 ? JFactory::getDate('+' . $publishing_period . ' days')->toSql() : JFactory::getDbo()->getNullDate();

        $published         = (-1 == $feed->i2c_published) ? $configuration->get('i2c_default_article_state', 1) : $feed->i2c_published;

        $content = self::getArticleContent($feed, $cache);

        $data = array();

        $data['catid']        = $feed->i2c_catid ? $feed->i2c_catid : $configuration->get('i2c_default_catid');
        $data['title']        = $cache->item_title;
        $data['created']      = $cache->item_date;
        $data['created_by']   = $feed->i2c_author ? $feed->i2c_author : $configuration->get('i2c_default_author');
        $data['state']        = $published;
        $data['language']     = '*';
        $data['publish_down'] = $publish_down;
        $data['access']       = $feed->params->get('i2c_access_level', $configuration->get('i2c_default_access_level'));
        $data['featured']     = $feed->params->get('i2c_frontpage', 0);
        $data['introtext']    = $content;

        $table = JTable::getInstance('Content', 'RssFactoryTable');
        $table->setFeed($feed);

        \Joomla\CMS\Factory::getApplication()->triggerEvent('onImport2ContentBeforeSave', array(
            'com_rssfactory',
            $table,
        ));

        if (!$table->save($data)) {
            return false;
        }

        return $table;
    }

    public static function reorderFeaturedArticles()
    {
        JLoader::register('ContentTableFeatured', JPATH_ADMINISTRATOR . '/components/com_content/tables/featured.php');
        $table = JTable::getInstance('Featured', 'ContentTable');
        $table->reorder();
    }

    private static function getArticleContent(RssFactoryTableFeed $feed, RssFactoryTableCache $cache)
    {
        $configuration = JComponentHelper::getParams('com_rssfactory');

        $prepend_text = ($feed->i2c_prepend ? $feed->i2c_prepend : $configuration->get('i2c_default_prepend_text')) . '<br />';
        $append_text  = '<br />' . ($feed->i2c_append ? $feed->i2c_append : $configuration->get('i2c_default_append_text'));

        $content = $prepend_text . $cache->getItemDescription() . $append_text;

        if ($feed->params->get('i2c_include_enclosures', false)) {
            $enclosures = unserialize(base64_decode($cache->item_enclosure));

            $thumbnails = [];

            foreach ($enclosures as $enclosure) {
                if (isset($enclosure['thumbnails']) && is_array($enclosure['thumbnails'])) {
                    foreach ($enclosure['thumbnails'] as $thumbnail) {
                        $thumbnails[] = '<img src="' . self::cacheImage($thumbnail) . '" />';
                    }
                }
            }

            $content .= '<div>' . implode($thumbnails) . '</div>';
        }

        return $content;
    }

    private static function cacheImage($image)
    {
        $md5 = md5($image);
        $parsedUrl = parse_url($image);
        $extension = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);

        $folder = substr($md5, 0, 2);
        $subFolder = substr($md5, 2, 2);
        $filename = substr($md5, 4) . '.' . $extension;

        if (!is_dir(JPATH_SITE . '/cache/com_rssfactory_enclosures')) {
            mkdir(JPATH_SITE . '/cache/com_rssfactory_enclosures');
        }

        if (!is_dir(JPATH_SITE . '/cache/com_rssfactory_enclosures/' . $folder)) {
            mkdir(JPATH_SITE . '/cache/com_rssfactory_enclosures/' . $folder);
        }

        if (!is_dir(JPATH_SITE . '/cache/com_rssfactory_enclosures/' . $folder . '/' . $subFolder)) {
            mkdir(JPATH_SITE . '/cache/com_rssfactory_enclosures/' . $folder . '/' . $subFolder);
        }

        file_put_contents(JPATH_SITE . '/cache/com_rssfactory_enclosures/' . $folder . '/' . $subFolder . '/' . $filename, RssFactoryHelper::remoteReadUrl($image));

        return \Joomla\CMS\Uri\Uri::root() . 'cache/com_rssfactory_enclosures/' . $folder . '/' . $subFolder . '/' . $filename;
    }
}
