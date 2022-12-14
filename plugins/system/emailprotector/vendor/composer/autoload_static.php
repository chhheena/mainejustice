<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8d7ee705be67909ecf069cb7b7b1ae01
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RegularLabs\\Plugin\\System\\EmailProtector\\' => 41,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RegularLabs\\Plugin\\System\\EmailProtector\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit8d7ee705be67909ecf069cb7b7b1ae01::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8d7ee705be67909ecf069cb7b7b1ae01::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8d7ee705be67909ecf069cb7b7b1ae01::$classMap;

        }, null, ClassLoader::class);
    }
}
