<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbb523eca11cc998f0b74510de948c64d
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'RegularLabs\\Plugin\\System\\AdvancedTemplates\\' => 44,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'RegularLabs\\Plugin\\System\\AdvancedTemplates\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitbb523eca11cc998f0b74510de948c64d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbb523eca11cc998f0b74510de948c64d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitbb523eca11cc998f0b74510de948c64d::$classMap;

        }, null, ClassLoader::class);
    }
}