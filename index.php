<?php
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelPath = __DIR__.'/laravel';

if (file_exists($laravelPath.'/storage/framework/maintenance.php')) {
    require $laravelPath.'/storage/framework/maintenance.php';
}

require $laravelPath.'/vendor/autoload.php';

(require_once $laravelPath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
