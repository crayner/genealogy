<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

$_SERVER['APP_DEBUG'] = ($_SERVER['APP_ENV'] && $_SERVER['APP_ENV'] === 'dev') || $_SERVER['APP_DEBUG'];

if (key_exists('APP_DEBUG', $_SERVER) && $_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) key_exists('APP_DEBUG', $_SERVER) && $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
