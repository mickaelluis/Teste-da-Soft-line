<?php

namespace App\Models\AuthModels;
use App\Models\Database;
class LoginModels {
    public function buscar_por_email($email){
        $conexao = Database::conectar();     
        $stmt = $conexao->prepare("CALL login_usuario(?)");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        $stmt->closeCursor();
        return $usuario;
    }
}