<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
namespace JchOptimize\Database;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
/**
 * Decorator for DatabaseInterface to use in Joomla3
 */
class Database implements DatabaseInterface
{
    /**
     * @var \JDatabaseDriver
     */
    protected $db;
    public function __construct(\JDatabaseDriver $db)
    {
        $this->db = $db;
    }
    /**
     * @inheritDoc
     */
    public function connect()
    {
        $this->db->connect();
    }
    /**
     * @inheritDoc
     */
    public function connected() : bool
    {
        return $this->db->connected();
    }
    /**
     * @inheritDoc
     */
    public function createDatabase($options, $utf = \true)
    {
        return $this->db->createDatabase($options, $utf);
    }
    /**
     * @inheritDoc
     */
    public function decodeBinary($data) : string
    {
        return $data;
    }
    /**
     * @inheritDoc
     */
    public function disconnect()
    {
        $this->db->disconnect();
    }
    /**
     * @inheritDoc
     */
    public function dropTable($table, $ifExists = \true)
    {
        return $this->dropTable($table, $ifExists);
    }
    /**
     * @inheritDoc
     */
    public function escape($text, $extra = \false) : string
    {
        return $this->db->escape($text, $extra);
    }
    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->db->execute();
    }
    /**
     * @inheritDoc
     */
    public function getAffectedRows() : int
    {
        return $this->db->getAffectedRows();
    }
    /**
     * @inheritDoc
     */
    public function getCollation()
    {
        return $this->db->getCollation();
    }
    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->db->getConnection();
    }
    /**
     * @inheritDoc
     */
    public function getConnectionCollation() : string
    {
        return $this->db->getConnectionCollation();
    }
    /**
     * @inheritDoc
     */
    public function getConnectionEncryption() : string
    {
        return '';
    }
    /**
     * @inheritDoc
     */
    public function isConnectionEncryptionSupported() : bool
    {
        return \false;
    }
    /**
     * @inheritDoc
     */
    public function isMinimumVersion() : bool
    {
        return $this->db->isMinimumVersion();
    }
    /**
     * @inheritDoc
     */
    public function getCount() : int
    {
        return $this->db->getCount();
    }
    /**
     * @inheritDoc
     */
    public function getDateFormat() : string
    {
        return $this->db->getDateFormat();
    }
    /**
     * @inheritDoc
     */
    public function getMinimum() : string
    {
        return $this->db->getMinimum();
    }
    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->db->getName();
    }
    /**
     * @inheritDoc
     */
    public function getNullDate() : string
    {
        return $this->db->getNullDate();
    }
    /**
     * @inheritDoc
     */
    public function getNumRows($cursor = null) : int
    {
        return $this->db->getNumRows($cursor);
    }
    /**
     * @inheritDoc
     */
    public function getQuery($new = \false)
    {
        return $this->db->getQuery($new);
    }
    /**
     * @inheritDoc
     */
    public function getServerType() : string
    {
        return $this->db->getServerType();
    }
    /**
     * @inheritDoc
     */
    public function getTableColumns($table, $typeOnly = \true) : array
    {
        return $this->db->getTableColumns($table, $typeOnly);
    }
    /**
     * @inheritDoc
     */
    public function getTableKeys($tables) : array
    {
        return $this->db->getTableKeys($tables);
    }
    /**
     * @inheritDoc
     */
    public function getTableList() : array
    {
        return $this->db->getTableList();
    }
    /**
     * @inheritDoc
     */
    public function getVersion() : string
    {
        return $this->db->getVersion();
    }
    /**
     * @inheritDoc
     */
    public function hasUtfSupport() : bool
    {
        return $this->db->hasUtfSupport();
    }
    /**
     * @inheritDoc
     */
    public function insertid()
    {
        return $this->db->insertid();
    }
    /**
     * @inheritDoc
     */
    public function insertObject($table, &$object, $key = null) : bool
    {
        return $this->db->insertObject($table, $object, $key);
    }
    /**
     * @inheritDoc
     */
    public static function isSupported() : bool
    {
        return Factory::getDbo()::isSupported();
    }
    /**
     * @inheritDoc
     */
    public function loadAssoc()
    {
        return $this->db->loadAssoc();
    }
    /**
     * @inheritDoc
     */
    public function loadAssocList($key = null, $column = null)
    {
        return $this->db->loadAssocList($key, $column);
    }
    /**
     * @inheritDoc
     */
    public function loadColumn($offset = 0)
    {
        return $this->db->loadColumn($offset);
    }
    /**
     * @inheritDoc
     */
    public function loadObject($class = \stdClass::class)
    {
        return $this->db->loadObject($class);
    }
    /**
     * @inheritDoc
     */
    public function loadObjectList($key = '', $class = \stdClass::class)
    {
        return $this->db->loadObjectList($key, $class);
    }
    /**
     * @inheritDoc
     */
    public function loadResult()
    {
        return $this->db->loadResult();
    }
    /**
     * @inheritDoc
     */
    public function loadRow()
    {
        return $this->db->loadRow();
    }
    /**
     * @inheritDoc
     */
    public function loadRowList($key = null)
    {
        return $this->db->loadRowList($key);
    }
    /**
     * @inheritDoc
     */
    public function lockTable($tableName)
    {
        return $this->db->lockTable($tableName);
    }
    /**
     * @inheritDoc
     */
    public function quote($text, $escape = \true)
    {
        return $this->db->quote($text, $escape);
    }
    /**
     * @inheritDoc
     */
    public function quoteBinary($data) : string
    {
        return $this->db->quoteBinary($data);
    }
    /**
     * @inheritDoc
     */
    public function quoteName($name, $as = null)
    {
        return $this->db->quoteName($name, $as);
    }
    /**
     * @inheritDoc
     */
    public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
    {
        return $this->db->renameTable($oldTable, $newTable, $backup, $prefix);
    }
    /**
     * @inheritDoc
     */
    public function replacePrefix($sql, $prefix = '#__') : string
    {
        return $this->db->replacePrefix($sql, $prefix);
    }
    /**
     * @inheritDoc
     */
    public function select($database) : bool
    {
        return $this->db->select($database);
    }
    /**
     * @inheritDoc
     */
    public function setQuery($query, $offset = 0, $limit = 0)
    {
        return $this->db->setQuery($query, $offset, $limit);
    }
    /**
     * @inheritDoc
     */
    public function transactionCommit($toSavepoint = \false)
    {
        $this->db->transactionCommit($toSavepoint);
    }
    /**
     * @inheritDoc
     */
    public function transactionRollback($toSavepoint = \false)
    {
        $this->db->transactionRollback($toSavepoint);
    }
    /**
     * @inheritDoc
     */
    public function transactionStart($asSavepoint = \false)
    {
        $this->db->transactionStart($asSavepoint);
    }
    /**
     * @inheritDoc
     */
    public function truncateTable($table)
    {
        $this->db->truncateTable($table);
    }
    /**
     * @inheritDoc
     */
    public function unlockTables()
    {
        return $this->db->unlockTables();
    }
    /**
     * @inheritDoc
     */
    public function updateObject($table, &$object, $key, $nulls = \false) : bool
    {
        return $this->db->updateObject($table, $object, $key, $nulls);
    }
    /**
     * Call any other method magically
     *
     * @param   string  $name  Name of method
     * @param   array   $args  Arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->db->{$name}(\implode(',', $args));
    }
    /**
     * Call static methods magically
     *
     * @param   string  $name  Name of method
     * @param   array   $args  Arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        return Factory::getDbo()::$name(\implode(',', $args));
    }
}
