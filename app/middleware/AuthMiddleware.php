<?php

/**
 * Middleware for checking if user is authenticated.
 *
 * @author Leo Rudin
 */

namespace App\Middleware;

use App\Accessor;

class AuthMiddleware extends Accessor
{
    public function __invoke($req, $res, $next)
    {
        if (!$this->auth->isUserAuthenticated()) {
            $this->flash->addMessage('error', 'Please first sign in to access the configuration panel.');
            return $res->withRedirect($this->router->pathFor('sign-in'));
        }

        return $next($req, $res);
    }
}