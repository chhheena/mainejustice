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

use JchOptimize\Container;
use JchOptimize\Core\Admin\Ajax\OptimizeImage;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\FeatureHelpers\Webp;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Plugin;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
\defined('_JCH_EXEC') or die('Restricted access');
class Tasks
{
    public static $startHtaccessLine = '## BEGIN EXPIRES CACHING - JCH OPTIMIZE ##';
    public static $endHtaccessLine = '## END EXPIRES CACHING - JCH OPTIMIZE ##';
    /**
     * @return string
     */
    public static function leverageBrowserCaching() : string
    {
        $htaccess = Paths::rootPath() . '/.htaccess';
        if (\file_exists($htaccess)) {
            $contents = \file_get_contents($htaccess);
            $cleanContents = \preg_replace(self::getHtaccessRegex(), \PHP_EOL, $contents);
            $startLine = self::$startHtaccessLine;
            $endLine = self::$endHtaccessLine;
            $expires = <<<APACHECONFIG

{$startLine}
<IfModule mod_expires.c>
\tExpiresActive on

\t# Your document html
\tExpiresByType text/html "access plus 0 seconds"

\t# Data
\tExpiresByType text/xml "access plus 0 seconds"
\tExpiresByType application/xml "access plus 0 seconds"
\tExpiresByType application/json "access plus 0 seconds"

\t# Feed
\tExpiresByType application/rss+xml "access plus 1 hour"
\tExpiresByType application/atom+xml "access plus 1 hour"

\t# Favicon (cannot be renamed)
\tExpiresByType image/x-icon "access plus 1 week"

\t# Media: images, video, audio
\tExpiresByType image/gif "access plus 1 year"
\tExpiresByType image/png "access plus 1 year"
\tExpiresByType image/jpg "access plus 1 year"
\tExpiresByType image/jpeg "access plus 1 year"
\tExpiresByType image/webp "access plus 1 year"
\tExpiresByType audio/ogg "access plus 1 year"
\tExpiresByType video/ogg "access plus 1 year"
\tExpiresByType video/mp4 "access plus 1 year"
\tExpiresByType video/webm "access plus 1 year"

\t# HTC files (css3pie)
\tExpiresByType text/x-component "access plus 1 year"

\t# Webfonts
\tExpiresByType application/font-ttf "access plus 1 year"
\tExpiresByType font/* "access plus 1 year"
\tExpiresByType application/font-woff "access plus 1 year"
\tExpiresByType application/font-woff2 "access plus 1 year"
\tExpiresByType image/svg+xml "access plus 1 year"
\tExpiresByType application/vnd.ms-fontobject "access plus 1 year"

\t# CSS and JavaScript
\tExpiresByType text/css "access plus 1 year"
\tExpiresByType type/javascript "access plus 1 year"
\tExpiresByType application/javascript "access plus 1 year"

\t<IfModule mod_headers.c>
\t\tHeader append Cache-Control "public"
\t\t<FilesMatch ".(js|css|xml|gz|html)\$">
\t\t\tHeader append Vary: Accept-Encoding
\t\t</FilesMatch>
\t</IfModule>

</IfModule>

<IfModule mod_brotli.c>
\t<IfModule mod_filter.c>
\t\tAddOutputFilterByType BROTLI_COMPRESS text/html text/xml text/plain 
\t\tAddOutputFilterByType BROTLI_COMPRESS application/rss+xml application/xml application/xhtml+xml 
\t\tAddOutputFilterByType BROTLI_COMPRESS text/css 
\t\tAddOutputFilterByType BROTLI_COMPRESS text/javascript application/javascript application/x-javascript 
\t\tAddOutputFilterByType BROTLI_COMPRESS image/x-icon image/svg+xml
\t\tAddOutputFilterByType BROTLI_COMPRESS application/rss+xml
\t\tAddOutputFilterByType BROTLI_COMPRESS application/font application/font-truetype application/font-ttf
\t\tAddOutputFilterByType BROTLI_COMPRESS application/font-otf application/font-opentype
\t\tAddOutputFilterByType BROTLI_COMPRESS application/font-woff application/font-woff2
\t\tAddOutputFilterByType BROTLI_COMPRESS application/vnd.ms-fontobject
\t\tAddOutputFilterByType BROTLI_COMPRESS font/ttf font/otf font/opentype font/woff font/woff2
\t</IfModule>
</IfModule>

<IfModule mod_deflate.c>
\t<IfModule mod_filter.c>
\t\tAddOutputFilterByType DEFLATE text/html text/xml text/plain 
\t\tAddOutputFilterByType DEFLATE application/rss+xml application/xml application/xhtml+xml 
\t\tAddOutputFilterByType DEFLATE text/css 
\t\tAddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript 
\t\tAddOutputFilterByType DEFLATE image/x-icon image/svg+xml
\t\tAddOutputFilterByType DEFLATE application/rss+xml
\t\tAddOutputFilterByType DEFLATE application/font application/font-truetype application/font-ttf
\t\tAddOutputFilterByType DEFLATE application/font-otf application/font-opentype
\t\tAddOutputFilterByType DEFLATE application/font-woff application/font-woff2
\t\tAddOutputFilterByType DEFLATE application/vnd.ms-fontobject
\t\tAddOutputFilterByType DEFLATE font/ttf font/otf font/opentype font/woff font/woff2
\t</IfModule>
</IfModule>

# Don't compress files with extension .gz or .br
<IfModule mod_rewrite.c>
\tRewriteRule "\\.(gz|br)\$" "-" [E=no-gzip:1,E=no-brotli:1]
</IfModule>

<IfModule !mod_rewrite.c>
\t<IfModule mod_setenvif.c>
\t\tSetEnvIfNoCase Request_URI \\.(gz|br)\$ no-gzip no-brotli
\t</IfModule>
</IfModule>
{$endLine}

APACHECONFIG;
            $expires = \str_replace(array("\r\n", "\n"), \PHP_EOL, $expires);
            $str = $expires . $cleanContents;
            return File::write($htaccess, $str);
        } else {
            return 'FILEDOESNTEXIST';
        }
    }
    private static function getHtaccessRegex() : string
    {
        return '#[\\r\\n]*' . \preg_quote(self::$startHtaccessLine) . '.*?' . \preg_quote(\rtrim(self::$endHtaccessLine, "# \n\r\t\v\x00")) . '[^\\r\\n]*[\\r\\n]*#s';
    }
    /**
     */
    public static function cleanHtaccess()
    {
        $htaccess = Paths::rootPath() . '/.htaccess';
        if (\file_exists($htaccess)) {
            $contents = \file_get_contents($htaccess);
            $cleanContents = \preg_replace(self::getHtaccessRegex(), '', $contents, -1, $count);
            if ($count > 0) {
                File::write($htaccess, $cleanContents);
            }
        }
    }
    public static function restoreBackupImages()
    {
        $backupPath = Paths::backupImagesParentDir() . OptimizeImage::$backup_folder_name;
        if (!\is_dir($backupPath)) {
            return 'BACKUPPATHDOESNTEXIST';
        }
        $aFiles = Folder::files($backupPath, '.', \false, \false, []);
        $bFailure = \false;
        foreach ($aFiles as $backupContractedFile) {
            $bSuccess = \false;
            $aPotentialOriginalFilePaths = [AdminHelper::expandFileName($backupContractedFile), AdminHelper::expandFileNameLegacy($backupContractedFile)];
            foreach ($aPotentialOriginalFilePaths as $originalFilePath) {
                if (@\file_exists($originalFilePath)) {
                    //Attempt to restore backup images
                    if (AdminHelper::copyImage($backupContractedFile, $originalFilePath)) {
                        File::delete(Webp::getWebpPath($originalFilePath));
                        File::delete(Webp::getWebpPathLegacy($originalFilePath));
                    }
                    AdminHelper::unmarkOptimized($originalFilePath);
                    $bSuccess = \true;
                    break;
                }
            }
            if (!$bSuccess) {
                $bFailure = \true;
            }
        }
        \clearstatcache();
        if ($bFailure) {
            return 'SOMEIMAGESDIDNTRESTORE';
        } else {
            self::deleteBackupImages();
        }
        return \true;
    }
    public static function deleteBackupImages()
    {
        $backupPath = Paths::backupImagesParentDir() . OptimizeImage::$backup_folder_name;
        if (!\is_dir($backupPath)) {
            return 'BACKUPPATHDOESNTEXIST';
        }
        return Folder::delete($backupPath);
    }
    public static function generateNewCacheKey()
    {
        $container = Container::getInstance();
        $rand = \rand();
        /** @var Registry $params */
        $params = $container->get('params');
        $params->set('cache_random_key', $rand);
        Plugin::saveSettings($params);
    }
}
