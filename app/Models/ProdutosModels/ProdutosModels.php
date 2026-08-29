<?php

namespace App\Models\ProdutosModels;

use App\Models\Database;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;

class ProdutosModels{
    public function listar_Produtos($id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_produtos(?)");
        try {
            $stmt->execute([$id]);
            $produtos = $stmt->fetchAll();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        return array_map(fn(array $produto) => [
            'id' => $produto['id'] ?? null,
            'nome' => $produto['nome'] ?? null,
            'codigo' => $produto['codigo'] ?? null,
            'valor' => $produto['valor'] ?? null,
            'codigo_de_barras' => $produto['codigo_de_barras'] ?? null,
        ], $produtos);
    }

    public function buscar_Produto($id_produto, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_produto(?, ?)");
        try {
            $stmt->execute([$id_produto, $id]);
            $produto = $stmt->fetch();
            $stmt->closeCursor();
            if ($produto === false) {
                throw new RegistroNaoEncontrado('produto');
            }
            return $produto;
        } catch (RegistroNaoEncontrado $e) {
            throw $e;
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
    }

    public function criar_Produto($id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido){
        $conexao = Database::conectar();   
        $stmt = $conexao->prepare("CALL inserir_produto(?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido]);
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            if($e->getCode() === "23000"){
                throw ValorDuplicado::fromPdo($e);
            }
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
    }

    public function atualizar_Produto($id_produto, $id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL atualizar_produto(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $this->validar_produto($id_produto, $id);
            $stmt->execute([$id_produto, $id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido]);
        } catch (RegistroNaoEncontrado $e) {
            throw $e;
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            if($e->getCode() === "23000"){
                throw ValorDuplicado::fromPdo($e);
            }
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        return true;
    }

    public function deletar_Produto($id_produto, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL deletar_produto(?, ?)");
        try {
            $this->validar_produto($id_produto, $id);
            $stmt->execute([$id, $id_produto]);
        } catch (RegistroNaoEncontrado $e) {
            throw $e;
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        return true;
    }

    private function validar_produto($id_produto, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_produto(?, ?)");
        try {
            $stmt->execute([$id_produto, $id]);
            $produto = $stmt->fetch();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        if ($produto === false) {
            throw new RegistroNaoEncontrado('produto');
        }
    }
}
