<?php

namespace App\Models\ClientesModels;

use App\Models\Database;

class ClientesModels{
    public function buscar_por_documento($id, $documento){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_cliente_documento(?, ?)");
        $stmt->execute([$id, $documento]);
        $cliente = $stmt->fetch();
        $stmt->closeCursor();
        return $cliente;
    }

    public function criar_Cliente($id, $codigo, $nome, $fantasia, $documento, $endereco){
        $conexao = Database::conectar();   
        try {
            $conexao->beginTransaction();  
            $stmt = $conexao->prepare("CALL inserir_clientes(?, ?, ?, ?, ?)");
            $stmt->execute([$id, $codigo, $nome, $fantasia, $documento]);
            $resultado = $stmt->fetch();
            $id_cliente = $resultado['id'];
            $stmt->closeCursor();
            $stmt = $conexao->prepare("CALL inserir_endereco(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_cliente,
                $endereco['cep'],
                $endereco['estado'],
                $endereco['cidade'],
                $endereco['bairro'],
                $endereco['rua'],
                $endereco['numero']
            ]);
            $conexao->commit();
            return true;
        } catch (\Throwable $e) {
            $conexao->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}