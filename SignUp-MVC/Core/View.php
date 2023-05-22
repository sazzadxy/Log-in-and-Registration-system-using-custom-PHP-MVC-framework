<?php

namespace Core;

use App\Auth;
use App\Flash;
use Twig\TwigFunction;

class View  
{
    // Render a view file
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);
        $file = "../App/Views/$view"; // relative to Core directory
        if (is_readable($file)) {
            require $file;
        } else {
            // echo "$file not found";
            throw new \Exception("$file not found");
            
        }
    }

    // Render a view file using twing
    public static function renderTemplate($template, $args = [])
    {
        echo static::getTemplate($template, $args);
    }

    // Get the contents of a view template using Twig
    public static function getTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader($_SERVER['DOCUMENT_ROOT'] .'/SignUp-MVC/App/Views');
            $twig = new \Twig\Environment($loader, ['cache' => false]); // new \Twig\Environment($loader);

            $twig->addGlobal('current_user', Auth::getUser());
            $twig->addGlobal('flash_messages', Flash::getMessages());

            $twig->addFunction( 
                new TwigFunction('activeLink', function ($currentPage)
                {
                    $url = $_SERVER['REQUEST_URI'];
                    if ($currentPage == $url) {
                        echo 'active';
                    }
                }));

            // $function = new TwigFunction('activeLink', function ($param1, $param2 = null) {
            //     return isset($param2) ? $param1 * $param2 : $param1;
            // });

            
            // $twig->addGlobal('is_logged_in', \App\Auth::isLoggedIn());

            // $twig = new \Twig\Environment($loader, [
            // 'cache' => $_SERVER['DOCUMENT_ROOT'] .'/SignUp-MVC/App/Views/Cache',
            // ]);

            // $twig->addGlobal('session', $_SESSION);
            // for session in Views/Home/index.html
            //  {% if session.user_id  %}
                //   User with ID {{ session.user_id }} is logged in. <a href="/logout">Log out</a> 
        
        }
        return $twig->render($template, $args);
    }
}
