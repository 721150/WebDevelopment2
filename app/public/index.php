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
$router->get('/cases/case/(\d+)', 'CaseController@getOne');
$router->get('/cases/user/(\d+)', 'CaseController@getByUser');
$router->post('/cases', 'CaseController@create');
$router->put('/cases/(\d+)', 'CaseController@update');

// Routes voor de gebruikers verbindingen
$router->get('/users', 'UserController@getAll');
$router->get('/users/(\d+)', 'UserController@getOne');
$router->post('/users/admin', 'UserController@createAdmin');
$router->post('/users/handler', 'UserController@createHandler');
$router->post('/users/applicant', 'UserController@createApplicant');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');
$router->post('/users/login', 'UserController@login');

// Routes voor de opleidingen
$router->get('/educations', 'EducationController@getAll');

// Routes voor de scholen
$router->get('/institutions', 'InstitutionController@getAll');

// Routes voor de onderwerpen
$router->get('/subjects', 'SubjectController@getAll');

// Routes voor de type rechten
$router->get('/typesOfLows', 'TypeOfLowController@getAll');

// Routes voor de reacties
$router->post('/reacties', 'ReactieController@create');

$router->run();
?>