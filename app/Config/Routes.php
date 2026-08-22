<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Authentication Routes
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/authenticate', 'Auth::authenticate');
$routes->get('/logout', 'Auth::logout');

// Protected Routes (require login)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/dashboard/refresh', 'Dashboard::refresh');

    // Access Denied page
    $routes->get('/access-denied', 'Errors::accessDenied');

    // Customers
    $routes->get('/customers', 'Customers::index');
    $routes->get('/customers/create', 'Customers::create');
    $routes->post('/customers/store', 'Customers::store');
    $routes->get('/customers/view/(:num)', 'Customers::view/$1');
    $routes->get('/customers/edit/(:num)', 'Customers::edit/$1');
    $routes->post('/customers/update/(:num)', 'Customers::update/$1');
    $routes->get(
        '/customers/delete/(:num)',
        'Customers::delete/$1',
        ['filter' => 'role:admin']
    );
    $routes->get('/customers/export', 'Customers::export');
});

// ============================
// REST API Routes
// ============================

// Public: login (issues JWT token)
$routes->post('api/login', 'Api\AuthController::login');

// Protected: everything else requires a valid JWT
$routes->group('api', ['filter' => 'jwt'], function ($routes) {
    $routes->get('customers', 'Api\CustomersController::index');
    $routes->get('customers/(:num)', 'Api\CustomersController::show/$1');
    $routes->post('customers', 'Api\CustomersController::create');
    $routes->put('customers/(:num)', 'Api\CustomersController::update/$1');
    $routes->delete('customers/(:num)', 'Api\CustomersController::delete/$1');
});