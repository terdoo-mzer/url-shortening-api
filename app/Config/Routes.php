<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/(:alphanum)', 'UrlController::retrieveLongUrl/$1'); // This route will be responsible for grabbing shortcode and redirecting to original url
$routes->get('/', 'UrlController::test'); // This route will be responsible for grabbing shortcode and redirecting to original url

 $routes->group('auth/v1', static function ($routes) {
    $routes->post('register', 'UserController::createUser');
    $routes->post('login', 'UserController::loginUser');
    $routes->delete('logout', 'UserController::logout');
    $routes->post('refreshtoken', 'TokenAuthenticationController::getNewRefreshToken');
});

// Please note that the filters have been applied to this routes in the Filters.php file.
// Therefore, there is no need to individually apply them here to the routes. This is more cleaner as there
// is no need to repeat code.
$routes->group('api/v1', static function ($routes) {
  
    $routes->post('shorten_url', 'UrlController::create_shorten_url');
    $routes->get('get_single_url_details/(:any)', 'UrlController::getSingleUrlAnalytics/$1');
    $routes->put('revoke_url/(:any)', 'UrlController::revokeUrl/$1');
    $routes->get('get_all_urls/(:num)', 'UrlController::getAllUrls/$1');
    
});


