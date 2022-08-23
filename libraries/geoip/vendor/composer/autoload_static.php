<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite41afa0fc37c34093d4986f4316442d9
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'splitbrain\\PHPArchive\\' => 22,
        ),
        'R' => 
        array (
            'RegularLabs\\Library\\GeoIp\\' => 26,
        ),
        'M' => 
        array (
            'MaxMind\\WebService\\' => 19,
            'MaxMind\\Exception\\' => 18,
            'MaxMind\\Db\\' => 11,
        ),
        'G' => 
        array (
            'GeoIp2\\' => 7,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'splitbrain\\PHPArchive\\' => 
        array (
            0 => __DIR__ . '/..' . '/splitbrain/php-archive/src',
        ),
        'RegularLabs\\Library\\GeoIp\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'MaxMind\\WebService\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService',
        ),
        'MaxMind\\Exception\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception',
        ),
        'MaxMind\\Db\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db',
        ),
        'GeoIp2\\' => 
        array (
            0 => __DIR__ . '/..' . '/geoip2/geoip2/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite41afa0fc37c34093d4986f4316442d9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite41afa0fc37c34093d4986f4316442d9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite41afa0fc37c34093d4986f4316442d9::$classMap;

        }, null, ClassLoader::class);
    }
}
