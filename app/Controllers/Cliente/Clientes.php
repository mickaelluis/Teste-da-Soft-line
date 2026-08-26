<?php

namespace App\Controllers\Clientes;
use App\Controllers\BaseController;
use App\Models\ClientesModels\ClientesModels;


class Clientes extends BaseController{
    public function inserir_Clientes(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $nome = $dados['nome'] ?? null;
            $fantasia = $dados['fantasia'] ?? null;
            $documento = $dados['documento'] ?? null;
            $endereco  = [
                'cep'    => $dados['cep'] ?? null,
                'estado' => $dados['estado'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'rua'    => $dados['rua'] ?? null,
                'numero' => $dados['numero'] ?? null,
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

            if( !$this->documento_disponivel($id, $documento)){
                return $this->responder(409, "Esse documento já está cadastrado, tente outro!");
            }

            $ClienteModels = new ClientesModels();

            
            $cliente = $ClienteModels->criar_Cliente($id, $codigo, $nome, $fantasia, $documento, $endereco);
            if($cliente === false){
                return $this->responder(400, "Não e possivel criar cliente agora, tente mais tarde!"); 
            }
            return $this->responder(200, "Cliente criado com sucesso!!!" ); 
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao criar cliente, tente mais tarde!");
        }
    }

    private function documento_disponivel($id, $documento){
        $cliente = (new ClientesModels())->buscar_por_documento($id, $documento);
        return $cliente === false;   
    }
}