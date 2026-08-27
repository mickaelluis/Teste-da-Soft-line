<?php

namespace App\Models\ClientesModels;

use App\Models\Database;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;

class ClientesModels{
    public function listar_Clientes($id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_clientes(?)");
        try {
            $stmt->execute([$id]);
            $clientes = $stmt->fetchAll();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        return $clientes;
    }

    public function buscar_Cliente($id_cliente, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_cliente(?, ?)");
        $stmtEndereco = $conexao->prepare("CALL select_enderecos(?)");
        try {
            $stmt->execute([$id_cliente, $id]);
            $cliente = $stmt->fetch();
            $stmt->closeCursor();
            if ($cliente === false) {
                throw new RegistroNaoEncontrado('cliente');
            }
            $stmtEndereco->execute([$cliente['id']]);
            $cliente['endereco'] = $stmtEndereco->fetchAll();
            $stmtEndereco->closeCursor();
            return $cliente;
        } catch (RegistroNaoEncontrado $e) {
            throw $e;
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            $stmtEndereco->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
    }

    public function criar_Cliente($id, $codigo, $nome, $fantasia, $documento, $endereco){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL inserir_clientes(?, ?, ?, ?, ?)");
        $stmtEndereco = $conexao->prepare("CALL inserir_endereco(?, ?, ?, ?, ?, ?, ?)");
        try {
            $conexao->beginTransaction();
            $stmt->execute([$id, $codigo, $nome, $fantasia, $documento]);
            $resultado = $stmt->fetch();
            $id_cliente = $resultado['id'];
            $stmt->closeCursor();
            $stmtEndereco->execute([
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
        } catch (\PDOException $e) {
            $conexao->rollBack();
            $stmt->closeCursor();
            $stmtEndereco->closeCursor();
            error_log($e->getMessage());
            if($e->getCode() === "23000"){
                throw ValorDuplicado::fromPdo($e);
            }
            throw new \RuntimeException;
        } catch (\Throwable $e) {
            $conexao->rollBack();
            $stmt->closeCursor();
            $stmtEndereco->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
    }

    public function atualizar_Cliente($id_cliente, $id, $codigo, $nome, $fantasia, $documento, $endereco = null){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL atualizar_clientes(?, ?, ?, ?, ?, ?)");
        $stmtEndereco = $conexao->prepare("CALL atualizar_endereco(?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $this->validar_cliente($id_cliente, $id);
            $conexao->beginTransaction();
            $stmt->execute([$id_cliente, $id, $codigo, $nome, $fantasia, $documento]);
            $stmt->closeCursor();
            if ($endereco !== null) {
                $this->validar_endereco_cliente($endereco['id'], $id_cliente);
                $stmtEndereco->execute([
                    $endereco['id'],
                    $id_cliente,
                    $endereco['cep'],
                    $endereco['estado'],
                    $endereco['cidade'],
                    $endereco['bairro'],
                    $endereco['rua'],
                    $endereco['numero']
                ]);
                $stmtEndereco->closeCursor();
            }
            $conexao->commit();
            return true;
        } catch (RegistroNaoEncontrado $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            throw $e;
        } catch (\PDOException $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $stmt->closeCursor();
            $stmtEndereco->closeCursor();
            error_log($e->getMessage());
            if($e->getCode() === "23000"){
                throw ValorDuplicado::fromPdo($e);
            }
            throw new \RuntimeException;
        } catch (\Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $stmt->closeCursor();
            $stmtEndereco->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
    }

    private function validar_cliente($id_cliente, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_cliente(?, ?)");
        try {
            $stmt->execute([$id_cliente, $id]);
            $cliente = $stmt->fetch();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        if ($cliente === false) {
            throw new RegistroNaoEncontrado('cliente');
        }
    }

    private function validar_endereco_cliente($id_endereco, $id_cliente){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_endereco(?, ?)");
        try {
            $stmt->execute([$id_endereco, $id_cliente]);
            $endereco = $stmt->fetch();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        if ($endereco === false) {
            throw new RegistroNaoEncontrado('endereco');
        }
    }

    public function deletar_Cliente($id_cliente, $id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL deletar_clientes(?, ?)");
        try {
            $this->validar_cliente($id_cliente, $id);
            $stmt->execute([$id, $id_cliente]);
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
}
