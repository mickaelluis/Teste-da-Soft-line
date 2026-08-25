<?php

namespace App\Controllers\Produto;

use App\Controllers\BaseController;

class ProdutoPages extends BaseController
{
    public function lista()
    {
        $this->view('produto/lista.html');
    }

    public function cadastro()
    {
        $this->view('produto/cadastro.html');
    }

    public function visualizar()
    {
        $this->view('produto/visualizar.html');
    }
}
