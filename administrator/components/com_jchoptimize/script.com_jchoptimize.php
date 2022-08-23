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
defined( '_JEXEC' ) or die();

use JchOptimize\Container;
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\Model\Cache;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Filesystem\File;
use Joomla\Filesystem\Folder;

class Com_JchoptimizeInstallerScript
{
	protected $removeFiles = [
		'files'   => [],
		'folders' => [
			'administrator/components/com_jchoptimize/cache',
			'administrator/components/com_jchoptimize/Controller',
			'administrator/components/com_jchoptimize/Dispatcher',
			'administrator/components/com_jchoptimize/Helper',
			'administrator/components/com_jchoptimize/Model',
			'administrator/components/com_jchoptimize/Platform',
			'administrator/components/com_jchoptimize/sql',
			'administrator/components/com_jchoptimize/Toolbar',
			'administrator/components/com_jchoptimize/View'
		]

	];

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string            $type  install, update or discover_update
	 * @param   ComponentAdapter  $parent
	 *
	 * @return void
	 */
	public function postflight( string $type, ComponentAdapter $parent ): void
	{
		if ( ! in_array( $type, [ 'install', 'update' ] ) )
		{
			return;
		}

		if ( version_compare( JVERSION, '3.99.99', '<=' ) )
		{
			$config_j3 = $parent->getParent()->getPath( 'source' ) . '/backend/config_j3.xml';
			$config    = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/config.xml';

			File::delete( $config );
			if ( ! File::copy( $config_j3, $config ) )
			{
				$msg = "<p>Couldn't copy the config.xml file</p>";
				JLog::add( $msg, JLog::WARNING, 'jerror' );
			}

			File::delete( JPATH_ADMINISTRATOR . '/components/com_jchoptimize/config_j3.xml' );
			File::delete( JPATH_ADMINISTRATOR . '/components/com_jchoptimize/config_j4.xml' );
		}

		// Remove obsolete files and folders
		$this->removeFilesAndFolders( $this->removeFiles );
	}

	/**
	 * Runs on uninstallation
	 *
	 * @param   ComponentAdapter  $parent  Parent object
	 *
	 * @return  void
	 */
	public function uninstall( ComponentAdapter $parent ): void
	{
		// Clean up Htaccess file
		@include_once( JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php' );
		Tasks::cleanHtaccess();
		Folder::delete( JPATH_ROOT . '/images/jch-optimize' );
		Folder::delete( JPATH_ROOT . '/jchoptimizecapturecache' );

		$container  = Container::getInstance();
		$cacheModel = $container->get( Cache::class );
		$cacheModel->cleanCache();
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeList  The files and directories to remove
	 */
	private function removeFilesAndFolders( array $removeList )
	{
		foreach ( $removeList['files'] ?? [] as $file )
		{
			$f = JPATH_ROOT . '/' . $file;

			@is_file( $f ) && File::delete( $f );
		}

		foreach ( $removeList['folders'] ?? [] as $folder )
		{
			$f = JPATH_ROOT . '/' . $folder;

			@is_dir( $f ) && Folder::delete( $f );
		}
	}
}