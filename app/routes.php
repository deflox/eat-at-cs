<?php

/**
 * Routes for the application.
 *
 * @author Leo Rudin
 */

/*
 * Auth pages
 * ---------------------------------------
 */
$app->group('', function() {
    $this->get('/', 'AuthController:getSignIn')->setName('sign-in');
    $this->post('/', 'AuthController:postSignIn');

    $this->get('/signup', 'AuthController:getSignUp')->setName('sign-up');
    $this->post('/signup', 'AuthController:postSignUp');

    $this->get('/verify/{token}', 'AuthController:getVerifyEmail')->setName('verify');

    $this->get('/forgot', 'AuthController:getForgotPassword')->setName('forgot');
    $this->post('/forgot', 'AuthController:postForgotPassword');

    $this->get('/reset/{token}', 'AuthController:getResetPassword')->setName('reset');
    $this->post('/reset/{token}', 'AuthController:postResetPassword');
})->add(new App\Middleware\RedirectMiddleware($container));

$app->get('/signout', 'AuthController:getSignOut')->setName('sign-out');

/*
 * Account pages
 * ---------------------------------------
 */
$app->group('', function() {
    $this->get('/account', 'AccountController:getAccountPage')->setName('account');
    $this->get('/settings', 'AccountController:getSettingsPage')->setName('settings');
})->add(new App\Middleware\AuthMiddleware($container));