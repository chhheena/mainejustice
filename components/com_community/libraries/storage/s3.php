<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class S3_CStorage {
    public $accessKey = null;
    public $secretKey = null;
    public $s3 = null;
    public $bucket = null;
    public $useSSL = false;
    public $name = 's3';
    
    public function _init() {
        $config = CFactory::getConfig();
        $this->accessKey = $config->get('storages3accesskey');
        $this->secretKey = $config->get('storages3secretkey');
        $this->bucket = $config->get('storages3bucket');
        $this->region = $config->get('storages3region');
    }

    protected function getS3()
    {
        if (!$this->s3) {
            require_once JPATH_ROOT . '/components/com_community/libraries/composer/autoload.phar';

            if (!$this->accessKey || !$this->secretKey || !$this->bucket || !$this->region) {
                return;
            }

            $options = array(
                'region'            => $this->region,
                'version'           => '2006-03-01',
                'signature_version' => 'v4',
                'credentials' => array(
                    'key'    => $this->accessKey,
                    'secret' => $this->secretKey,
                ),
            );

            try {
                $this->s3 = new Aws\S3\S3Client($options);
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage('Init S3 Storage error: ' . $e->getMessage(), 'error');
            }
        }

        return $this->s3;
    }

    /**
     * Check if the given storage id exist. We perform local check via db since
     * checking remotely is time consuming
     *
     * @return true is file exits
     * */
    public function exists($storageid, $checkRemote = false) {
        $item = JTable::getInstance('StorageS3', 'CTable');
        return $item->load($storageid);
    }

    /**
     * Put the file into remote storage,
     * @return true if successful
     */
    public function put($storageid, $file) {
        $this->getS3();
        if (!$this->s3) {
            return false;
        }

        $metaHeaders = array(
            "Cache-Control" => "max-age=94608000",
            "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+3 years"))
        );

        $result = $this->s3->putObject(array(
            'Bucket'     => $this->bucket,
            'Key'        => $storageid,
            'SourceFile' => $file,
            'Metadata'   => $metaHeaders,
            'ACL'        => 'public-read',
            'ContentType' => $this->__getMIMEType($storageid)
        ));

        if ($result->get('ObjectURL')) {
            // Insert into our s3 database
            $item = JTable::getInstance('StorageS3', 'CTable');
            $item->storageid = $storageid;
            $item->resource_path = $storageid;
            try {
                $item->store();
            } catch (Exception $e) {
                return false;
            }
            return true;
        }

        return false;
    }

    protected function __getMIMEType(&$file)
	{
		$exts = array(
			'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
			'png' => 'image/png', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf',
			'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml', 'swf' => 'application/x-shockwave-flash', 
			'zip' => 'application/zip', 'gz' => 'application/x-gzip',
			'tar' => 'application/x-tar', 'bz' => 'application/x-bzip',
			'bz2' => 'application/x-bzip2',  'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload', 'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed', 'txt' => 'text/plain',
			'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
			'css' => 'text/css', 'js' => 'text/javascript',
			'xml' => 'text/xml', 'xsl' => 'application/xsl+xml',
			'ogg' => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
			'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
			'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php'
		);

		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if (isset($exts[$ext])) return $exts[$ext];

		return 'application/octet-stream';
	}

    public function delete($storageid) {
        $this->getS3();
        if (!$this->s3) {
            return false;
        }

        //if there is nothing, do not delete
        if(!$storageid){
            return;
        }

        if (is_array($storageid)) {
            $storageids = $storageid;
        } else {
            $storageids[] = $storageid;
        }

        foreach ($storageids as $storageid) {
            $this->s3->deleteObject(array(
                'Bucket' => $this->bucket,
                'Key'    => $storageid
            ));

            $item = JTable::getInstance('StorageS3', 'CTable');
            $item->load($storageid);

            if(!is_null($item->storageid)){
                $item->delete();
            }

        }
        return true;
    }

    /**
     * Retrive the file from remote location and store it locally
     * @param storageid The unique file we want to retrive
     * @param file String	filename where we want to save the file
     */
    public function get($storageid, $file) {
        $this->getS3();
        if (!$this->s3) {
            return false;
        }

        // Put our file (also with public read access)
        $result = $this->s3->getObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $storageid
        ));

        $body = $result->get('Body');
        JFile::write($file, $body);

        return true;
    }

    /**
     * Return the absolute URI path to the resource
     */
    public function getURI($storageId) {
        $item = JTable::getInstance('StorageS3', 'CTable');
        $item->load($storageId);

        if (isset($item->resource_path)) {
            $uri = JUri::getInstance();
            /* Get S3 URL format */
            $s3Url = CFactory::getConfig()->get('storages3bucket_url', 's3.amazonaws.com/<bucket>');

            /* Replace <bucket> by real bucketname */
            $s3Url = str_replace('<bucket>', $this->bucket, $s3Url);
            $s3Url = str_replace('.'.$this->bucket, '', $s3Url);

            /* General final path */
            if ($uri->isSSL()) {
                return 'https://' . $s3Url . '/' . $item->resource_path;
            } else {

                return 'http://' . $s3Url . '/' . $item->resource_path;
            }
        }

        return $storageId;
    }

    /**
     * @since 4.2 to retrieve all the files within the folder
     * @param string $prefix - path of the folder that needs to be iterated
     * @return array|false
     */
    public function getFileList($prefix = 'images/avatar/'){
        $this->getS3();
        if (!$this->s3) {
            return false;
        }

        //@todo there are some limitation here, it can only retrieve 1k results, a marker need to be set to retrieve the next page
        $result = $this->s3->listObjects(array(
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
        ));

        $list = array();
        foreach ($result['Contents'] as $item) {
            $list[$item['Key']] = $item;
        }

        return $list;
    }

}

class CTableStorageS3 extends JTable {

    var $storageid = null;
    var $resource_path = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_storage_s3', 'storageid', $db);
    }

    public function store($updateNulls = null) {
        $k = $this->_tbl_key;

        if (empty($this->$k)) {
            return false;
        }

        $db = $this->getDBO();

        $query = 'SELECT count(*)'
                . ' FROM ' . $this->_tbl
                . ' WHERE ' . $this->_tbl_key . ' = ' . $db->Quote($this->storageid);
        $db->setQuery($query);
        $isExist = $db->loadResult();

        if (!$isExist) {
            $query = 'INSERT INTO ' . $this->_tbl
                    . ' SET ' . $db->quoteName('storageid') . '=' . $db->Quote($this->storageid)
                    . ' , ' . $db->quoteName('resource_path') . '= ' . $db->Quote($this->resource_path);
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        } else {
            $query = 'UPDATE ' . $this->_tbl
                    . ' SET ' . $db->quoteName('resource_path') . '= ' . $db->Quote($this->resource_path)
                    . ' WHERE ' . $db->quoteName('storageid') . '=' . $db->Quote($this->storageid);
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return true;
    }

}
