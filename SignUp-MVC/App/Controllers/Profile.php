<?php
namespace App\Controllers;

use App\Auth;
use App\Controllers\Authenticated;
use App\Flash;
use Core\View;

class Profile extends Authenticated  
{
    // Before filter - called before each action method
    public function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }

    // Show the profile
    public function showAction()
    {
        View::renderTemplate('Profile/show.html',[
            'user' => $this->user
        ]);
    }

    // Show the form for editing the profile
    public function editAction()
    {
        View::renderTemplate('Profile/edit.html',[
            'user' => $this->user
        ]);
    }

    // Update the profile
    public function updateAction()
    {

        if ($this->user->updateProfile($_POST)) {
            
            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');

        } else {
            View::renderTemplate('Profile/edit.html', [
                'user' => $this->user
            ]);
        }

    }

    // Delete the profile
    public function deleteAction()
    {
        View::renderTemplate('Profile/delete.html',[
                    'user' => $this->user
        ]);
    }

    public function executeAction()
    {
        if ($this->user->deleteProfile($_POST)) {
            Flash::addMessage('Profile deleted successfully');
            $this->redirect('/');
            Auth::logout();
        } else {
            View::renderTemplate('Profile/delete.html',[
                'user' => $this->user
            ]);
        }
    }
}