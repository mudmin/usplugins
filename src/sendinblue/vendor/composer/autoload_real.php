<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit1c4ca9eb5efb1c5c3338c8c9220a5ce5
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit1c4ca9eb5efb1c5c3338c8c9220a5ce5', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit1c4ca9eb5efb1c5c3338c8c9220a5ce5', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit1c4ca9eb5efb1c5c3338c8c9220a5ce5::getInitializer($loader));

        $loader->register(true);

        $includeFiles = \Composer\Autoload\ComposerStaticInit1c4ca9eb5efb1c5c3338c8c9220a5ce5::$files;
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire1c4ca9eb5efb1c5c3338c8c9220a5ce5($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequire1c4ca9eb5efb1c5c3338c8c9220a5ce5($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}
