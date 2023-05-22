<?php

namespace Core;

use App\Auth;
use App\Flash;

// Base Cotroller
abstract class Controller
{
    // Parameters from the matched route
    protected $route_params = [];

    public function __construct($route_params)
    {
        $this->route_params = $route_params;
    }


    /* Magic method called when a non-existent or inaccessible method is
    called on an object of this class. Used to execute before and after
    filter methods on action methods. Action methods need to be named
    with an "Action" suffix, e.g. indexAction, showAction etc. */
    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller" . get_class($this));
        }
    }

    // Before filter - called before an action method.
    protected function before()
    {
    }

    // After filter - called after an action method.
    protected function after()
    {
    }

    // Redirect to a different page
    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit();
        
    }

    // Require the user to be logged in before giving access to the requested page.
    // Remember the requested page for later, ther redirect to the login page.
    public function requireLogin()
    {
        if (!Auth::getUser()) {  // Auth::isLoggedIn()

            Flash::addMessage('Please login to access that page', Flash::INFO);

            Auth::rememberRequestedPage();

            $this->redirect('/login');
        }
    }

    public function redirectToHome()
    {
        $this->redirect('/');
    }
}
