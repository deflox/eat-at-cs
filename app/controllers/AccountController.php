<?php

/**
 * Controller for handling account page.
 *
 * @author Leo Rudin
 */

namespace App\Controllers;

use App\Accessor;

class AccountController extends Accessor
{
    /**
     * Handles get request for displaying the account view.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getAccountPage($req, $res)
    {
        return $this->view->render($res, '/account.twig');
    }

    /**
     * Handles get request for displaying the settings view.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getSettingsPage($req, $res)
    {
        return $this->view->render($res, '/settings.twig');
    }
}