<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\public\index.php
// Create at 2508041215 Ver1.04

require_once __DIR__ . '/../src/core/bootstrap.php';
require_once __DIR__ . '/../src/core/Router.php';

// Initialize Router with base path
$router = new Router('/AppMart');

// Define routes
$router->get('/', 'AppController@listApps');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/app/register', 'AppController@registerApp');
$router->post('/app/register', 'AppController@registerApp');
$router->get('/app/update', 'AppController@updateApp');
$router->post('/app/update', 'AppController@updateApp');
$router->post('/app/delete', 'AppController@deleteApp');
$router->get('/myapps', 'AppController@myApps');
$router->get('/app/detail', 'AppController@appDetail');
$router->get('/app/download', 'AppController@downloadApp');

$router->get('/admin/pending', 'AdminController@pendingApps');
$router->post('/admin/approve', 'AdminController@approveApp');
$router->post('/admin/reject', 'AdminController@rejectApp');

// Dispatch the request
$router->dispatch();
