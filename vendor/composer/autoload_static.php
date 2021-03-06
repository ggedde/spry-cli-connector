<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit10748005f242167b118e3a79485c4790
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Spry\\SpryConnector\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Spry\\SpryConnector\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Spry\\SpryConnector\\SpryCliConnector' => __DIR__ . '/../..' . '/src/SpryCliConnector.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit10748005f242167b118e3a79485c4790::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit10748005f242167b118e3a79485c4790::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit10748005f242167b118e3a79485c4790::$classMap;

        }, null, ClassLoader::class);
    }
}
