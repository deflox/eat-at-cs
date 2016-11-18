<?php

/**
 * Controller for handling authentication.
 *
 * @author Leo Rudin
 */

namespace App\Controllers;

use App\Accessor;
use App\Models\User;

class AuthController extends Accessor
{
    /**
     * Handles get request for displaying the sign in view.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getSignIn($req, $res)
    {
        return $this->view->render($res, '/signin.twig');
    }

    /**
     * Handles post request for doing sign in.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function postSignIn($req, $res)
    {
        $validation = $this->validator->validate($req, [
            'email|E-Mail' => ['required', 'email'],
            'password|Password' => ['required']
        ]);

        if ($validation->failed())
            return $res->withRedirect($this->router->pathFor('sign-in'));

        if (!$this->auth->attempt($req->getParam('email'), $req->getParam('password'), $req->getAttribute('ip_address'))) {
            $this->flash->addMessage('error', $this->auth->error());
            return $res->withRedirect($this->router->pathFor('sign-in'));
        }

        $this->flash->addMessage('success', 'Successfully signed in.');
        return $res->withRedirect($this->router->pathFor('account'));
    }

    /**
     * Handles get request for displaying the sign up view.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getSignUp($req, $res)
    {
        return $this->view->render($res, '/signup.twig');
    }

    /**
     * Handles post request for doing sign up.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function postSignUp($req, $res)
    {
        $validation = $this->validator->validate($req, [
            'username|Name' => ['required'],
            'email|E-Mail' => ['required', 'email'],
            'password|Password' => ['required', ['equals', 'password_repeat']],
            'password_repeat|Repeated Password' => ['required']
        ]);

        if ($validation->failed())
            return $res->withRedirect($this->router->pathFor('sign-up'));

        if (!$this->auth->addUser($req->getParams())) {
            $this->flash->addMessage('error', $this->auth->error());
            return $res->withRedirect($this->router->pathFor('sign-up'));
        }

        $this->mail->init([
            'receiver' => $req->getParam('email'),
            'subject' => 'Eat@CS - Please verify your e-mail address',
            'view' => $this->view->fetch('email/signup.email.twig', [
                'token' => $this->auth->generateToken($req->getParam('email'))
            ])
        ])->send();

        if (!$this->auth->attempt($req->getParam('email'), $req->getParam('password'), $req->getAttribute('ip_address'))) {
            $this->flash->addMessage('error', 'There was an unexpected error. Please try again later.');
            return $res->withRedirect($this->router->pathFor('sign-up'));
        }

        return $res->withRedirect($this->router->pathFor('account'));
    }

    /**
     * Handles get request for displaying the forgot view.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getForgotPassword($req, $res)
    {
        return $this->view->render($res, '/forgot.twig');
    }

    /**
     * Handles post request for doing reset password.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function postForgotPassword($req, $res)
    {
        $validation = $this->validator->validate($req, [
            'forgot_email|E-Mail' => ['required', 'email']
        ]);

        if ($validation->failed())
            return $res->withRedirect($this->router->pathFor('forgot'));

        $user = User::where('email', $req->getParam('forgot_email'))
            ->get()
            ->first();

        if (!isset($user)) {
            $this->flash->addMessage('error', 'We could not find any user with the entered e-mail address.');
            return $res->withRedirect($this->router->pathFor('forgot'));
        }

        $this->mail->init([
            'receiver' => $req->getParam('forgot_email'),
            'subject' => 'Eat@CS - Forgot your password',
            'view' => $this->view->fetch('email/forgot.email.twig', [
                'token' => $this->auth->generateToken($req->getParam('forgot_email'))
            ])
        ])->send();

        $this->flash->addMessage('success', 'We sent the email to the given e-mail address.');
        return $res->withRedirect($this->router->pathFor('sign-in'));
    }

    /**
     * Handles get request for displaying the reset view.
     *
     * @param  $req
     * @param  $res
     * @param  $args
     * @return \Slim\Views\Twig
     */
    public function getResetPassword($req, $res, $args)
    {
        if ($this->auth->verifyToken($args['token'])) {
            return $this->view->render($res, '/reset.twig', [
                'token' => $args['token']
            ]);
        } else {
            $this->flash->addMessage('error', $this->auth->error());
            return $res->withRedirect($this->router->pathFor('sign-in'));
        }
    }

    /**
     * Handles post request for doing reset password.
     *
     * @param  $req
     * @param  $res
     * @param  $args
     * @return \Slim\Views\Twig
     */
    public function postResetPassword($req, $res, $args)
    {
        $validation = $this->validator->validate($req, [
            'password|Password' => ['required', ['equals', 'password_repeat']],
            'password_repeat|Repeated Password' => ['required']
        ]);

        if ($validation->failed())
            return $res->withRedirect($this->router->pathFor('reset', ['token' => $args['token']]));

        if (!$this->auth->verifyResetPasswordToken($args['token'], $req->getParams())) {
            $this->flash->addMessage('error', $this->auth->error());
            return $res->withRedirect($this->router->pathFor('sign-in'));
        }

        $this->flash->addMessage('success', 'You changed successfully your password. You can now login.');
        return $res->withRedirect($this->router->pathFor('sign-in'));
    }

    /**
     * Handles get request for verifying e-mail address.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getVerifyEmail($req, $res, $args)
    {
        $forward = $this->auth->isUserAuthenticated() ? 'account' : 'sign-in';

        if (!$this->auth->verifyEmailVerificationToken($args['token'])) {
            $this->flash->addMessage('error', $this->auth->error());
            return $res->withRedirect($this->router->pathFor($forward));
        }

        $this->flash->addMessage('success', 'Your e-mail got successfully verified!');
        return $res->withRedirect($this->router->pathFor($forward));
    }

    /**
     * Handles get request for doing log out.
     *
     * @param  $req
     * @param  $res
     * @return \Slim\Views\Twig
     */
    public function getSignOut($req, $res)
    {
        $this->auth->logout();
        $this->flash->addMessage('success', 'Successfully signed out.');
        return $res->withRedirect($this->router->pathFor('sign-in'));
    }
}