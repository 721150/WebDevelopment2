<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$uri = trim($_SERVER['REQUEST_URI'], '/');

$router = new \Bramus\Router\Router();

$router->setNamespace('App\Controllers');

// Routes voor de blog verbindingen
$router->get('/blogs', 'BlogController@getAll');
$router->get('/blogs/(\d+)', 'BlogController@getOne');

// Routes voor de casus verbindingen
$router->get('/cases', 'CaseController@getAll');
$router->get('/cases/(\d+)', 'CaseController@getOne');

$router->run();
?>