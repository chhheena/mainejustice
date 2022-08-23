<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c323330ad7fdb81fa2b299c73a8c4de
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RegularLabs\\Plugin\\System\\ArticlesAnywhere\\' => 43,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RegularLabs\\Plugin\\System\\ArticlesAnywhere\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2c323330ad7fdb81fa2b299c73a8c4de::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2c323330ad7fdb81fa2b299c73a8c4de::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2c323330ad7fdb81fa2b299c73a8c4de::$classMap;

        }, null, ClassLoader::class);
    }
}