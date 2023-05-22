<?php

namespace App\Controllers;

use Core\View;

class Items extends Authenticated
{
    // Items index
    public function indexAction()
    {
        View::renderTemplate('Items/index.html');
    }

    // Add a new item
    public function newAction()
    {
        echo "new action";
    }

    // Show an item
    public function showAction()
    {
        echo "show action";
    }
}
