<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
// Protect from unauthorized access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class Pkg_JchoptimizeInstallerScript
{
	/**
	 * The name of our package, e.g. pkg_example. Used for dependency tracking.
	 *
	 * @var  string
	 */
	protected $packageName = 'pkg_jchoptimize';

	/**
	 * The name of our component, e.g. com_example. Used for dependency tracking.
	 *
	 * @var  string
	 */
	protected $componentName = 'com_jchoptimize';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '7.3.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.9.7';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.999.999';

	/**
	 * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
	 * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins).
	 *
	 * @var array
	 */
	protected $extensionsToEnable = [
		[ 'plugin', 'jchoptimize', 0, 'system' ],
		[ 'plugin', 'jchoptimizeuserstate', 0, 'user' ],
		[ 'module', 'mod_jchmodeswitcher', 1, '' ]
	];

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the package. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * In here we'll try to install FOF. We have to do that before installing the component since it's using an
	 * installation script extending FOF's InstallScript class. We can't use a <file> tag in the manifest to install FOF
	 * since the FOF installation is expected to fail if a newer version of FOF is already installed on the site.
	 *
	 * @param   string          $type    Installation type (install, update, discover_install)
	 * @param   PackageAdapter  $parent  Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight( string $type, PackageAdapter $parent ): bool
	{
		if ( ! in_array( $type, [ 'install', 'update' ] ) )
		{
			return true;
		}

		// Check the minimum PHP version
		if ( ! version_compare( PHP_VERSION, $this->minimumPHPVersion, 'ge' ) )
		{
			$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this package</p>";
			Log::add( $msg, Log::WARNING, 'jerror' );

			return false;
		}

		// Check the minimum Joomla! version
		if ( ! version_compare( JVERSION, $this->minimumJoomlaVersion, 'ge' ) )
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";
			Log::add( $msg, Log::WARNING, 'jerror' );

			return false;
		}

		// Check the maximum Joomla! version
		if ( ! version_compare( JVERSION, $this->maximumJoomlaVersion, 'le' ) )
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";
			Log::add( $msg, Log::WARNING, 'jerror' );

			return false;
		}

		//
		$manifest    = $parent->getManifest();
		$new_variant = (string)$manifest->variant;

		$files   = [];
		$files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jchoptimize.xml';
		$files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jch_optimize.xml';

		foreach ( $files as $file )
		{
			if ( file_exists( $file ) )
			{
				$xml         = simplexml_load_file( $file );
				$old_variant = (string)$xml->variant;

				if ( $old_variant == 'PRO' && $new_variant == 'FREE' )
				{
					$msg = '<p>You are trying to install the FREE version of JCH Optimize, but you currently have the PRO version installed. You must uninstall the PRO version first before you can install the FREE version.</p>';
					Log::add( $msg, Log::WARNING, 'jerror' );

					return false;
				}

				break;
			}
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string          $type     install, update or discover_update
	 * @param   PackageAdapter  $parent   Parent object
	 * @param   array           $results  The results of each installed extension
	 */
	public function postflight( string $type, PackageAdapter $parent, array $results )
	{
		if ( $type == 'uninstall' )
		{
			return;
		}

		$this->removeOldJchOptimizePackage();

		//We're no longer using FOF so remove our dependency and uninstall if there are no more dependencies
		$this->removeDependencyAndUninstallFoF( $parent );

		if ( in_array( $type, [ 'install', 'update' ] ) )
		{
			//Update htaccess
			$this->leverageBrowserCaching();

			//Order the plugins
			try
			{
				$this->orderPlugins();
			}
			catch ( \Exception $e )
			{
				$msg = '<p>Couldn\'t order the plugins. Please order the plugins from the Dashboard for 
				best compatibility with other extensions.</p>';
				Log::add( $msg, Log::WARNING, 'jerror' );
			}
		}


		/**
		 * Clean up the obsolete package update sites.
		 *
		 * If you specify a new update site location in the XML manifest Joomla will install it in the #__update_sites
		 * table but it will NOT remove the previous update site. This method removes the old update sites which are
		 * left behind by Joomla.
		 */
		if ( $type !== 'install' )
		{
			$this->removeObsoleteUpdateSites();
		}

		/**
		 * Clean the cache after installing the package.
		 *
		 * See bug report https://github.com/joomla/joomla-cms/issues/16147
		 */
		$conf         = Factory::getConfig();
		$clearGroups  = array( '_system', 'com_modules', 'mod_menu', 'com_plugins', 'com_modules', 'page', 'com_jchoptimize', 'plg_jch_optimize' );
		$cacheClients = array( 0, 1 );

		foreach ( $clearGroups as $group )
		{
			foreach ( $cacheClients as $client_id )
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'    => ( $client_id ) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get( 'cache_path', JPATH_SITE . '/cache' )
					);

					/** @var Cache $cache */
					$cache = Cache::getInstance( 'callback', $options );
					$cache->clean();
				}
				catch ( Exception $exception )
				{
					$options['result'] = false;
				}

				// Trigger the onContentCleanCache event.
				try
				{
					Factory::getApplication()->triggerEvent( 'onContentCleanCache', $options );
				}
				catch ( Exception $e )
				{
					// Suck it up
				}
			}
		}

		//Delete static cache files
		try
		{
			$staticCacheFolder = JPATH_ROOT . '/media/com_jchoptimize/cache';

			if ( file_exists( $staticCacheFolder ) )
			{
				Folder::delete( $staticCacheFolder );
			}
		}
		catch ( Throwable $e )
		{
			//Don't cry
		}
	}

	private function leverageBrowserCaching()
	{
		$htaccess = JPATH_ROOT . '/.htaccess';

		$startHtaccessLine = '## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##';
		$endHtaccessLine   = '## END EXPIRES CACHING - JCH OPTIMIZE ##';

		$endHtaccessLineRegex = preg_quote( rtrim( $endHtaccessLine, "# \n\r\t\v\x00" ) ) . '[^\r\n]*[\r\n]*';

		if ( file_exists( $htaccess ) )
		{
			$contents      = file_get_contents( $htaccess );
			$htaccessRegex = '#[\r\n]?' . preg_quote( $startHtaccessLine ) . '.*?' . $endHtaccessLineRegex . '#s';
			$cleanContents = preg_replace( $htaccessRegex, PHP_EOL, $contents );

			$expires = <<<APACHECONFIG

$startHtaccessLine
<IfModule mod_expires.c>
	ExpiresActive on

	# Your document html
	ExpiresByType text/html "access plus 0 seconds"

	# Data
	ExpiresByType text/xml "access plus 0 seconds"
	ExpiresByType application/xml "access plus 0 seconds"
	ExpiresByType application/json "access plus 0 seconds"

	# Feed
	ExpiresByType application/rss+xml "access plus 1 hour"
	ExpiresByType application/atom+xml "access plus 1 hour"

	# Favicon (cannot be renamed)
	ExpiresByType image/x-icon "access plus 1 week"

	# Media: images, video, audio
	ExpiresByType image/gif "access plus 1 year"
	ExpiresByType image/png "access plus 1 year"
	ExpiresByType image/jpg "access plus 1 year"
	ExpiresByType image/jpeg "access plus 1 year"
	ExpiresByType image/webp "access plus 1 year"
	ExpiresByType audio/ogg "access plus 1 year"
	ExpiresByType video/ogg "access plus 1 year"
	ExpiresByType video/mp4 "access plus 1 year"
	ExpiresByType video/webm "access plus 1 year"

	# HTC files (css3pie)
	ExpiresByType text/x-component "access plus 1 year"

	# Webfonts
	ExpiresByType application/font-ttf "access plus 1 year"
	ExpiresByType font/* "access plus 1 year"
	ExpiresByType application/font-woff "access plus 1 year"
	ExpiresByType application/font-woff2 "access plus 1 year"
	ExpiresByType image/svg+xml "access plus 1 year"
	ExpiresByType application/vnd.ms-fontobject "access plus 1 year"

	# CSS and JavaScript
	ExpiresByType text/css "access plus 1 year"
	ExpiresByType type/javascript "access plus 1 year"
	ExpiresByType application/javascript "access plus 1 year"

	<IfModule mod_headers.c>
		Header append Cache-Control "public"
		<FilesMatch ".(js|css|xml|gz|html)$">
			Header append Vary: Accept-Encoding
		</FilesMatch>
	</IfModule>

</IfModule>


<IfModule mod_brotli.c>
	<IfModule mod_filter.c>
		AddOutputFilterByType BROTLI_COMPRESS text/html text/xml text/plain 
		AddOutputFilterByType BROTLI_COMPRESS application/rss+xml application/xml application/xhtml+xml 
		AddOutputFilterByType BROTLI_COMPRESS text/css 
		AddOutputFilterByType BROTLI_COMPRESS text/javascript application/javascript application/x-javascript 
		AddOutputFilterByType BROTLI_COMPRESS image/x-icon image/svg+xml
		AddOutputFilterByType BROTLI_COMPRESS application/rss+xml
		AddOutputFilterByType BROTLI_COMPRESS application/font application/font-truetype application/font-ttf
		AddOutputFilterByType BROTLI_COMPRESS application/font-otf application/font-opentype
		AddOutputFilterByType BROTLI_COMPRESS application/font-woff application/font-woff2
		AddOutputFilterByType BROTLI_COMPRESS application/vnd.ms-fontobject
		AddOutputFilterByType BROTLI_COMPRESS font/ttf font/otf font/opentype font/woff font/woff2
	</IfModule>
</IfModule>

<IfModule mod_deflate.c>
	<IfModule mod_filter.c>
		AddOutputFilterByType DEFLATE text/html text/xml text/plain 
		AddOutputFilterByType DEFLATE application/rss+xml application/xml application/xhtml+xml 
		AddOutputFilterByType DEFLATE text/css 
		AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript 
		AddOutputFilterByType DEFLATE image/x-icon image/svg+xml
		AddOutputFilterByType DEFLATE application/rss+xml
		AddOutputFilterByType DEFLATE application/font application/font-truetype application/font-ttf
		AddOutputFilterByType DEFLATE application/font-otf application/font-opentype
		AddOutputFilterByType DEFLATE application/font-woff application/font-woff2
		AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
		AddOutputFilterByType DEFLATE font/ttf font/otf font/opentype font/woff font/woff2
	</IfModule>
</IfModule>

# Don't compress files with extensions .gz or .br
<IfModule mod_rewrite.c>
	RewriteRule "\.(gz|br)$" "-" [E=no-gzip:1,E=no-brotli:1]
</IfModule>

<IfModule !mod_rewrite.c>
	<IfModule mod_setenvif.c>
		SetEnvIfNoCase Request_URI \.(gz|br)$ no-gzip no-brotli
	</IfModule>
</IfModule>
$endHtaccessLine

APACHECONFIG;

			$expires = str_replace( array( "\r\n", "\n" ), PHP_EOL, $expires );
			$str     = $expires . PHP_EOL . $cleanContents;

			file_put_contents( $htaccess, $str );
		}

	}

	private function orderPlugins()
	{
		//These plugins must be ordered last in this order; array of plugin elements
		$aOrder = [
			'jscsscontrol',
			'eorisis_jquery',
			'jqueryeasy',
			'quix',
			'jchoptimize',
			'setcanonical',
			'canonical',
			'plugin_googlemap3',
			'jomcdn',
			'cdnforjoomla',
			'bigshotgoogleanalytics',
			'GoogleAnalytics',
			'pixanalytic',
			'ykhoonhtmlprotector',
			'jat3',
			'cache',
			'plg_gkcache',
			'pagecacheextended',
			'homepagecache',
			'jSGCache',
			'j2pagecache',
			'jotcache',
			'lscache',
			'vmcache_last',
			'pixcookiesrestrict',
			'speedcache',
			'speedcache_last',
			'jchoptimizepagecache',
		];

		//Get an associative array of all installed system plugins with their extension id, ordering, and element
		$plugins = $this->loadPlugins();

		//Get an array of all the plugins that are installed that are in the array of specified plugin order above
		$aLowerPlugins = array_values( array_filter( $aOrder,
			function ( $aVal ) use ( $plugins ) {
				return ( array_key_exists( $aVal, $plugins ) );
			}
		) );

		//Number of installed plugins
		$iNoPlugins = count( $plugins );
		//Number of installed plugins that needs to be ordered at the bottom of the order
		$iNoLowerPlugins = count( $aLowerPlugins );
		$iBaseOrder      = $iNoPlugins - $iNoLowerPlugins;

		$cid   = array();
		$order = array();

		//Iterate through list of installed system plugins
		foreach ( $plugins as $key => $value )
		{
			if ( in_array( $key, $aLowerPlugins ) )
			{
				$value['ordering'] = $iNoPlugins + 1 + array_search( $key, $aLowerPlugins );
			}

			$cid[]   = $value['extension_id'];
			$order[] = $value['ordering'];
		}

		ArrayHelper::toInteger( $cid );
		ArrayHelper::toInteger( $order );


		$config = [
			'base_path' => JPATH_ADMINISTRATOR . '/components/com_plugins',
			'name'      => 'plugins'
		];

		//Joomla version 3.9 doesn't use a factory
		if ( version_compare( JVERSION, '3.10', 'lt' ) )
		{
			$oPluginsController = new BaseController( $config );
		}
		else
		{
			$factory            = version_compare( JVERSION, '3.999.999', 'gt' ) ? new MVCFactory( '\Joomla\Component\Plugins' ) : new LegacyFactory();
			$oPluginsController = new BaseController( $config, $factory );
		}

		$oPluginModel = $oPluginsController->getModel( 'Plugin', '', $config );

		$oPluginModel->saveorder( $cid, $order );
	}

	private function removeOldJchOptimizePackage()
	{
		$id = $this->findPackageExtensionID( 'pkg_jch_optimize' );

		if ( ! $id )
		{
			return;
		}

		$plugin = $this->loadPlugins( 'jch_optimize' );

		$pluginParams = new Registry( $plugin->params );

		//update smart combine to json
		$smart_combine_values = $pluginParams->get( 'pro_smart_combine_values' );

		if ( ! empty( $smart_combine_values ) && is_array( $smart_combine_values ) )
		{
			$pluginParams->set( 'pro_smart_combine_values', json_encode( $smart_combine_values ) );
		}

		try
		{
			$this->saveComponentSettings( $pluginParams );

		}
		catch ( \Exception $e )
		{
			$msg = "<p>We weren't able to transfer the settings from the plugin to the component. You may have to reconfigure JCH Optimize.</p>";
			Log::add( $msg, Log::WARNING, 'jerror' );
		}

		try
		{
			$installer = new Installer;
			$installer->uninstall( 'package', $id );
		}
		catch ( Exception $e )
		{
			$msg = "<p>We weren't able to uninstall the previous version of JCH Optimize. You'll need to do that from the Extensions Manager.</p>";
			Log::add( $msg, Log::WARNING, 'jerror' );
		}
	}

	/**
	 * @param   Registry  $params
	 *
	 * @return void
	 */
	private function saveComponentSettings( Registry $params )
	{
		$db = Factory::getDbo();
		//Save plugin's params to component
		$query = $db->getQuery( true )
			->update( $db->qn( '#__extensions' ) )
			->set( $db->qn( 'params' ) . ' = ' . $db->q( $params->toString() ) )
			->where( $db->qn( 'element' ) . ' = ' . $db->q( 'com_jchoptimize' ) )
			->where( $db->qn( 'type' ) . ' = ' . $db->q( 'component' ) );
		$db->setQuery( $query );
		$db->execute();
	}

	private function removeDependencyAndUninstallFoF( $parent ): bool
	{
		if ( ! file_exists( JPATH_LIBRARIES . '/fof40/include.php' ) )
		{
			//Already uninstalled so let's go!
			return true;
		}

		// Load the fof40 library
		include_once( JPATH_LIBRARIES . '/fof40/include.php' );

		// Remove the dependency of our component
		$this->removeDependency( 'fof40', $this->componentName );

		// Then try to uninstall the FOF library. The uninstallation might fail if there are other extensions depending
		// on it. That would cause the entire package uninstallation to fail, hence the need for special handling.

		//Maybe don't uninstall as other extensions may not be properly registered.
		//$this->uninstallFOF( $parent );

		return true;

	}

	/**
	 * Removes a package dependency from #__akeeba_common
	 *
	 * @param   string  $package     The package
	 * @param   string  $dependency  The dependency to remove
	 */
	private function removeDependency( string $package, string $dependency )
	{
		$dependencies = $this->getDependencies( $package );

		if ( in_array( $dependency, $dependencies ) )
		{
			$index = array_search( $dependency, $dependencies );
			unset( $dependencies[ $index ] );

			$this->setDependencies( $package, $dependencies );
		}
	}

	/**
	 * Get the dependencies for a package from the #__akeeba_common table
	 *
	 * @param   string  $package  The package
	 *
	 * @return  array  The dependencies
	 */
	private function getDependencies( string $package ): array
	{
		$db = Factory::getDbo();

		$query = $db->getQuery( true )
			->select( $db->qn( 'value' ) )
			->from( $db->qn( '#__akeeba_common' ) )
			->where( $db->qn( 'key' ) . ' = ' . $db->q( $package ) );

		try
		{
			$dependencies = $db->setQuery( $query )->loadResult();
			$dependencies = json_decode( $dependencies, true );

			if ( empty( $dependencies ) )
			{
				$dependencies = array();
			}
		}
		catch ( Exception $e )
		{
			$dependencies = array();
		}

		return $dependencies;
	}

	/**
	 * Sets the dependencies for a package into the #__akeeba_common table
	 *
	 * @param   string  $package       The package
	 * @param   array   $dependencies  The dependencies list
	 */
	private function setDependencies( string $package, array $dependencies )
	{
		$db = Factory::getDbo();

		$query = $db->getQuery( true )
			->delete( '#__akeeba_common' )
			->where( $db->qn( 'key' ) . ' = ' . $db->q( $package ) );

		try
		{
			$db->setQuery( $query )->execute();
		}
		catch ( Exception $e )
		{
			// Do nothing if the old key wasn't found
		}

		$object = (object)array(
			'key'   => $package,
			'value' => json_encode( $dependencies )
		);

		try
		{
			$db->insertObject( '#__akeeba_common', $object, 'key' );
		}
		catch ( Exception $e )
		{
			// Do nothing if the old key wasn't found
		}
	}

	/**
	 * Try to uninstall the FOF library. We don't go through the Joomla! package uninstallation since we can expect the
	 * uninstallation of the FOF library to fail if other software depends on it.
	 *
	 * @param   PackageAdapter  $parent
	 */
	private function uninstallFOF( PackageAdapter $parent )
	{
		// Check dependencies on FOF
		$dependencyCount = count( $this->getDependencies( 'fof40' ) );

		if ( $dependencyCount )
		{
			$msg = "<p>You have $dependencyCount extension(s) depending on this version of FOF. The package cannot be uninstalled unless these extensions are uninstalled first.</p>";

			Log::add( $msg, Log::DEBUG, 'jerror' );

			return;
		}

		$tmpInstaller = new Installer;

		$db = $parent->getParent()->getDbo();

		$query = $db->getQuery( true )
			->select( 'extension_id' )
			->from( '#__extensions' )
			->where( 'type = ' . $db->quote( 'library' ) )
			->where( 'element = ' . $db->quote( 'lib_fof40' ) );

		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( ! $id )
		{
			return;
		}

		try
		{
			$tmpInstaller->uninstall( 'library', $id, 0 );
		}
		catch ( \Exception $e )
		{
			// We can expect the uninstallation to fail if there are other extensions depending on the FOF library.
		}
	}

	/**
	 * Removes the obsolete update sites for the component, since now we're dealing with a package.
	 *
	 * Controlled by componentName, packageName and obsoleteUpdateSiteLocations
	 *
	 * Depends on getExtensionId, getUpdateSitesFor
	 *
	 * @return  void
	 */
	private function removeObsoleteUpdateSites()
	{
		// Get package ID
		$packageID = $this->findPackageExtensionID( $this->packageName );

		if ( ! $packageID )
		{
			return;
		}

		// All update sites for the package
		$deleteIDs = $this->getUpdateSitesFor( $packageID );

		if ( empty( $deleteIDs ) )
		{
			$deleteIDs = [];
		}

		if ( count( $deleteIDs ) <= 1 )
		{
			return;
		}

		$deleteIDs = array_unique( $deleteIDs );

		// Remove the latest update site, the one we just installed
		array_pop( $deleteIDs );

		$db = Factory::getDbo();

		if ( empty( $deleteIDs ) || ! count( $deleteIDs ) )
		{
			return;
		}

		// Delete the remaining update sites
		$deleteIDs = array_map( [ $db, 'q' ], $deleteIDs );

		$query = $db->getQuery( true )
			->delete( $db->qn( '#__update_sites' ) )
			->where( $db->qn( 'update_site_id' ) . ' IN(' . implode( ',', $deleteIDs ) . ')' );

		try
		{
			$db->setQuery( $query )->execute();
		}
		catch ( Exception $e )
		{
			// Do nothing.
		}

		$query = $db->getQuery( true )
			->delete( $db->qn( '#__update_sites_extensions' ) )
			->where( $db->qn( 'update_site_id' ) . ' IN(' . implode( ',', $deleteIDs ) . ')' );

		try
		{
			$db->setQuery( $query )->execute();
		}
		catch ( Exception $e )
		{
			// Do nothing.
		}
	}

	/**
	 * Gets the ID of an extension
	 *
	 * @param   string  $element  Package extension element, e.g. pkg_foo
	 *
	 * @return  int  Extension ID or 0 on failure
	 */
	private function findPackageExtensionID( $element )
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery( true )
			->select( $db->qn( 'extension_id' ) )
			->from( $db->qn( '#__extensions' ) )
			->where( $db->qn( 'element' ) . ' = ' . $db->q( $element ) )
			->where( $db->qn( 'type' ) . ' = ' . $db->q( 'package' ) );

		try
		{
			$id = $db->setQuery( $query, 0, 1 )->loadResult();
		}
		catch ( Exception $e )
		{
			return 0;
		}

		return empty( $id ) ? 0 : (int)$id;
	}

	/**
	 * Returns the update site IDs for the specified Joomla Extension ID.
	 *
	 * @param   int  $eid  Extension ID for which to retrieve update sites
	 *
	 * @return  array  The IDs of the update sites
	 */
	private function getUpdateSitesFor( $eid = null )
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery( true )
			->select( $db->qn( 's.update_site_id' ) )
			->from( $db->qn( '#__update_sites', 's' ) )
			->innerJoin( $db->qn( '#__update_sites_extensions', 'e' ) . 'ON(' . $db->qn( 'e.update_site_id' ) .
				' = ' . $db->qn( 's.update_site_id' ) . ')'
			)
			->where( $db->qn( 'e.extension_id' ) . ' = ' . $db->q( $eid ) );

		try
		{
			$ret = $db->setQuery( $query )->loadColumn();
		}
		catch ( Exception $e )
		{
			return [];
		}

		return empty( $ret ) ? [] : $ret;
	}

	/**
	 * Runs on installation (but not on upgrade). This happens in install and discover_install installation routes.
	 *
	 * @param   \JInstallerAdapterPackage  $parent  Parent object
	 *
	 * @return  bool
	 */
	public function install( $parent )
	{
		// Enable the extensions we need to install
		$this->enableExtensions();

		return true;
	}

	//Temporary function to enable newly added plugin
	public function update( $parent )
	{
		$this->enableExtension( 'plugin', 'jchoptimizeuserstate', 0, 'user' );
	}

	/**
	 * Enable modules and plugins after installing them
	 */
	private function enableExtensions()
	{
		foreach ( $this->extensionsToEnable as $ext )
		{
			$this->enableExtension( $ext[0], $ext[1], $ext[2], $ext[3] );
		}
	}

	/**
	 * Loads the JCH Optimize plugin
	 */
	private function loadPlugins( $element = '' )
	{
		$db = Factory::getDbo();

		try
		{
			$query = $db->getQuery( true )
				->select( 'folder AS type, element AS name, element, params, extension_id, ordering, enabled, package_id' )
				->from( '#__extensions' );

			if ( $element != '' )
			{
				$query->where( $db->quoteName( 'element' ) . ' = ' . $db->quote( $element ) );
			}

			$query->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'plugin' ) )
				->where( $db->quoteName( 'folder' ) . ' = ' . $db->quote( 'system' ) );
			$db->setQuery( $query );

			if ( $element != '' )
			{
				return $db->loadObject();
			}
			else
			{
				return $db->loadAssocList( 'element' );
			}
		}
		catch ( Exception $e )
		{
			return null;
		}
	}


	/**
	 * Enable an extension
	 *
	 * @param   string       $type    The extension type.
	 * @param   string       $name    The name of the extension (the element field).
	 * @param   integer      $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
	 * @param   string|null  $group   The extension group (for plugins).
	 */
	private function enableExtension( string $type, string $name, int $client = 1, string $group = null )
	{
		try
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery( true )
				->update( '#__extensions' )
				->set( $db->qn( 'enabled' ) . ' = ' . $db->q( 1 ) )
				->where( 'type = ' . $db->quote( $type ) )
				->where( 'element = ' . $db->quote( $name ) );
		}
		catch ( \Exception $e )
		{
			return;
		}


		switch ( $type )
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where( 'folder = ' . $db->quote( $group ) );
				break;

			case 'language':
			case 'module':
			case 'template':
				// Languages, modules and templates have a client but not a folder
				$query->where( 'client_id = ' . (int)$client );
				break;

			default:
			case 'library':
			case 'package':
			case 'component':
				// Components, packages and libraries don't have a folder or client.
				// Included for completeness.
				break;
		}

		try
		{
			$db->setQuery( $query );
			$db->execute();
		}
		catch ( \Exception $e )
		{
		}
	}
}