<?php
 
namespace App\Controllers\AuthController;
use App\Controllers\BaseController;
use App\Models\AuthModels\LoginModels;
use App\Exceptions\FalhaDeConexao;

class Login extends BaseController{
    public function entrar(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $email = $dados['email'] ?? null;
            $senha = $dados['senha'] ?? null;
            $AuthModel = new LoginModels();
            $usuario = $AuthModel->buscar_por_email($email);
            if($usuario === false){
                return $this->responder(400, "Email ou senha invalido, por favor tentar outro"); 
            }
            if(!password_verify($senha, $usuario['password'])){
                return $this->responder(400, "Email ou senha invalido, por favor tentar outro"); 
            }
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['email'] = $email;
            return $this->responder(200, "Logado com sucesso, seja bem vindo {$usuario["nome"]}" ); 
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao fazer login, tente mais tarde!");
        }
    }
}
