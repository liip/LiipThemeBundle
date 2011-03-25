<?php

if (! file_exists($file = $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php')) {
    throw new \Exception('Modify the phpunit.xml to match your Symfony path.');
}

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Liip\\ThemeBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
