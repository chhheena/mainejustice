<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ModuleAdapter;

defined( '_JEXEC' ) or die( 'Restricted Access' );

class Mod_JchmodeswitcherInstallerScript
{
	protected $moduleClient = 'administrator';

	protected $moduleName = 'mod_jchmodeswitcher';

	public function postflight( string $type, ModuleAdapter $parent ): void
	{
		if ( $type == 'install' || $type == 'update' )
		{
			$this->publishModule();
		}
	}

	private function publishModule()
	{
		$db = Factory::getDbo();

		//get module id
		$query = $db->getQuery( true )
			->select( $db->quoteName( 'id' ) )
			->from( '#__modules' )
			->where( $db->quoteName( 'module' ) . ' = ' . $db->quote( 'mod_jchmodeswitcher' ) )
			->where( $db->quoteName( 'client_id' ) . ' = 1' );
		$db->setQuery( $query, 0, 1 );
		$iId = $db->loadResult();

		if ( ! $iId )
		{
			return;
		}

		//Check if module in modules_menu table
		$query->clear()
			->select( $db->quoteName( 'moduleid' ) )
			->from( '#__modules_menu' )
			->where( $db->quoteName( 'moduleid' ) . ' = ' . (int)$iId );
		$db->setQuery( $query, 0, 1 );

		if ( $db->loadResult() )
		{
			return;
		}

		//Get highest order
		$query->clear()
			->select( $db->quoteName( 'ordering' ) )
			->from( '#__modules' )
			->where( $db->quoteName( 'position' ) . ' = ' . $db->quote( 'status' ) )
			->where( $db->quoteName( 'client_id' ) . ' = 1' )
			->order( 'ordering DESC' );
		$db->setQuery( $query, 0, 1 );
		$iOrdering = $db->loadResult();
		$iOrdering++;

		//publish module
		$query->clear()
			->update( '#__modules' )
			->set( $db->quoteName( 'published' ) . ' = 1' )
			->set( $db->quoteName( 'ordering' ) . ' = ' . (int)$iOrdering )
			->set( $db->quoteName( 'position' ) . ' = ' . $db->quote( 'status' ) )
			->set( $db->quoteName( 'access' ) . ' = 2' )
			->where( $db->quoteName( 'id' ) . ' = ' . (int)$iId );
		$db->setQuery( $query );
		$db->execute();

		//add module to the modules_menu table
		$query->clear()
			->insert( '#__modules_menu' )
			->columns( [ $db->quoteName( 'moduleid' ), $db->quoteName( 'menuid' ) ] )
			->values( (int)$iId . ',  0' );
		$db->setQuery( $query );
		$db->execute();
	}
}