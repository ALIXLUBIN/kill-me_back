<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/user/login', 'User::login');
$routes->post('/register', 'User::create');
$routes->options('/(:any)', '');
// $routes->post('/blog/postes', 'Blog::postes');
$routes->resource('blog');
$routes->post('character', 'Character::index');
$routes->resource('character');
$routes->post('character/(:segment)/buy', 'Character::buy/$1');
$routes->resource('battle');


// $routes->resource('photos');
// Equivalent to the following:
// $routes->get('photos/new', 'Photos::new');
// $routes->post('photos', 'Photos::create');
// $routes->get('photos', 'Photos::index');
// $routes->get('photos/(:segment)', 'Photos::show/$1');
// $routes->get('photos/(:segment)/edit', 'Photos::edit/$1');
// $routes->put('photos/(:segment)', 'Photos::update/$1');
// $routes->patch('photos/(:segment)', 'Photos::update/$1');
// $routes->delete('photos/(:segment)', 'Photos::delete/$1');
