<?php

/**
 * Middleware for redirecting user in case he is logged in.
 *
 * @author Leo Rudin
 */

namespace App\Middleware;

use App\Accessor;

class RedirectMiddleware extends Accessor
{
    public function __invoke($req, $res, $next)
    {
        if ($this->auth->isUserAuthenticated())
            return $res->withRedirect($this->router->pathFor('account'));

        return $next($req, $res);
    }
}