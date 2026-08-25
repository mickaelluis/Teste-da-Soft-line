<?php 
 
 namespace App\Controllers\AuthController;

 use App\Controllers\BaseController;

 use App\Models\AuthModels\RegisterModels;

class Register extends BaseController {

    public function cadastrar() {
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $name = $dados['name'] ?? null;
            $email = $dados['email'] ?? null;
            $senha = $dados['senha'] ?? null;
            $confirm_senha = $dados['confirm_senha'] ?? null;

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                return $this->responder(400, "Email invalido, por favor tentar outro"); 
            }
            if($senha !== $confirm_senha){
                return $this->responder(400, "As duas senha tem que ser iguais");
            }
            $senhaHash    = password_hash($senha, PASSWORD_BCRYPT);
            $usuarioModel = new RegisterModels();
            $resultado    = $usuarioModel->criar_registro($name, $email, $senhaHash);
            if($resultado === false){
                return $this->responder(409, "Este e-mail não pode ser registrado, tente outro.");
            }
            return $this->responder(200, "Usuario cadastrado com sucesso!!");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao cadastrar usuario, tente mais tarde!");
        }
    } 
}