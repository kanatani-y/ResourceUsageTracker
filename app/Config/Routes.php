<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'AuthController::login', ['filter' => 'session']);

$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('logout', 'AuthController::logout', ['as' => 'logout']);
$routes->get('/', 'Home::index', ['as' => 'home']);

service('auth')->routes($routes);

$routes->group('admin', ['filter' => 'group:admin'], function ($routes) {
    $routes->get('users', 'Admin\UserController::index', ['as' => 'admin.users.index']);
    $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1', ['as' => 'admin.users.edit']);
    $routes->post('users/update/(:num)', 'Admin\UserController::update/$1', ['as' => 'admin.users.update']);
    $routes->get('register', 'Admin\UserController::create', ['as' => 'admin.register']);
    $routes->post('register', 'Admin\UserController::store');
    $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1', ['as' => 'admin.users.delete']);
});

$routes->group('resource', ['filter' => 'group:admin'], function ($routes) {
    $routes->get('/', 'Resource\ResourceController::index', ['as' => 'resource.index']);
    $routes->get('create', 'Resource\ResourceController::create', ['as' => 'resource.create']);
    $routes->post('store', 'Resource\ResourceController::store', ['as' => 'resource.store']);
    $routes->get('edit/(:num)', 'Resource\ResourceController::edit/$1', ['as' => 'resource.edit']);
    $routes->post('update/(:num)', 'Resource\ResourceController::update/$1', ['as' => 'resource.update']);
    $routes->get('delete/(:num)', 'Resource\ResourceController::delete/$1', ['as' => 'resource.delete']);
});
