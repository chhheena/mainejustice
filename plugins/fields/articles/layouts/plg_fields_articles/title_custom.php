<?php
/**
 * @package         Articles Field
 * @version         3.8.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text as JText;

/*
 * @var array  $displayData
 * @var object $article
 * @var object $settings
 */
extract($displayData);

$title = htmlentities($article->title);

if ($settings->link_title)
{
	if ( ! class_exists('ContentHelperRoute'))
	{
		require_once JPATH_SITE . '/components/com_content/helpers/route.php';
	}

	$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
	$link = JRoute::_(ContentHelperRoute::getArticleRoute($slug, $article->catid, $article->language));

	$title = '<a href="' . $link . '">' . $title . '</a>';
}

// No custom field found. Just return title
if (empty($settings->custom_field))
{
	echo $title;

	return;
}

$fields = FieldsHelper::getFields('com_content.article', $article);

// No custom fields found. Just return title
if (empty($fields))
{
	return $title;
}

foreach ($fields as $field)
{
	if ($field->id != $settings->custom_field)
	{
		continue;
	}

	// field has no value
	if (empty($field->value))
	{
		break;
	}

	PlgFieldsArticlesHelper::prepareCustomField('com_content.article', $article, $field);

	echo JText::sprintf('FLDA_OUTPUT_TITLE_CUSTOM_FIELD', $title, $field->value);

	return;
}

// No custom field found. Just return title
echo $title;
