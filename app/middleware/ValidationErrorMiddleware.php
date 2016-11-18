<?php

/**
 * Middleware for make the validation errors globally
 * available.
 *
 * @author Leo Rudin
 */

namespace App\Middleware;

use App\Accessor;

class ValidationErrorMiddleware extends Accessor
{
    public function __invoke($req, $res, $next)
    {
        if (isset($_SESSION['errors']))
            $this->container->view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);

        unset($_SESSION['errors']);

        $response = $next($req, $res);
        return $response;
    }
}