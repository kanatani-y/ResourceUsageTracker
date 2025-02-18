<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'AuthController::login', ['filter' => 'session']);

$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('logout', 'AuthController::logout', ['as' => 'logout']);
$routes->get('/', 'ReservationController::schedule');

service('auth')->routes($routes);

$routes->group('admin', ['filter' => 'group:admin'], function ($routes) {
    $routes->get('users', 'UserController::index', ['as' => 'admin.users.index']);
    $routes->get('users/edit/(:num)', 'UserController::edit/$1', ['as' => 'admin.users.edit']);
    $routes->post('users/update/(:num)', 'UserController::update/$1', ['as' => 'admin.users.update']);
    $routes->get('register', 'UserController::create', ['as' => 'admin.register']);
    $routes->post('register', 'UserController::store');
    $routes->get('users/delete/(:num)', 'UserController::delete/$1', ['as' => 'admin.users.delete']);
});

$routes->group('resource', ['filter' => 'group:admin'], function ($routes) {
    $routes->get('/', 'ResourceController::index', ['as' => 'resource.index']);
    $routes->get('create', 'ResourceController::create', ['as' => 'resource.create']);
    $routes->post('store', 'ResourceController::store', ['as' => 'resource.store']);
    $routes->get('show/(:num)', 'ResourceController::show/$1', ['as' => 'resource.show']);
    $routes->get('edit/(:num)', 'ResourceController::edit/$1', ['as' => 'resource.edit']);
    $routes->post('update/(:num)', 'ResourceController::update/$1', ['as' => 'resource.update']);
    $routes->get('delete/(:num)', 'ResourceController::delete/$1', ['as' => 'resource.delete']);
});

$routes->group('account', function ($routes) {
    $routes->get('/', 'AccountController::index', ['as' => 'account.index']); // 全アカウント一覧
    $routes->get('/(:num)', 'AccountController::index/$1', ['as' => 'account.index_with_id']); // 特定のリソース
    $routes->get('edit/(:num)', 'AccountController::edit/$1', ['as' => 'account.edit']);
    $routes->post('update/(:num)', 'AccountController::update/$1', ['as' => 'account.update']);
    $routes->get('create/(:num)', 'AccountController::create/$1', ['as' => 'account.create']);
    $routes->get('create', 'AccountController::create', ['as' => 'account.create_no_resource']);
    $routes->post('store', 'AccountController::store', ['as' => 'account.store']);
    $routes->post('delete/(:num)', 'AccountController::delete/$1', ['as' => 'account.delete']);
});

$routes->group('profile', function ($routes) {
    $routes->get('settings', 'ProfileController::settings', ['as' => 'profile.settings']);
    $routes->post('update', 'ProfileController::update', ['as' => 'profile.update']);
});

$routes->group('reservation', function ($routes) {
    $routes->get('/', 'ReservationController::index', ['as' => 'reservation.index']);
    $routes->get('date/(:segment)?', 'ReservationController::index/$1', ['as' => 'reservation.by_date']);
    $routes->get('schedule', 'ReservationController::schedule', ['as' => 'reservation.schedule']);
    $routes->get('create', 'ReservationController::create', ['as' => 'reservation.create']);
    $routes->post('store', 'ReservationController::store', ['as' => 'reservation.store']);
    $routes->get('edit/(:num)', 'ReservationController::edit/$1', ['as' => 'reservation.edit']);
    $routes->post('update/(:num)', 'ReservationController::update/$1', ['as' => 'reservation.update']);
    $routes->post('delete/(:num)', 'ReservationController::delete/$1', ['as' => 'reservation.delete']);
    $routes->get('getReservations', 'ReservationController::getReservations');
});

