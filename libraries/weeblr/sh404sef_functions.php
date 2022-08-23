<?php
/**
 * sh404SEF hooks file: this file is automatically loaded by sh404SEF and you can use it
 * to create deep customization of sh404SEF behavior. We have added a few examples of
 * hooks but there are more.
 *
 * Note that we do not provide any support for using hooks. Please always mention that you created
 * custom hooks when asking for support on any other topic.
 *
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Customize OGP tags
 */
//function customizeOGPOrTCardsTags($tags)
//{
//	$app    = Factory::getApplication();
//	$option = $app->input->getCmd('option');
//	$view   = $app->input->getCmd('view');
//	$id     = $app->input->getCmd('id');
//	if ('com_content' == $option && 'article' == $view)
//	{
//		// this is an article being displayed
//		// load the article and get the intro image
//		$article = Table::getInstance('Content');
//		$article->load($id);
//		if (!empty($article->images))
//		{
//			$images = json_decode($article->images);
//			if (!empty($images->image_intro))
//			{
//				// there is an intro image, use that
//				$tags['image'] = ShlSystem_Route::absolutify($images->image_intro, true);
//			}
//		}
//	}
//
//	return $tags;
//}

/**
 * Filter the list of OGP tags as computed by sh404SEF.
 *
 * @api
 * @package sh404SEF\filter\seo
 * @var sh404sef_ogp_tags
 *
 * @param array $displayData Associative array of OGP related data.
 *
 * @return array
 * @since   1.9.2
 *
 */
//ShlHook::add(
//	'sh404sef_ogp_tags',
//	'customizeOGPOrTCardsTags'
//);

/**
 * Filter the list of Twitter cards as computed by sh404SEF.
 *
 * @api
 * @package sh404SEF\filter\seo
 * @var sh404sef_tcards_tags
 *
 * @param array $displayData Associative array of Twitter cards related data.
 *
 * @return array
 * @since   1.9.2
 *
 */
//ShlHook::add(
//	'sh404sef_tcards_tags',
//	'customizeOGPOrTCardsTags'
//);

/**
 * Filter the  list of query variables that should be stripped from requests before doing
 * comparison operations, to lookup custom meta data or similar.
 *
 * @api
 * @package sh404SEF\filter\routing
 * @var sh404sef_tracking_vars_to_strip
 *
 * @param array $varList The list of query variables to remove from query.
 *
 * @return array
 * @since   4.13.0
 *
 */
//ShlHook::add(
//	'sh404sef_tracking_vars_to_strip',
//	function ($varsList) {
//
//		$varsList = array_merge(
//			$varsList,
//			array(
//				'test'
//			)
//		);
//
//		return $varsList;
//	}
//);

/**
 * Filter automatically computed description.
 *
 * @api
 * @package sh404SEF\filter\metadata
 * @var sh404sef_auto_fallback_description
 *
 * @param string    $context The context from onPrepareContent.
 * @param string    $content The raw text from onPrepareContent.
 * @param JRegistry $params The params object from onPrepareContent
 * @param int       $page The page number, as obtained from onPrepareContent.
 *
 * @return array
 * @since   4.11.3
 *
 */
//ShlHook::add(
//	'sh404sef_auto_fallback_description',
//	function ($description,
//	          $context,
//	          $content,
//	          $params,
//	          $page
//	) {
//		$description .= ' Customized!';
//
//		return $description;
//	}
//);

/**
 * Filter a list of regular expressions that will be used to remove unwanted content in automatically generated meta
 * description.
 *
 * @api
 * @package sh404SEF\filter\metadata
 * @var sh404sef_auto_fallback_description
 *
 * @param array  $expressions List of regular expressions, ready to use in preg_replace.
 * @param string $content The original content from which description should be extracted.
 *
 * @return array
 * @since   4.13.2
 *
 */
//ShlHook::add(
//	'sh404sef_auto_fallback_description_cleanup_regexp',
//	function ($expressions, $content) {
//
//		$expressions = array_merge(
//			$expressions,
//			array(
//				'#\[widgetkit[^\]]+\]#us'
//			)
//		);
//
//		return $expressions;
//	}
//);

/**
 * Filter the incoming URI just before the sh404SEF parsing starts.
 *
 * @api
 * @package sh404SEF\filter\router
 * @var sh404sef_before_parse_rule
 *
 * @param JRouter $jRouter The Joomla router instance.
 * @param JUri    $uri The incoming URI.
 *
 * @return array
 * @since   4.15.3
 *
 */
//ShlHook::add(
//	'sh404sef_before_parse_rule',
//	function ($uri, $jRouter) {
//
//		$path = $uri->getPath();
//
//		$doNotAutoRedirectFolders    = array(
//			'images/Documents/'
//		);
//		$doNotAutoRedirectExtensions = array(
//			'.pdf',
//			'.mp4',
//			'.txt'
//		);
//
//		if (
//			wbStartsWith(
//				$path,
//				$doNotAutoRedirectFolders
//			)
//			&&
//			wbEndsWith(
//				$path,
//				$doNotAutoRedirectExtensions
//			)
//		)
//		{
//			throw new \Exception('Not found', 404);
//		}
//
//		return $uri;
//	}
//);

/**
 * Filter the list of query variables as parsed by sh404SEF router.
 *
 * @api
 * @package sh404SEF\filter\router
 * @var sh404sef_after_router_parse_vars
 *
 * @param array   $parsedVars Associative array of query vars.
 * @param JRouter $jRouter The Joomla router instance.
 * @param JUri    $uri The incoming URI.
 *
 * @return array
 * @since   4.8
 *
 */
//ShlHook::add(
//	'sh404sef_after_router_parse_vars',
//	function ($parsedVars, $uri, $jRouter) {
//
//		return $parsedVars;
//	}
//);

/**
 * Filter the Itemid as parsed by sh404SEF router. Return null to keep Itemid unchanged,
 * or an Itemid value to be set.
 *
 * @api
 * @package sh404SEF\filter\router
 * @var sh404sef_after_router_parse_vars_set_itemid
 *
 * @param int     $Itemid The current Itemid if any.
 * @param array   $parsedVars Associative array of query vars.
 * @param JRouter $jRouter The Joomla router instance.
 * @param JUri    $uri The incoming URI.
 *
 * @return int | null
 * @since   4.12.0
 *
 */
//ShlHook::add(
//	'sh404sef_after_router_parse_vars_set_itemid',
//	function ($Itemid, $parsedVars, $uri, $jRouter) {
//
//		return $Itemid;
//	}
//);

/**
 * Filter whether to include the id in an item page title (or possibly other use).
 *
 * @api
 * @package sh404SEF\filter\config
 * @var sh404sef_should_insert_article_id_in_title
 *
 * @param boolean $shouldInsertTitle Whether to include the id for this usage.
 * @param string  $user How the id is to be used: page_title | url
 * @param Object  $article The item being displayed.
 *
 * @return array
 * @since   4.17.1
 *
 */
//ShlHook::add(
//	'sh404sef_should_insert_article_id_in_title',
//	function (
//		$shouldInserArticleId,
//		$use,
//		$item) {
//
//		$shouldInserArticleId = $item->id != 17;
//
//		return $shouldInserArticleId;
//	}
//);

// New syntax

/**
 * Whether to enable protection against rogue plugins that force
 * document type to be HTML by calling Factory::getDocument() too early.
 *
 * @api
 * @package sh404SEF\filter\router
 * @var sh404sef_should_protect_against_document_type_error
 *
 * @param bool $protectAgainstBadPlugins If true, leave document format as query var
 * @param Uri  $originalUri Uri object we are building a SEF URL for.
 *
 * @return array
 * @since   4.20.0
 *
 */
//wbAddHook(
//	'sh404sef_should_protect_against_document_type_error',
//	function ($protectAgainstBadPlugins, $originalUri) {
//
//		$protectAgainstBadPlugins = true;
//
//		return $protectAgainstBadPlugins;
//	}
//);

/**
 * Filter the list of hosts allowed in views list. Default only contains current host.
 *
 * @api
 * @package sh4\filter\analytics
 * @var sh4_analytics_filter_hosts_for_views
 *
 * @param array $allowedHosts List of hosts allowed in views list.
 * @param array $viewList Views obtained from Google Analytics
 *
 * @return string
 *
 * @since   4.20.0
 *
 */
//wbAddHook(
//	'sh4_analytics_filter_hosts_for_views',
//	function ($allowedHosts, $viewList) {
//
//		$allowedHosts = array(
//			'example.com',
//			'example.net',
//		);
//
//		return $allowedHosts;
//	}
//);

/**
 * Whether sh404SEF should try to match parsed vars when using Joomla router
 * to the rebuilt URL.
 *
 * @api
 * @package sh404SEF\filter\router
 * @var sh404sef_match_rebuilt_sef_when_using_joomla_router
 *
 * @param bool                $isOnExclusionList If false, do not check rebuilt URL
 * @param string              $option
 * @param \Joomla\CMS\Uri\Uri $uri The requested uri
 * @param array               $parsedVars the non-sef vars parsed by Joomla router
 * @param \Joomla\CMS\Uri\Uri $rebuiltUri The URI rebuilt by Joomla router based on the parsed vars
 *
 * @return array
 * @since   4.20.2
 *
 */
//ShlHook::add(
//	'sh404sef_joomla_router_use_strict_mode',
//	function ($joomlaRouterStrictMode,
//	          $option,
//	          $uri,
//	          $parsedVars,
//	          $rebuiltUri) {
//
//		$joomlaRouterStrictMode = !in_array(
//			$option,
//			array(
//				'com_content'
//			)
//		);
//
//		return $joomlaRouterStrictMode;
//	}
//);

/**
 * Filter the page title just before it's inserted into the page.
 *
 * @api
 * @package sh404SEF\filter\output
 * @var sh404sef_meta_page_title
 *
 * @param string                $title The page title to be inserted
 * @param Sh404sefClassPageinfo $pageInfo An object describing the current page.
 *
 * @return string
 * @since 4.20.4
 *
 */
//ShlHook::add(
//	'sh404sef_meta_page_title',
//	function ($title, $pageInfo) {
//
//		// change title here
//
//		return $title;
//	}
//);

/**
 * Filter the meta description just before it's inserted into the page.
 *
 * @api
 * @package sh404SEF\filter\output
 * @var sh404sef_meta_description
 *
 * @param string                $description The meta description to be inserted
 * @param Sh404sefClassPageinfo $pageInfo An object describing the current page.
 *
 * @return string
 * @since 4.20.4
 *
 */
//ShlHook::add(
//	'sh404sef_meta_description',
//	function ($description, $pageInfo) {
//
//		// change description here
//
//		return $description;
//	}
//);

/**
 * Filter the meta robots just before it's inserted into the page.
 *
 * @api
 * @package sh404SEF\filter\output
 * @var sh404sef_meta_robots
 *
 * @param string                $robots The meta robots to be inserted
 * @param Sh404sefClassPageinfo $pageInfo An object describing the current page.
 *
 * @return string
 * @since 4.20.4
 *
 */
//ShlHook::add(
//	'sh404sef_meta_robots',
//	function ($robots, $pageInfo) {
//
//		// change meta robots here
//
//		return $robots;
//	}
//);
