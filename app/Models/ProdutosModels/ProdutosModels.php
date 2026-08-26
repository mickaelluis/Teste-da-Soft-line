<?php

namespace App\Models\ProdutosModels;

use App\Models\Database;

class ProdutosModels{
    public function criar_Produto($id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido){
        $conexao = Database::conectar();   
        $stmt = $conexao->prepare("CALL inserir_produto(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido]);
        return true;
    }
}
