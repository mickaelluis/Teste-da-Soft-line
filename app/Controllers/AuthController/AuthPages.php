<?php

namespace App\Controllers\AuthController;

use App\Controllers\BaseController;

class AuthPages extends BaseController
{
    public function login()
    {
        if (isset($_SESSION['id_usuario'])) {
            $_SESSION = [];
            session_destroy();
        }
        $this->view('login/login.html');
    }

    public function cadastro()
    {
        $this->view('login/cadastro.html');
    }
}
