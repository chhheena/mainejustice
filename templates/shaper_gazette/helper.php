<?php

/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;

class TplShaperGazetteHelper
{
    public static function getAjax()
    {
        self::articlerating();
    }

    public static function articlerating()
    {
        $output = array();
        $output['status'] = false;
        $output['message'] = 'Invalid Token';
        Session::checkToken() or die(json_encode($output));

        $app = Factory::getApplication();
        $input = $app->input;
        $article_id = (int) $input->post->get('article_id', 0, 'INT');
        $rating = (int) $input->post->get('rating', 0, 'INT');

        $userIP = $_SERVER['REMOTE_ADDR'];
        $lastip = '';
        $last_rating = self::getRating($article_id);

        if(isset($last_rating->lastip) && $last_rating->lastip)
        {
            $lastip = $last_rating->lastip;
        }

        if($userIP == $lastip)
        {
            $output['status'] = false;
            $output['message'] = Text::_('HELIX_ALREADY_RATED');
            $output['rating_count'] = (isset($last_rating->rating_count) && $last_rating->rating_count) ? $last_rating->rating_count : 0;
        }
        else
        {
            $newRatings = self::addRating($article_id, $rating, $userIP);

            $output['status'] = true;
            $output['message'] = Text::_('HELIX_THANK_YOU');

            $rating = round($newRatings->rating_sum/$newRatings->rating_count);
            $output['rating_count'] = $newRatings->rating_count;

            $output['ratings'] = '';
            $j = 0;
            for($i = $rating; $i < 5; $i++)
            {
                $output['ratings'] .= '<span class="rating-star" data-number="'.(5-$j).'"></span>';
                $j = $j+1;
            }
            for ($i = 0; $i < $rating; $i++)
            {
                $output['ratings'] .= '<span class="rating-star active" data-number="'.($rating - $i).'"></span>';
            }
        }

        die(json_encode($output));
    }

    private static function addRating($id, $rating, $ip)
    {
        $db = Factory::getDbo();
        $lastRating = self::getRating($id);

        $userRating = new stdClass();
        $userRating->content_id = $id;
        $userRating->lastip = $ip;

        if(isset($lastRating->rating_count) && $lastRating->rating_count)
        {
            $userRating->rating_sum = ($lastRating->rating_sum + $rating);
            $userRating->rating_count = ($lastRating->rating_count + 1);
            $db->updateObject('#__content_rating', $userRating, 'content_id');
        }
        else
        {
            $userRating->rating_sum = $rating;
            $userRating->rating_count = 1;
            $db->insertObject('#__content_rating', $userRating);
        }

        return self::getRating($id);
    }

    private static function getRating($id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__content_rating'))
            ->where($db->quoteName('content_id') . ' = ' . (int) $id);

        $db->setQuery($query);
        $data = $db->loadObject();

        return $data;
    }

    // get Tag list
    public static function getTagList($params = '')
	{
        $db          = Factory::getDbo();
		$user        = Factory::getUser();
		$groups      = implode(',', $user->getAuthorisedViewLevels());
		$maximum     = 8;
		$order_value = 'title';
		$nowDate     = Factory::getDate()->toSql();
		$nullDate    = $db->quote($db->getNullDate());

		$query = $db->getQuery(true)
			->select(
				array(
					'MAX(' . $db->quoteName('tag_id') . ') AS tag_id',
					' COUNT(*) AS count', 'MAX(t.title) AS title',
					'MAX(' . $db->quoteName('t.access') . ') AS access',
					'MAX(' . $db->quoteName('t.alias') . ') AS alias',
					'MAX(' . $db->quoteName('t.params') . ') AS params',
				)
			)
			->group($db->quoteName(array('tag_id', 'title', 'access', 'alias')))
			->from($db->quoteName('#__contentitem_tag_map', 'm'))
			->where($db->quoteName('t.access') . ' IN (' . $groups . ')');

		// Only return published tags
		$query->where($db->quoteName('t.published') . ' = 1 ');

		// Optionally filter on language
		$language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');
        $query->where($db->quoteName('t.language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');

		$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('tag_id') . ' = t.id')
		->join('INNER', $db->qn('#__ucm_content', 'c') . ' ON ' . $db->qn('m.core_content_id') . ' = ' . $db->qn('c.core_content_id'));

		$query->where($db->quoteName('m.type_alias') . ' = ' . $db->quoteName('c.core_type_alias'));

		// Only return tags connected to published articles
		$query->where($db->quoteName('c.core_state') . ' = 1')
			->where('(' . $db->quoteName('c.core_publish_up') . ' = ' . $nullDate
				. ' OR ' . $db->quoteName('c.core_publish_up') . ' <= ' . $db->quote($nowDate) . ')')
			->where('(' . $db->quoteName('c.core_publish_down') . ' = ' . $nullDate
				. ' OR  ' . $db->quoteName('c.core_publish_down') . ' >= ' . $db->quote($nowDate) . ')');

		// Set query depending on order_value param
		if ($order_value === 'rand()')
		{
			$query->order($query->Rand());
		}
		else
		{
			$order_value     = $db->quoteName($order_value);
			$order_direction = 'DESC';

			$query->order($order_value . ' ' . $order_direction);
		}

		$db->setQuery($query, 0, $maximum);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$results = array();
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		return $results;
	}
}