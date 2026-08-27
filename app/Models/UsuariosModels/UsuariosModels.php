<?php

namespace App\Models\UsuariosModels;

use App\Models\Database;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;

class UsuariosModels{
    public function buscar_Usuario($id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_usuario(?)");
        try {
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            $stmt->closeCursor();
            if ($usuario === false) {
                throw new RegistroNaoEncontrado('usuario');
            }
            return $usuario;
        } catch (RegistroNaoEncontrado $e) {
            throw $e;
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
    }

    public function atualizar_Usuario($id, $email, $nome, $senhaHash){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL atualizar_usuario(?, ?, ?, ?)");
        try {
            $this->validar_usuario($id);
            $stmt->execute([$id, $email, $nome, $senhaHash]);
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

    public function deletar_Usuario($id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL deletar_usuario(?)");
        try {
            $this->validar_usuario($id);
            $stmt->execute([$id]);
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

    private function validar_usuario($id){
        $conexao = Database::conectar();
        $stmt = $conexao->prepare("CALL select_usuario(?)");
        try {
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
        } catch (\PDOException $e) {
            $stmt->closeCursor();
            error_log($e->getMessage());
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
        if ($usuario === false) {
            throw new RegistroNaoEncontrado('usuario');
        }
    }
}
