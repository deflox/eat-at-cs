<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_NOTICE);

/*
 * Config
 * ---------------------------------------
 */
$config = new \Noodlehaus\Config(__DIR__ . '/config.php');

/*
 * Slim App instance
 * ---------------------------------------
 */
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => $config->get('debug'),
        'addContentLengthHeader' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => $config->get('db.host'),
            'database' => $config->get('db.database'),
            'username' => $config->get('db.user'),
            'password' => $config->get('db.password'),
            'charset' => $config->get('db.charset'),
            'collation' => $config->get('db.collation'),
            'port' => $config->get('db.port'),
        ],
    ],
]);

$container = $app->getContainer();

/*
 * Database
 * ---------------------------------------
 */
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($c) use ($capsule) {
    return $capsule;
};

/*
 * Dependencies
 * ---------------------------------------
 */
$container['phpmailer'] = function ($c) {
    return new PHPMailer;
};
$container['flash'] = function($c) {
    return new \Slim\Flash\Messages;
};
$container['config'] = function($c) use($config) {
    return $config;
};

/*
 * Controllers
 * ---------------------------------------
 */
$container['AccountController'] = function($c) {
    return new App\Controllers\AccountController($c);
};
$container['AuthController'] = function($c) {
    return new App\Controllers\AuthController($c);
};

/*
 * Libraries
 * ---------------------------------------
 */
$container['auth'] = function($c) {
    return new App\Libraries\Auth($c);
};
$container['validator'] = function($c) {
    return new App\Libraries\Validation($c);
};
$container['mail'] = function($c) {
    return new App\Libraries\Mail($c);
};

/*
 * Middleware
 * ---------------------------------------
 */
$app->add(new RKA\Middleware\IpAddress());
$app->add(new App\Middleware\ValidationErrorMiddleware($container));
$app->add(new App\Middleware\PersistInputMiddleware($container));

/*
 * Twig views
 * ---------------------------------------
 */
$container['view'] = function($c) {

    $view = new \Slim\Views\Twig(__DIR__ . '/../views');

    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->router,
        $c->config->get('url')
    ));

    $view->getEnvironment()->addGlobal('flash', $c->flash);

    return $view;

};

/*
 * Routes
 * ---------------------------------------
 */
require __DIR__ . '/../app/routes.php';
