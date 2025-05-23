<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaa0f3e4702a64bd767e6d0579a042510
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Alirezacrr\\LaraArtisanServiceMaker\\' => 35,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Alirezacrr\\LaraArtisanServiceMaker\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitaa0f3e4702a64bd767e6d0579a042510::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaa0f3e4702a64bd767e6d0579a042510::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitaa0f3e4702a64bd767e6d0579a042510::$classMap;

        }, null, ClassLoader::class);
    }
}
