<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;

class ClientePages extends BaseController
{
    public function lista()
    {
        $this->view('cliente/lista.html');
    }

    public function cadastro()
    {
        $this->view('cliente/cadastro.html');
    }

    public function visualizar()
    {
        $this->view('cliente/visualizar.html');
    }
}
