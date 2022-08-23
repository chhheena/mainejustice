<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\Container;
use JchOptimize\Core\Helper;
use JchOptimize\Core\PageCache\PageCache;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;

defined( '_JEXEC' ) or die ( 'Restricted access' );

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class plgSystemJchoptimizepagecache extends CMSPlugin
{
	/**
	 * If plugin is enabled
	 *
	 * @var bool
	 */
	public $enabled = true;

	/**
	 * Application object
	 *
	 * @var CMSApplication
	 */
	protected $app;

	/**
	 * Container object
	 *
	 * @var Container
	 */
	private $container;

	/**
	 * Page Cache object
	 *
	 * @var PageCache
	 */
	private $pageCache;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  $subject  The object to observe
	 * @param   array                $config   Optional associative array of configurations
	 */
	public function __construct( &$subject, $config = array() )
	{
		parent::__construct( $subject, $config );

		//Disable if the component is not installed or disabled
		if ( ! ComponentHelper::isEnabled( 'com_jchoptimize' ) )
		{
			$this->enabled = false;

			return;
		}

		//Disable if we can't get component's container
		try
		{
			$this->container = Container::getInstance();
		}
		catch ( Exception $e )
		{
			$this->enabled = false;

			return;
		}

		//Disable if client is not Site
		if ( ! $this->app->isClient( 'site' ) )
		{
			$this->enabled = false;

			return;
		}

		//Disable if site offline
		if ( $this->app->get( 'offline', '0' ) )
		{
			$this->enabled = false;

			return;
		}

		//Disable if there are messages enqueued
		if ( $this->app->getMessageQueue() )
		{
			$this->enabled = false;

			return;
		}

		//Disable if we couldn't get cache object
		try
		{
			$this->pageCache = $this->container->get( PageCache::class );
		}
		catch ( Exception $e )
		{
			//didn't work, disable
			$this->enabled = false;

			return;
		}
	}

	public function onAfterInitialise()
	{
		//If already disabled return
		if ( ! $this->enabled )
		{
			return;
		}

		if ( JDEBUG )
		{
			$this->pageCache->disableCaptureCache();
		}

		$this->pageCache->initialize();
	}

	/**
	 * After route event, have to check for excluded menu items here
	 */
	public function onAfterRoute()
	{
		//If already disabled return
		if ( ! $this->enabled )
		{
			return;
		}

		try
		{
			$excludedMenus = $this->container->get( 'params' )->get( 'cache_exclude_menu', [] );

			if ( in_array( $this->app->input->get( 'Itemid', '', 'int' ), $excludedMenus ) )
			{
				$this->enabled = false;
				$this->pageCache->disableCaching();

				return;
			}
			//Now may be a good time to set Caching
			$this->pageCache->setCaching();

		}
		catch ( Exception $e )
		{
		}
	}

	public function onAfterRender()
	{
		if ( ! $this->enabled )
		{
			return;
		}

		$html = $this->app->getBody();

		if ( ! Helper::validateHtml( $html ) )
		{
			$this->pageCache->disableCaching();

			return;
		}

		//Save page cache here before it gets gzipped
		$this->pageCache->store( $html );
	}

	/**
	 * If Page Cache plugin is already disabled then this will disable the Page Cache object when it is constructed
	 *
	 * @return bool
	 */
	public function onPageCacheSetCaching(): bool
	{
		return $this->enabled;
	}
}