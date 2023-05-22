<?php

namespace App\Controllers;

use App\Auth;
use Core\View;
use App\Models\User;

class Signup extends \Core\Controller 
{
    // Show the signup page
    public function newAction()
    {
        if (Auth::getUser()) {
            $this->redirectToHome();
        } else {
            View::renderTemplate('Signup/new.html');
        } 
    }

    // Sign up a new user
    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
            ob_start(); // Only for PhpMailer otherwise no need for Mailgun

            $user->sendActivationEmail();

            $this->redirect('/signup/success');
            
            ob_clean(); // Only for PhpMailer otherwise no need for Mailgun

        } else {
            // var_dump($user->errors);
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }
    }

    // Show the signup success page
    public function successAction()
    {
        if (Auth::getUser()) {
            $this->redirectToHome();
        } else {
            View::renderTemplate('Signup/success.html');
        }
    }

    // Activate a new account
    public function activateAction()
    {
        $user = User::findByActivationToken($this->route_params['token']) ?? null;
        
        if ($user) {
            User::activate($this->route_params['token']);
            $this->redirect('/signup/activated');
        } else {
            View::renderTemplate('Signup/already_activated.html');
            exit;
        }

    }

    // Show the activation success page
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}
