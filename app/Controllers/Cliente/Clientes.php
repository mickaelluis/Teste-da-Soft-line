<?php

namespace App\Controllers\Clientes;

class Clientes{
    public function inserir_Clientes(){
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $_SESSION['id_usuario'] ?? null;
        $nome = $dados['nome'] ?? null;
        $codigo = $dados['codigo'] ?? null;
        $fantasia = $dados['fantasia'] ?? null;
        $documento = $dados['documento'] ?? null;
        $endereco  = [
            $dados['cep'] ?? null,
            $dados['estado'] ?? null,
            $dados['cidade'] ?? null,
            $dados['bairro'] ?? null,
            $dados['rua'] ?? null,
            $dados['numero'] ?? null,
        ];

        $obrigatorios = ['nome', 'codigo', 'fantasia', 'documento', 'cep', 'estado', 'cidade', 'bairro', 'rua', 'numero'];
        foreach ($obrigatorios as $campo) {
        $valor = $dados[$campo] ?? null;
        if ($valor === null || trim((string)$valor) === '') {
            return $this->responder(400, "Campo obrigatório: {$campo}");
            }
        }
        
        if( strlen($nome) > 60){
            return $this->responder(400, "Campo nome deve ter menos que 60 caracteres");
        }

        if( strlen($fantasia) > 100){
            return $this->responder(400, "Campo fantasia deve ter menos que 100 caracteres");
        }

        if( strlen($documento) > 14){
            return $this->responder(400, "Campo documento tem que ser um cpf ou cnpj valido");
        }

        

    }
}