<?php

/**
 * This library provides functions for authentication and
 * authorization purposes.
 *
 * @author Leo Rudin
 */

namespace App\Libraries;

use App\Accessor;
use App\Models\Token;
use App\Models\User;
use App\Models\Attempt;

class Auth extends Accessor
{
    /**
     * Contains string with the error.
     *
     * @var string
     */
    private $error = null;

    /**
     * Defines how many attempts a user
     * can make until he gets locked
     * out of the application.
     *
     * @var integer
     */
    private $attempts = 3;

    /**
     * Defines how long the user gets
     * locked out of the application
     * in minutes in case he made
     * more attempts than possible.
     *
     * @var integer
     */
    private $lockTime = 5;

    /**
     * Makes an attempt to authenticate a user
     * to the application.
     *
     * @param  $email
     * @param  $password
     * @param  $ip
     * @return bool
     */
    public function attempt($email, $password, $ip)
    {
        if ($this->checkAttempt($ip)) {

            $user = User::where('email', $email)
                ->get()
                ->first();

            // Check if user exists
            if (!isset($user)) {
                $this->error = "We couldn't verify your credentials. Please check them and try again.";
                return false;
            }

            // Check password
            if (!password_verify($password, $user->password)) {
                $this->error = "We couldn't verify your credentials. Please check them and try again.";
                return false;
            }

            // Set session
            $_SESSION['user'] = $user->id;

            // Reset attempt if user logged in correctly
            $this->resetAttempt($ip);

            return true;

        } else {

            $this->error = "You had too many failed login attempts. Please try again in ".$this->lockTime." minutes.";
            return false;

        }

    }

    /**
     * Checks if the users attempt is valid.
     *
     * @param  $ip
     * @return bool
     */
    private function checkAttempt($ip)
    {
        $attempt = Attempt::where('ip_address', md5($ip))
            ->get()
            ->first();

        if (!isset($attempt)) {

            $attempt = new Attempt();
            $attempt->ip_address = md5($ip);
            $attempt->count = 1;
            $attempt->save();
            return true;

        } else {

            if ($attempt->count >= $this->attempts) {

                if (!isset($attempt->lock_time)) {

                    Attempt::where('id', $attempt->id)
                        ->update([
                            'lock_time' => date('Y-m-d H:i:s')
                        ]);

                    return false;

                } else if (!$this->checkIfOlder($attempt->lock_time, $this->lockTime)) {

                    return false;

                } else {

                    Attempt::where('id', $attempt->id)
                        ->update([
                            'count' => 1,
                            'lock_time' => null
                        ]);

                    return true;

                }

            } else {
                Attempt::where('id', $attempt->id)
                    ->update([
                        'count' => $attempt->count + 1
                    ]);
                return true;
            }

        }

    }

    /**
     * Adds a new user to the database.
     *
     * @param  data
     * @return boolean
     */
    public function addUser($data)
    {
        $user = User::where('email', $data['email'])
            ->get()
            ->first();

        if (isset($user)) {
            $this->error = "There is already an user with the entered e-mail address. Please choose another one.";
            return false;
        }

        $user = new User();

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->locked = 0;
        $user->verified = 0;

        if (!$user->save()) {
            $this->error = "There was an unexpected error. Please try again later.";
            return false;
        }

        return true;
    }

    /**
     * Generates token and inserts it into the database.
     *
     * @param  $email
     * @return string
     */
    public function generateToken($email)
    {
        $user = User::where('email', $email)
            ->get()
            ->first();

        $tokenValue = md5($email.time());

        $token = new Token();
        $token->value = $tokenValue;
        $token->user_id = $user->id;
        $token->save();

        return $tokenValue;
    }

    /**
     * Verifies if a token for email verification is valid.
     *
     * @param  $tokenValue
     * @return boolean
     */
    public function verifyEmailVerificationToken($tokenValue)
    {
        $token = Token::where('value', $tokenValue)
            ->get()
            ->first();

        if (!isset($token)) {
            $this->error = "We couldn't verify this token. It is either not valid or expired.";
            return false;
        }

        User::where('id', $token->user_id)
            ->update([
                'verified' => 1
            ]);

        Token::destroy($token->id);

        return true;
    }

    /**
     * Verifies if a token for password reset is valid.
     *
     * @param  $tokenValue
     * @param  $data
     * @return boolean
     */
    public function verifyResetPasswordToken($tokenValue, $data)
    {
        $token = Token::where('value', $tokenValue)
            ->get()
            ->first();

        if (!isset($token)) {
            $this->error = "We couldn't verify this token. It is either not valid or expired.";
            return false;
        }

        User::where('id', $token->user_id)
            ->update([
                'password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

        Token::destroy($token->id);

        return true;
    }

    /**
     * Verifies if a token is valid.
     *
     * @param  $tokenValue
     * @return boolean
     */
    public function verifyToken($tokenValue)
    {
        $token = Token::where('value', $tokenValue)
            ->get()
            ->first();

        if (!isset($token)) {
            $this->error = "We couldn't verify this token. It is either not valid or expired.";
            return false;
        }

        return true;
    }

    /**
     * Logs a user out of the application.
     */
    public function logout()
    {
        unset($_SESSION['user']);
    }

    /**
     * Checks if the user is logged in.
     *
     * @return boolean
     */
    public function isUserAuthenticated()
    {
        return isset($_SESSION['user']);
    }

    /**
     * Resets an attempt entry based on the users
     * ip address.
     *
     * @param $ip
     */
    private function resetAttempt($ip)
    {
        Attempt::where('ip_address', md5($ip))
            ->update([
                'count' => 0,
                'lock_time' => null
            ]);
    }

    /**
     * Returns error variable.
     *
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Returns true if given timestamp is older than the given
     * check amount in minutes.
     *
     * @param  $timestamp
     * @param  $check
     * @return boolean
     */
    private function checkIfOlder($timestamp, $check)
    {
        return (strtotime($timestamp) <= strtotime('-'.$check.' minutes'));
    }
}