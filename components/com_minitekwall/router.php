<?php
/**
* @title				Minitek Wall
* @copyright   	Copyright (C) 2011-2020 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url   https://www.minitek.gr/
* @developers   Minitek.gr
*/

defined('_JEXEC') or die;

class MinitekWallRouter extends JComponentRouterBase
{
  /**
   * Build the route for the com_minitekwall component
   *
   * @param   array  &$query  An array of URL arguments
   *
   * @return  array  The URL arguments to use to assemble the subsequent URL.
   *
   * @since   3.3
   */
  public function build(&$query)
  {
    $segments = array();

    if (isset($query['view']))
    {
      unset($query['view']);
    }

    return $segments;
  }

  /**
   * Parse the segments of a URL.
   *
   * @param   array  &$segments  The segments of the URL to parse.
   *
   * @return  array  The URL attributes to be used by the application.
   *
   * @since   3.3
   */
  public function parse(&$segments)
  {
  	$lang = JFactory::getLanguage();
  	$lang->load('com_minitekwall', JPATH_SITE, $lang->getTag(), true);

  	$vars = array();

  	if (count($segments))
  	{
  		JError::raiseError(404, JText::_('COM_MINITEKWALL_ERROR_PAGE_NOT_FOUND'));
  	}

  	return $vars;
  }
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function MinitekWallBuildRoute(&$query)
{
  $router = new MinitekWallRouter;

  return $router->build($query);
}

function MinitekWallParseRoute($segments)
{
  $router = new MinitekWallRouter;

  return $router->parse($segments);
}
