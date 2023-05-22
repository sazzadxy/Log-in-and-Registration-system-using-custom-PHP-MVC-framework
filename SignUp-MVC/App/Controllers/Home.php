<?php

namespace App\Controllers;

use App\Auth;
use \Core\View;

class Home extends \Core\Controller
{
    // Show the index page
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }

    // Before filter
    protected function before()
    {
        // echo "(before) ";
        // return false;
    }

    // After filter
    protected function after()
    {
        // echo " (after)";
    }
}
