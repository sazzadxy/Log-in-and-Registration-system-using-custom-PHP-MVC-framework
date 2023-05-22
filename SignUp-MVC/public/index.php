<?php

// Composer
require('../vendor/autoload.php');

// Autoloader
// spl_autoload_register(function ($class)
// {
//     $root = dirname(__DIR__); // get the parent directory
//     $file = $root. '/' . str_replace('\\','/',$class). '.php';
//     if (is_readable($file)) {
//         require $root. '/' . str_replace('\\','/', $class). '.php';
//     }
// });


// Error and exception handling
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

// Sessions
session_start();


// Routing
$router = new Core\Router();


$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');
$router->add('login', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);

// $router->add('{controller}/{id:\d+}/{action}');
// $router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);

$router->dispatch($_SERVER['QUERY_STRING']);