<?php 

namespace App\Controllers;

use App\Auth;
use App\Models\User;
use Core\Controller;
use Core\View;

class Password extends Controller 
{
    // Show the forgotten password page
    public function forgotAction()
    {
        if (Auth::getUser()) {
            $this->redirectToHome();
        } else {
            View::renderTemplate('Password/forgot.html');
        }
    }

    // Send the password reset link to the supplied email
    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);
        View::renderTemplate('Password/reset_requested.html');
    }

    // Show the reset password form
    public function resetAction()
    {
        if (Auth::getUser()) {
            $this->redirectToHome();
        } else {
            $token = $this->route_params['token']; // Get the token from URL
            // echo $token;

            //$user = $this->getUserOrExit($token);
            //var_dump($user);

            View::renderTemplate('Password/reset.html', [
                'token' => $token
            ]);
        }
    }

    // Reset the user's passowrd
    public function resetPasswordAction()
    {
        $token = $_POST['token'];

        $user = $this->getUserOrExit($token);
        
        if ($user->resetPassword($_POST['password'])) {

            View::renderTemplate('Password/reset_success.html');

        } else {

            View::renderTemplate('Password/reset.html', [
                'token' => $token,
                'user' => $user
            ]);
        }
    }

    // Find the user model associated with the password reset form, or end the request with message
    public function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        } else {
            View::renderTemplate('Password/token_expired.html');
            exit;
        }  
    }

}
