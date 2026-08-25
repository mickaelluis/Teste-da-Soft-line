<?php

namespace App\Controllers\Usuario;

use App\Controllers\BaseController;

class UsuarioPages extends BaseController
{
    public function index()
    {
        $this->view('usuario/index.html');
    }
}
