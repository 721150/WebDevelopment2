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
$router->post('/blogs', 'BlogController@create');
$router->put('/blogs/(\d+)', 'BlogController@update');
$router->delete('/blogs/(\d+)', 'BlogController@delete');

// Routes voor de casus verbindingen
$router->get('/cases', 'CaseController@getAll');
$router->get('/cases/(\d+)', 'CaseController@getOne');
$router->post('/cases', 'CaseController@create');
$router->put('/cases/(\d+)', 'CaseController@update');

// Routes voor de gebruikers verbindingen
$router->get('/users', 'UserController@getAll');
$router->get('/users/(\d+)', 'UserController@getOne');
$router->post('/users', 'UserController@create');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');

$router->run();
?>