<?php

namespace App\Controllers\Cliente;
use App\Controllers\BaseController;
use App\Models\ClientesModels\ClientesModels;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;
use App\Exceptions\FalhaDeConexao;


class Clientes extends BaseController{
    public function inserir_Clientes(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $nome = $dados['nome'] ?? null;
            $fantasia = $dados['fantasia'] ?? null;
            $documento = preg_replace('/\D/', '', (string)($dados['documento'] ?? null));
            $endereco  = [
                'cep'    => $dados['cep'] ?? null,
                'estado' => $dados['estado'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'rua'    => $dados['rua'] ?? null,
                'numero' => $dados['numero'] ?? null,
            ];

            $obrigatorios = ['nome', 'codigo', 'fantasia', 'documento', 'cep', 'estado', 'cidade', 'bairro', 'rua', 'numero'];
            foreach ($obrigatorios as $campo) {
            $valor = $dados[$campo] ?? null;
            if ($valor === null || trim((string)$valor) === '') {
                return $this->responder(400, "Campo obrigatório: {$campo}");
                }
            }
            
            if (filter_var($codigo, FILTER_VALIDATE_INT) === false) {
                return $this->responder(400, "Campo codigo so pode ser numerico");
            }
            $codigo = (int) $codigo;

            if( mb_strlen($nome) > 60){
                return $this->responder(400, "Campo nome deve ter menos que 60 caracteres");
            }

            if( mb_strlen($fantasia) > 100){
                return $this->responder(400, "Campo fantasia deve ter menos que 100 caracteres");
            }

            if( strlen($documento) !== 11 && strlen($documento) !== 14){
                return $this->responder(400, "Campo documento tem que ser um cpf ou cnpj valido");
            }

            $ClienteModels = new ClientesModels();
            $ClienteModels->criar_Cliente($id, $codigo, $nome, $fantasia, $documento, $endereco);
            return $this->responder(200, "Cliente criado com sucesso!!!" ); 
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao criar cliente, tente mais tarde!");
        }
    }

    public function listar_Clientes(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $ClienteModels = new ClientesModels();
            $clientes = $ClienteModels->listar_Clientes($id);
            if (empty($clientes)) {
                return $this->responder(200, "Voce nao tem nenhum cliente cadastrado", $clientes);
            }
            return $this->responder(200, "Clientes listados com sucesso!!!", $clientes);
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao listar clientes, tente mais tarde!");
        }
    }

    public function buscar_Cliente(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $id_cliente = $_GET['id'] ?? null;
            if ($id_cliente === null || trim((string)$id_cliente) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }
            $ClienteModels = new ClientesModels();
            $cliente = $ClienteModels->buscar_Cliente($id_cliente, $id);
            return $this->responder(200, "Cliente listado com sucesso!!!", $cliente);
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao buscar cliente, tente mais tarde!");
        }
    }

    public function atualizar_Cliente(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $id_cliente = $dados['id'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $nome = $dados['nome'] ?? null;
            $fantasia = $dados['fantasia'] ?? null;
            $documento = isset($dados['documento']) ? preg_replace('/\D/', '', (string)$dados['documento']) : null;

            if ($id_cliente === null || trim((string)$id_cliente) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }

            if ($codigo !== null) {
                if (filter_var($codigo, FILTER_VALIDATE_INT) === false) {
                    return $this->responder(400, "Campo codigo so pode ser numerico");
                }
                $codigo = (int) $codigo;
            }

            if ($nome !== null && mb_strlen($nome) > 60){
                return $this->responder(400, "Campo nome deve ter menos que 60 caracteres");
            }

            if ($fantasia !== null && mb_strlen($fantasia) > 100){
                return $this->responder(400, "Campo fantasia deve ter menos que 100 caracteres");
            }

            if ($documento !== null && strlen($documento) !== 11 && strlen($documento) !== 14){
                return $this->responder(400, "Campo documento tem que ser um cpf ou cnpj valido");
            }

            $campos_endereco = ['cep', 'estado', 'cidade', 'bairro', 'rua', 'numero'];
            $tem_endereco = false;
            foreach ($campos_endereco as $campo) {
                if (array_key_exists($campo, $dados)) {
                    $tem_endereco = true;
                    break;
                }
            }

            $endereco = null;
            if ($tem_endereco) {
                $id_endereco = $dados['id_endereco'] ?? null;
                if ($id_endereco === null || trim((string)$id_endereco) === '') {
                    return $this->responder(400, "Campo obrigatório: id_endereco");
                }
                $endereco = [
                    'id'     => $id_endereco,
                    'cep'    => $dados['cep'] ?? null,
                    'estado' => $dados['estado'] ?? null,
                    'cidade' => $dados['cidade'] ?? null,
                    'bairro' => $dados['bairro'] ?? null,
                    'rua'    => $dados['rua'] ?? null,
                    'numero' => $dados['numero'] ?? null,
                ];
            }

            $ClienteModels = new ClientesModels();
            $ClienteModels->atualizar_Cliente($id_cliente, $id, $codigo, $nome, $fantasia, $documento, $endereco);
            return $this->responder(200, "Cliente atualizado com sucesso!!!");
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao atualizar cliente, tente mais tarde!");
        }
    }

    public function deletar_Cliente(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $id_cliente = $_GET['id'] ?? null;
            if ($id_cliente === null || trim((string)$id_cliente) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }
            $ClienteModels = new ClientesModels();
            $ClienteModels->deletar_Cliente($id_cliente, $id);
            return $this->responder(200, "Cliente deletado com sucesso!!!");
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao deletar cliente, tente mais tarde!");
        }
    }

    public function deletar_Endereco(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $id_endereco = $_GET['id'] ?? null;
            $id_cliente = $_GET['id_cliente'] ?? null;
            if ($id_endereco === null || trim((string)$id_endereco) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }
            if ($id_cliente === null || trim((string)$id_cliente) === '') {
                return $this->responder(400, "Campo obrigatório: id_cliente");
            }
            $ClienteModels = new ClientesModels();
            $ClienteModels->deletar_Endereco($id_endereco, $id_cliente, $id);
            return $this->responder(200, "Endereco deletado com sucesso!!!");
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao deletar endereco, tente mais tarde!");
        }
    }

    public function inserir_Endereco(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $id_cliente = $dados['id_cliente'] ?? null;
            $endereco = [
                'cep'    => $dados['cep'] ?? null,
                'estado' => $dados['estado'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'rua'    => $dados['rua'] ?? null,
                'numero' => $dados['numero'] ?? null,
            ];

            if ($id_cliente === null || trim((string)$id_cliente) === '') {
                return $this->responder(400, "Campo obrigatório: id_cliente");
            }

            $obrigatorios = ['cep', 'estado', 'cidade', 'bairro', 'rua', 'numero'];
            foreach ($obrigatorios as $campo) {
                $valor = $dados[$campo] ?? null;
                if ($valor === null || trim((string)$valor) === '') {
                    return $this->responder(400, "Campo obrigatório: {$campo}");
                }
            }

            $ClienteModels = new ClientesModels();
            $ClienteModels->inserir_Endereco($id_cliente, $id, $endereco);
            return $this->responder(200, "Endereco criado com sucesso!!!");
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao criar endereco, tente mais tarde!");
        }
    }

    private function mensagem_duplicado(ValorDuplicado $e){
        return match ($e->getUnique()) {
            'uk_clientes_documento' => 'Esse documento já está cadastrado, tente outro!',
            'uk_clientes_codigo' => 'Esse codigo já está cadastrado, tente outro!',
            default => 'Esse valor já está cadastrado, tente outro!',
        };
    }

    private function mensagem_nao_encontrado(RegistroNaoEncontrado $e){
        return match ($e->getRegistro()) {
            'cliente' => 'Cliente nao encontrado',
            'endereco' => 'Endereco nao encontrado para este cliente',
            default => 'Registro nao encontrado',
        };
    }
}
