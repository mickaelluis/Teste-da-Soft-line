<?php

namespace App\Controllers\AuthController;

use App\Controllers\BaseController;

class AuthPages extends BaseController
{
    public function login()
    {
        $this->view('login/login.html');
    }

    public function cadastro()
    {
        $this->view('login/cadastro.html');
    }
}
