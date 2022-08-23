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

class RssFactoryBackendModelBackup extends JModelLegacy
{
    protected $tables = array(
        '#__categories',
        '#__rssfactory',
        '#__rssfactory_ads',
        '#__rssfactory_ad_category_map',
    );

    public function restoreBackup($data = array())
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.archive');

        $dbo = $this->getDbo();

        // Check for uploaded archive.
        if (!isset($data['error']) || 4 == $data['error']) {
            $this->setState('error', FactoryTextRss::_('backup_task_restore_error_no_file_uploaded'));
            return false;
        }

        // Check for errors uploading the archive.
        if (0 != $data['error']) {
            $this->setState('error', FactoryTextRss::sprintf('backup_task_restore_error_upload_error', $data['error']));
            return false;
        }

        // Check if uploaded file is a zip archive.
        if ('zip' != strtolower(JFile::getExt($data['name']))) {
            $this->setState('error', FactoryTextRss::_('backup_task_restore_error_not_valid_archive'));
            return false;
        }

        // Extract backup archive.
        $zip = new \Joomla\Archive\Zip();
        if (!$zip->extract($data['tmp_name'], RSS_FACTORY_TMP_PATH)) {
            $this->setState('error', FactoryTextRss::_('backup_task_restore_error_extracting_archive'));
            return false;
        }

        // Truncate tables.
        foreach ($this->tables as $table) {
            if ('#__categories' == $table) {
                JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
                $table = $this->getTable('Category', 'JTable');

                $query = $dbo->getQuery(true)
                    ->select('id')
                    ->from('#__categories')
                    ->where('extension = ' . $dbo->quote('com_rssfactory'));
                $results = $dbo->setQuery($query)
                    ->loadObjectList();

                foreach ($results as $result) {
                    $table->load($result->id);
                    $table->delete($result->id);
                }
                continue;
            }

            $dbo->setQuery('TRUNCATE TABLE ' . $dbo->quoteName($table))
                ->execute();
        }

        // Get restore queries.
        $sql = file(RSS_FACTORY_TMP_PATH . DS . 'RSSFactoryPRO.sql', FILE_IGNORE_NEW_LINES);

        // Restore data.
        if (!$this->executeSQL($sql)) {
            return false;
        }

        $this->restoreConfiguration();

        $this->removeTmpBackupFiles();

        return true;
    }

    public function generateBackup()
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.archive');

        $zip = new \Joomla\Archive\Zip();

        $files[] = array(
            'name' => 'configuration.json',
            'data' => JComponentHelper::getParams('com_rssfactory')->toString(),
        );
        $files[] = array(
            'name' => 'RSSFactoryPRO.sql',
            'data' => $this->getBackupSQL(),
        );

        $backupName = 'RSSFactoryPro_Backup_' . date('Y-m-d') . '.zip';
        $backupFile = RSS_FACTORY_TMP_PATH . DS . $backupName;

        if (!$zip->create($backupFile, $files)) {
            $this->setState('error', FactoryTextRss::_('backup_task_generate_error_create_archive'));
            return false;
        }

        $backupSize = filesize($backupFile);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Content-Type: application/x-compressed");
        header('Content-Disposition: attachment; filename="' . $backupName . '"');
        header("Content-Length: " . $backupSize);
        header("Content-size: " . $backupSize);
        header('Content-Transfer-Encoding: binary');

        readfile($backupFile);
        unlink($backupFile);

        jexit();
    }

    public function import($data, $separator)
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.archive');

        $dbo = $this->getDbo();

        // Check for uploaded archive.
        if (!isset($data['error']) || 4 == $data['error']) {
            $this->setState('error', FactoryTextRss::_('backup_task_restore_error_no_file_uploaded'));
            return false;
        }

        // Check for errors uploading the archive.
        if (0 != $data['error']) {
            $this->setState('error', FactoryTextRss::sprintf('backup_task_restore_error_upload_error', $data['error']));
            return false;
        }

        if ('TAB' == $separator) {
            $separator = "\t";
        }

        $handle = fopen($data['tmp_name'], 'r');
        $count = 0;

        while ($data = fgetcsv($handle, 10000, $separator)) {
            if (count($data) != 3) {
                continue;
            }

            $cat = $data[0];
            $title = $data[1];
            $url = $data[2];
            $row = JTable::getInstance('Feed', 'RssFactoryTable');

            $row->title = $title;

            // Check if category exists.
            if (!ctype_digit($cat)) {
                $query = $dbo->getQuery(true)
                    ->select('c.id')
                    ->from('#__categories c')
                    ->where('c.extension = ' . $dbo->quote('com_rssfactory'))
                    ->where('c.title = ' . $dbo->quote($cat));
                $result = $dbo->setQuery($query)
                    ->loadResult();

                if (!$result) {
                    $temp = array(
                        'parent_id' => 1,
                        'level'     => 1,
                        'path'      => JApplicationHelper::stringURLSafe($cat),
                        'extension' => 'com_rssfactory',
                        'title'     => $cat,
                        'published' => 0,
                        'access'    => 1,
                        'language'  => '*',
                    );

                    if (false !== $category = $this->insertCategory($temp)) {
                        $row->cat = $category->id;
                    }
                } else {
                    $row->cat = $result;
                }
            } else {
                $row->cat = $cat;
            }

            $row->url = $url;
            $row->published = 0;
            $row->ordering = $count;

            if ($row->store()) {
                $count++;
            }
        }

        fclose($handle);

        return true;
    }

    public function executeSQL($sql)
    {
        $this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
        $this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_rssfactory/tables');

        $dbo = $this->getDbo();
        $ids = array();

        $errors = array();
        $restored = array(
            'categories' => 0,
            'feeds'      => 0,
            'ads'        => 0,
        );

        foreach ($sql as $line) {
            $line = json_decode($line);

            if (is_null($line)) {
                continue;
            }

            switch ($line->table) {
                case '#__rssfactory':
                    $table = $this->getTable('Feed', 'RssFactoryTable');
                    $table->bind((array)$line->definition);

                    if (!$dbo->insertObject($line->table, $table)) {
                        $errors[] = $line->definition;
                    } else {
                        $restored['feeds']++;
                    }
                    break;

                case '#__rssfactory_ads':
                    $table = $this->getTable('Ad', 'RssFactoryTable');
                    $data = (array)$line->definition;

                    $array = array();
                    $categories = new JRegistry($data['categories_assigned']);
                    foreach ($categories->toArray() as $category) {
                        $array[] = $ids[$category];
                    }
                    $categories = new JRegistry($array);
                    $data['categories_assigned'] = $categories->toString();

                    $table->bind($data);
                    if (!$dbo->insertObject($line->table, $table)) {
                        $errors[] = $line->definition;
                    } else {
                        $restored['ads']++;
                    }
                    break;

                case '#__categories':
                    $table = $this->getTable('Category', 'JTable');
                    $data = (array)$line->definition;

                    $oldId = $data['id'];

                    unset($data['id'], $data['asset_id'], $data['lft'], $data['rgt'], $data['path']);

                    if (1 != $data['parent_id']) {
                        $data['parent_id'] = $ids[$data['parent_id']];
                    }

                    $table->setLocation($data['parent_id'], 'last-child');
                    if (!$table->save($data)) {
                        $errors[] = $line->definition;
                    } else {
                        $restored['categories']++;
                    }

                    $ids[$oldId] = $table->id;
                    break;

                case '#__rssfactory_ad_category_map':
                    $table = $this->getTable('AdCategoryMap', 'RssFactoryTable');
                    $data = (array)$line->definition;

                    $data['categoryId'] = $ids[$data['categoryId']];

                    $table->bind($data);
                    if (!$dbo->insertObject($line->table, $table)) {
                        $errors[] = $line->definition;
                    }
                    break;
            }
        }

        $table = $this->getTable('Category', 'JTable');
        $table->rebuild();

        if ($errors) {
            $this->setState('error', implode('<br />', $errors));
        }

        $this->setState('restored', $restored);

        return true;
    }

    protected function generateTableBackup($tableName)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true)
            ->select('*')
            ->from($tableName);

        if ('#__categories' == $tableName) {
            $query->where('extension = ' . $dbo->quote('com_rssfactory'));
        }

        $results = $dbo->setQuery($query)
            ->loadObjectList();

        $array = array();
        foreach ($results as $result) {
            $array[] = json_encode(array('table' => $tableName, 'definition' => $result));
        }

        return implode(PHP_EOL, $array);
    }

    protected function getBackupSQL()
    {
        $data = array();

        foreach ($this->tables as $table) {
            $data[] = $this->generateTableBackup($table);
        }

        $model = JModelLegacy::getInstance('About', 'RssFactoryBackendModel');
        $data[] = PHP_EOL . '/*version=' . $model->getCurrentVersion() . '*/';

        return implode(PHP_EOL, $data);
    }

    protected function removeTmpBackupFiles()
    {
        if ($handle = opendir(RSS_FACTORY_TMP_PATH)) {
            /* This is the correct way to loop over the directory. */
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (!preg_match('#\.htaccess$#', $file)) {
                    $realFile = RSS_FACTORY_TMP_PATH . DS . $file;
                    if (is_file($realFile)) {
                        JFile::delete($realFile);
                    } else if (is_dir($realFile)) {
                        JFolder::delete($realFile);
                    }
                }
            }
            closedir($handle);
        }
    }

    protected function restoreConfiguration()
    {
        $contents = file_get_contents(RSS_FACTORY_TMP_PATH . DS . 'configuration.json');
        $configuration = new JRegistry($contents);

        $extension = JTable::getInstance('Extension');
        $result = $extension->find(array('type' => 'component', 'element' => 'com_rssfactory'));

        $extension->load($result);
        $extension->params = $configuration->toString();
        return $extension->store();
    }

    private function insertCategory($data)
    {
        try {
            $category = JTable::getInstance('Category');
            $category->setLocation(1, 'last-child');
            $category->save($data);
        } catch (Exception $e) {
            return false;
        }

        return $category;
    }
}
