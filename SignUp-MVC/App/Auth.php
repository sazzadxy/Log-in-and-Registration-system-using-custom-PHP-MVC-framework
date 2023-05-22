<?php

namespace App;

use App\Models\RememberedLogin;
use App\Models\User;

// Authentication
class Auth  
{
    // Log in the user
    public static function login($user, $remember_me)
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;

        if ($remember_me) {

            if ($user->rememberLogin()) {
                //var_dump($user->remember_token,$user->expiry_timestamp);
                setcookie('remember_me', $user->remember_token, $user->expiryTimestamp, '/');
            }
        }
    }

    // Log out a user
    public static function logout()
    {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        static::forgetLogin();

    }

    // Return indicator of whether a user is logged in or not
    // public static function isLoggedIn()
    // {
    //     return isset($_SESSION['user_id']);
    // }

    // Remember the originally-requested page in the session
    public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    // Get the originally-requested page to return to after requiring login, or default to the homepage
    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/';
    }

    // Get the current logged-in user, from the session or the remember-me cookie
    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) {

            return User::findByID($_SESSION['user_id']);

        } else {

            return static::loginFromRememberCookie();
        }
    }

    // Login the user from a remembered login cookie
    protected static function loginFromRememberCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {

            $remembered_login = RememberedLogin::findByToken($cookie);

            if ($remembered_login && ! $remembered_login->hasExpired()) {
                
                $user = $remembered_login->getUser();

                static::login($user, false);

                return $user;
            }
        }
    }

    // Forget the rememberd login, if present
    public static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $remember_login = RememberedLogin::findByToken($cookie);

            if ($remember_login) {
                $remember_login->delete();
            }

            setcookie('remember_me', '', time() - 3600);
        }
    }
}
