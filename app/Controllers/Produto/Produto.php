<?php

namespace App\Controllers\Produto;
use App\Controllers\BaseController;
use App\Models\ProdutosModels\ProdutosModels;


class Produto extends BaseController{
    public function inserir_Produto(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $nome = $dados['nome'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $descricao = $dados['descricao'] ?? null;
            $codigo_de_barras = $dados['codigo_de_barras'] ?? null;
            $valor = $dados['valor'] ?? null;
            $peso_bruto = $dados['peso_bruto'] ?? null;
            $peso_liquido = $dados['peso_liquido'] ?? null;

            $obrigatorios = ['nome', 'codigo', 'descricao', 'codigo_de_barras', 'valor', 'peso_bruto', 'peso_liquido'];
            foreach ($obrigatorios as $campo) {
            $valor = $dados[$campo] ?? null;
            if ($valor === null || trim((string)$valor) === '') {
                return $this->responder(400, "Campo obrigatório: {$campo}");
                }
            }
            $valor = $dados['valor'] ?? null;

            if( strlen($nome) > 100){
                return $this->responder(400, "Campo nome deve ter menos que 100 caracteres");
            }

            if( strlen($descricao) > 60){
                return $this->responder(400, "Campo descrição deve ter menos que 60 caracteres");
            }

            if( strlen($codigo_de_barras) > 14){
                return $this->responder(400, "Campo código de barras deve ter no maximo 14 caracteres");
            }

            $AuthModel = new ProdutosModels();
            $produto = $AuthModel->criar_Produto($id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido);
            if($produto === false){
                return $this->responder(400, "Não e possivel criar produto agora, tente mais tarde!"); 
            }
            return $this->responder(200, "Produto criado com sucesso!!!" ); 
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao criar produto, tente mais tarde!");
        }
    }
}
