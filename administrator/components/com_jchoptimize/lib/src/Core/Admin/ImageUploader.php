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
namespace JchOptimize\Core\Admin;

\defined('_JCH_EXEC') or die('Restricted access');
use CURLFile;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Exception;
use Joomla\Registry\Registry;
use _JchOptimizeVendor\Laminas\Diactoros\Response;
use Psr\Http\Client\ClientInterface;
class ImageUploader
{
    protected $auth = array();
    /**
     * @var ClientInterface|null
     */
    private $http;
    /**
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Registry $params, ?ClientInterface $http)
    {
        if (\is_null($http)) {
            throw new Exception\InvalidArgumentException('No http client transporter found', 500);
        }
        $this->http = $http;
        $this->auth = ['auth' => ['dlid' => $params->get('pro_downloadid', ''), 'secret' => $params->get('hidden_api_secret', '')]];
    }
    /**
     * @throws \Exception
     */
    public function upload($opts = array())
    {
        if (empty($opts['files'][0])) {
            throw new Exception\InvalidArgumentException('File parameter was not provided', 500);
        }
        //if (!files_exists($opts['files']))
        //{
        //        throw new Exception('File \'' . $opts['files'] . '\' does not exist', 404);
        //}
        $files = array();
        foreach ($opts['files'] as $i => $file) {
            if (\class_exists('CURLFile')) {
                $files['files[' . $i . ']'] = new CURLFile($file, self::getMimeType($file), self::getPostedFileName($file));
            } else {
                throw new Exception\MissingDependencyException('CURLFile not available, cannot upload files', 500);
            }
        }
        unset($opts['files']);
        $data = \array_merge($files, array("data" => \json_encode(\array_merge($this->auth, $opts))));
        return self::request($data);
    }
    public static function getMimeType($file)
    {
        return \extension_loaded('fileinfo') ? \mime_content_type($file) : 'image/' . \preg_replace(array('#\\.jpg#', '#^.*?\\.(jpeg|png|gif)(?:[?\\#]|$)#i'), array('.jpeg', '\\1'), \strtolower($file));
    }
    public static function getPostedFileName($file)
    {
        return AdminHelper::contractFileNameLegacy($file);
    }
    private function request($data)
    {
        \ini_set('upload_max_filesize', '50M');
        \ini_set('post_max_size', '50M');
        \ini_set('max_input_time', 600);
        \ini_set('max_execution_time', 600);
        try {
            /** @var Response $response */
            $response = $this->http->post("https://api2.jch-optimize.net/", $data, ['Content-Type' => 'multipart/form-data']);
        } catch (\Exception $e) {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Exception trying to access API with message: ' . $e->getMessage()));
        }
        if ($response->getStatusCode() != '200') {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Response returned with status code: ' . $response->getStatusCode()), 500);
        }
        $body = $response->getBody();
        $body->rewind();
        $contents = \json_decode($body->getContents());
        if (\is_null($contents)) {
            return new \JchOptimize\Core\Admin\Json(new \Exception('Improper formatted response: ' . $body->getContents()));
        }
        return $contents;
    }
}
