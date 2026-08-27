<?php

namespace App\Models\AuthModels;
use App\Models\Database;
use App\Exceptions\ValorDuplicado;

class RegisterModels {
    public function criar_registro($name, $email, $senhaHash){
        $conexao = Database::conectar();     
        $stmt = $conexao->prepare("CALL inserir_usuario(?, ?, ?)");
        try {
            $stmt->execute([$email, $name, $senhaHash]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            if($e->getCode() === "23000"){
                throw ValorDuplicado::fromPdo($e);
            }
            throw new \RuntimeException;
        }
        $stmt->closeCursor();
    }
}
