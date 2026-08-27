<?php

namespace App\Controllers\Usuario;
use App\Controllers\BaseController;
use App\Models\UsuariosModels\UsuariosModels;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;
use App\Exceptions\FalhaDeConexao;


class Usuario extends BaseController{
    public function buscar_Usuario(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $UsuarioModels = new UsuariosModels();
            $usuario = $UsuarioModels->buscar_Usuario($id);
            return $this->responder(200, "Usuario listado com sucesso!!!", $usuario);
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao buscar usuario, tente mais tarde!");
        }
    }

    public function atualizar_Usuario(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $nome = $dados['nome'] ?? null;
            $email = $dados['email'] ?? null;
            $senha = $dados['senha'] ?? null;
            $confirm_senha = $dados['confirm_senha'] ?? null;
            $senhaHash = null;

            if ($nome !== null && mb_strlen($nome) > 60){
                return $this->responder(400, "Campo nome deve ter menos que 60 caracteres");
            }

            if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)){
                return $this->responder(400, "Email invalido, por favor tentar outro");
            }

            if ($senha !== null) {
                if (mb_strlen((string)$senha) < 4){
                    return $this->responder(400, "A senha deve ter no minimo 4 caracteres");
                }
                if ($senha !== $confirm_senha){
                    return $this->responder(400, "As duas senha tem que ser iguais");
                }
                $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            }

            $UsuarioModels = new UsuariosModels();
            $UsuarioModels->atualizar_Usuario($id, $email, $nome, $senhaHash);
            if ($email !== null) {
                $_SESSION['email'] = $email;
            }
            return $this->responder(200, "Usuario atualizado com sucesso!!!");
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao atualizar usuario, tente mais tarde!");
        }
    }

    public function deletar_Usuario(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $UsuarioModels = new UsuariosModels();
            $UsuarioModels->deletar_Usuario($id);
            $this->encerrar_sessao();
            return $this->responder(200, "Usuario deletado com sucesso!!!");
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao deletar usuario, tente mais tarde!");
        }
    }

    public function desconectar(){
        try {
            $this->encerrar_sessao();
            return $this->responder(200, "Desconectado com sucesso!!!");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao desconectar, tente mais tarde!");
        }
    }

    private function encerrar_sessao(){
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    private function mensagem_duplicado(ValorDuplicado $e){
        return match ($e->getUnique()) {
            'email' => 'Este e-mail não pode ser registrado, tente outro.',
            default => 'Esse valor já está cadastrado, tente outro!',
        };
    }

    private function mensagem_nao_encontrado(RegistroNaoEncontrado $e){
        return match ($e->getRegistro()) {
            'usuario' => 'Usuario nao encontrado',
            default => 'Registro nao encontrado',
        };
    }
}
