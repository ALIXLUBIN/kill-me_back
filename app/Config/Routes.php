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
$routes->post('character', 'Character::index');
$routes->resource('character');
$routes->post('character/(:segment)/buy', 'Character::buy/$1');
$routes->get('battle/endGame', 'Battle::endGame');
$routes->resource('battle');
$routes->resource('joinBattle');
$routes->post('joinBattle/joinAfterWait', 'JoinBattle::joinAfterWait');
$routes->post('battle/attack/(:segment)', 'Battle::attack/$1');

$routes->cli('validateTocken/(:any)', 'User::validateTocken/$1');

// Routes d'administration

$routes->resource('admin/character', ['controller' => '\App\Controllers\Admin\Admin_character']);
$routes->resource('admin/attaque', ['controller' => '\App\Controllers\Admin\Admin_attaque']);
$routes->put('admin/characterAttack/(:segment)', 'Admin\Admin_character::updateCharacterAttack/$1');


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
