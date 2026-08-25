<?php 

namespace App\Controllers;

abstract class BaseController
{
    protected function responder($codigo, $mensagem, $dados = null)
    {
        $status = $codigo < 400 ? "success" : "error";
        header('Content-Type: application/json');
        http_response_code($codigo);
        echo json_encode(["status" => $status, "message" => $mensagem, "data" => $dados]);
        return;
    }

    protected function view(string $caminho)
    {
        $arquivo = dirname(__DIR__) . '/views/' . ltrim($caminho, '/');
        if (!is_file($arquivo)) {
            http_response_code(404);
            require dirname(__DIR__) . '/views/erro/404.html';
            return;
        }
        header('Content-Type: text/html; charset=UTF-8');
        readfile($arquivo);
    }
}
