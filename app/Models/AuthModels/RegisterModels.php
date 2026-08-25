<?php

namespace App\Models\AuthModels;
use App\Models\Database;
class RegisterModels {
    public function criar_registro($name, $email, $senhaHash){
        try {
            $conexao = Database::conectar();     
            $stmt = $conexao->prepare("CALL inserir_usuario(?, ?, ?)");
            $stmt->execute([$email, $name, $senhaHash]);
            return true;
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}