<?php 
 
namespace App\Controllers\AuthController;

 use App\Controllers\BaseController;

 use App\Models\AuthModels\RegisterModels;
 use App\Exceptions\ValorDuplicado;
 use App\Exceptions\FalhaDeConexao;

class Register extends BaseController {

    public function cadastrar() {
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $name = $dados['name'] ?? null;
            $email = $dados['email'] ?? null;
            $senha = $dados['senha'] ?? null;
            $confirm_senha = $dados['confirm_senha'] ?? null;

            if($name === null || trim((string)$name) === ''){
                return $this->responder(400, "Campo obrigatório: name");
            }
            if( mb_strlen($name) > 60){
                return $this->responder(400, "Campo nome deve ter menos que 60 caracteres");
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                return $this->responder(400, "Email invalido, por favor tentar outro"); 
            }
            if( mb_strlen((string)$senha) < 4){
                return $this->responder(400, "A senha deve ter no minimo 4 caracteres");
            }
            if($senha !== $confirm_senha){
                return $this->responder(400, "As duas senha tem que ser iguais");
            }
            $senhaHash    = password_hash($senha, PASSWORD_BCRYPT);
            $usuarioModel = new RegisterModels();
            $usuarioModel->criar_registro($name, $email, $senhaHash);
            return $this->responder(200, "Usuario cadastrado com sucesso!!");
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao cadastrar usuario, tente mais tarde!");
        }
    }

    private function mensagem_duplicado(ValorDuplicado $e){
        return match ($e->getUnique()) {
            'email' => 'Este e-mail não pode ser registrado, tente outro.',
            default => 'Esse valor já está cadastrado, tente outro!',
        };
    } 
}
