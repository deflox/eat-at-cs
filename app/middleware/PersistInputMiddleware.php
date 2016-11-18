<?php

/**
 * Middleware for persisting input data in form in case of errors.
 *
 * @author Leo Rudin
 */

namespace App\Middleware;

use App\Accessor;

class PersistInputMiddleware extends Accessor
{
    public function __invoke($req, $res, $next)
    {
        $this->container->view->getEnvironment()->addGlobal('old', $_SESSION['old']);
        $_SESSION['old'] = $req->getParams();

        return $next($req, $res);
    }
}