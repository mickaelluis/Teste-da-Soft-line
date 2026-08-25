<?php

namespace App\Controllers\Home;

use App\Controllers\BaseController;

class HomePages extends BaseController
{
    public function index()
    {
        $this->view('home/index.html');
    }
}
