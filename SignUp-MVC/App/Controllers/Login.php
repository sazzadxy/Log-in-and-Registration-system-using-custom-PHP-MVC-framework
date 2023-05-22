<?php

namespace App\Controllers;

use App\Auth;
use App\Flash;
use \Core\View;
use \App\Models\User;

class Login extends \Core\Controller 
{
    // Show the login page
    public function newAction()
    {
        if (Auth::getUser()) {
            $this->redirectToHome();
        } else {
            View::renderTemplate('Login/new.html');
        }
    }

    // Log in a user
    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        // var_dump($_POST);

        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            
            Auth::login($user, $remember_me);

            Flash::addMessage('Login successful');

            $this->redirect(Auth::getReturnToPage());

        } else {

            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING);

            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me
            ]);
        }
    }

    // Log out a user
    public function destroyAction()
    {
        Auth::logout();

        $this->redirect('/login/show-logout-message');
    }

    /* Show a "logged out" flash message and redirect to the homepage. Necessary to use the flash messages
       as they use the session and at the end of the logout method (destroyAction) the session is destroyed
       so a new action needs to be called in order to use the session.
    */
    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful');

        $this->redirect('/');
    }

}
