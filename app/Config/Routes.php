<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'AuthController::login', ['filter' => 'session']);

$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('login', 'AuthController::login');
$routes->post('logout', 'AuthController::logout', ['as' => 'logout']);
$routes->get('guest-login', 'AuthController::guestLogin', ['as' => 'guest.login']);
$routes->get('/', 'ReservationController::schedule');

$routes->group('admin', ['filter' => 'group:admin'], function ($routes) {
    $routes->get('users', 'UserController::index', ['as' => 'admin.users.index']);
    $routes->get('users/edit/(:num)', 'UserController::edit/$1', ['as' => 'admin.users.edit']);
    $routes->post('users/update/(:num)', 'UserController::update/$1', ['as' => 'admin.users.update']);
    $routes->get('users/restore/(:num)', 'UserController::restore/$1', ['as' => 'admin.users.restore']);
    $routes->get('register', 'UserController::create', ['as' => 'admin.users.register']);
    $routes->post('register', 'UserController::store', ['as' => 'admin.users.store']);
    $routes->get('users/delete/(:num)', 'UserController::delete/$1', ['as' => 'admin.users.delete']);
});

// service('auth')->routes($routes);

$routes->group('resources', function ($routes) {
    $routes->get('/', 'ResourceController::index', ['as' => 'resources.index']);
    $routes->get('create', 'ResourceController::create', ['as' => 'resources.create']);
    $routes->post('store', 'ResourceController::store', ['as' => 'resources.store']);
    $routes->get('show/(:num)', 'ResourceController::show/$1', ['as' => 'resources.show']);
    $routes->get('edit/(:num)', 'ResourceController::edit/$1', ['as' => 'resources.edit']);
    $routes->post('update/(:num)', 'ResourceController::update/$1', ['as' => 'resources.update']);
    $routes->get('delete/(:num)', 'ResourceController::delete/$1', ['as' => 'resources.delete']);
});

$routes->group('accounts', function ($routes) {
    $routes->get('/', 'AccountController::index', ['as' => 'accounts.index']); // 全アカウント一覧
    $routes->get('/(:num)', 'AccountController::index/$1', ['as' => 'accounts.index_with_id']); // 特定のリソース
    $routes->get('edit/(:num)', 'AccountController::edit/$1', ['as' => 'accounts.edit']);
    $routes->post('update/(:num)', 'AccountController::update/$1', ['as' => 'accounts.update']);
    $routes->get('create/(:num)', 'AccountController::create/$1', ['as' => 'accounts.create']);
    $routes->get('create', 'AccountController::create', ['as' => 'accounts.create_no_resource']);
    $routes->post('store', 'AccountController::store', ['as' => 'accounts.store']);
    $routes->post('delete/(:num)', 'AccountController::delete/$1', ['as' => 'accounts.delete']);
});

$routes->group('profiles', function ($routes) {
    $routes->get('settings', 'ProfileController::settings', ['as' => 'profiles.settings']);
    $routes->post('update', 'ProfileController::update', ['as' => 'profiles.update']);
});

$routes->group('reservations', function ($routes) {
    // **ゲストを除外するミドルウェア**
    $routes->group('', ['filter' => 'auth'], function ($routes) {
        $routes->get('create', 'ReservationController::create', ['as' => 'reservations.create']);
        $routes->post('store', 'ReservationController::store', ['as' => 'reservations.store']);
    });

    $routes->get('/', 'ReservationController::schedule', ['as' => 'reservations.index']);
    $routes->get('date/(:segment)?', 'ReservationController::index/$1', ['as' => 'reservations.by_date']);
    $routes->get('schedule', 'ReservationController::schedule', ['as' => 'reservations.schedule']);

    // 通常の `create` ルート（パラメータなし）
    $routes->get('create', 'ReservationController::create', ['as' => 'reservations.create']);

    // `create` ルート（パラメータあり: `resource_id`, `account_id`, `time`）
    $routes->get('create/(:num)/(:num)/(:segment)/(:segment)', 'ReservationController::create/$1/$2/$3/$4', ['as' => 'reservations.create.with_params']);

    $routes->post('store', 'ReservationController::store', ['as' => 'reservations.store']);
    $routes->get('edit/(:num)', 'ReservationController::edit/$1', ['as' => 'reservations.edit']);
    $routes->post('update/(:num)', 'ReservationController::update/$1', ['as' => 'reservations.update']);
    $routes->post('delete/(:num)', 'ReservationController::delete/$1', ['as' => 'reservations.delete']);
    $routes->get('getReservations', 'ReservationController::getReservations');
});


