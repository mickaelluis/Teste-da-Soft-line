<?php

namespace App\Controllers\Produto;
use App\Controllers\BaseController;
use App\Models\ProdutosModels\ProdutosModels;
use App\Exceptions\ValorDuplicado;
use App\Exceptions\RegistroNaoEncontrado;
use App\Exceptions\FalhaDeConexao;


class Produto extends BaseController{
    public function inserir_Produto(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $nome = $dados['nome'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $descricao = $dados['descricao'] ?? null;
            $codigo_de_barras = $dados['codigo_de_barras'] ?? null;
            $valor = $dados['valor'] ?? null;
            $peso_bruto = $dados['peso_bruto'] ?? null;
            $peso_liquido = $dados['peso_liquido'] ?? null;

            $obrigatorios = ['nome', 'codigo', 'descricao', 'codigo_de_barras', 'valor', 'peso_bruto', 'peso_liquido'];
            foreach ($obrigatorios as $campo) {
            $conteudo = $dados[$campo] ?? null;
            if ($conteudo === null || trim((string)$conteudo) === '') {
                return $this->responder(400, "Campo obrigatório: {$campo}");
                }
            }

            if( mb_strlen($nome) > 100){
                return $this->responder(400, "Campo nome deve ter menos que 100 caracteres");
            }

            if( mb_strlen($descricao) > 60){
                return $this->responder(400, "Campo descrição deve ter menos que 60 caracteres");
            }

            if( mb_strlen($codigo_de_barras) > 14){
                return $this->responder(400, "Campo código de barras deve ter no maximo 14 caracteres");
            }

            if(!is_numeric($valor) || $valor <= 0){
                return $this->responder(400, "Campo valor deve ser um numero maior que zero");
            }

            if(!is_numeric($peso_bruto) || $peso_bruto <= 0){
                return $this->responder(400, "Campo peso bruto deve ser um numero maior que zero");
            }

            if(!is_numeric($peso_liquido) || $peso_liquido <= 0){
                return $this->responder(400, "Campo peso líquido deve ser um numero maior que zero");
            }

            $ProdutoModels = new ProdutosModels();
            $ProdutoModels->criar_Produto($id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido);

            return $this->responder(200, "Produto criado com sucesso!!!" ); 
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao criar produto, tente mais tarde!");
        }
    }

    public function listar_Produtos(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $ProdutoModels = new ProdutosModels();
            $produtos = $ProdutoModels->listar_Produtos($id);
            if (empty($produtos)) {
                return $this->responder(200, "Voce nao tem nenhum produto cadastrado", $produtos);
            }
            return $this->responder(200, "Produtos listados com sucesso!!!", $produtos);
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao listar produtos, tente mais tarde!");
        }
    }

    public function buscar_Produto(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $id_produto = $_GET['id'] ?? null;
            if ($id_produto === null || trim((string)$id_produto) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }
            $ProdutoModels = new ProdutosModels();
            $produto = $ProdutoModels->buscar_Produto($id_produto, $id);
            return $this->responder(200, "Produto listado com sucesso!!!", $produto);
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao buscar produto, tente mais tarde!");
        }
    }

    public function atualizar_Produto(){
        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            $id = $_SESSION['id_usuario'] ?? null;
            $id_produto = $dados['id'] ?? null;
            $nome = $dados['nome'] ?? null;
            $codigo = $dados['codigo'] ?? null;
            $descricao = $dados['descricao'] ?? null;
            $codigo_de_barras = $dados['codigo_de_barras'] ?? null;
            $valor = $dados['valor'] ?? null;
            $peso_bruto = $dados['peso_bruto'] ?? null;
            $peso_liquido = $dados['peso_liquido'] ?? null;

            if ($id_produto === null || trim((string)$id_produto) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }

            if ($nome !== null && mb_strlen($nome) > 100){
                return $this->responder(400, "Campo nome deve ter menos que 100 caracteres");
            }

            if ($descricao !== null && mb_strlen($descricao) > 60){
                return $this->responder(400, "Campo descrição deve ter menos que 60 caracteres");
            }

            if ($codigo_de_barras !== null && mb_strlen($codigo_de_barras) > 14){
                return $this->responder(400, "Campo código de barras deve ter no maximo 14 caracteres");
            }

            if ($valor !== null && (!is_numeric($valor) || $valor <= 0)){
                return $this->responder(400, "Campo valor deve ser um numero maior que zero");
            }

            if ($peso_bruto !== null && (!is_numeric($peso_bruto) || $peso_bruto <= 0)){
                return $this->responder(400, "Campo peso bruto deve ser um numero maior que zero");
            }

            if ($peso_liquido !== null && (!is_numeric($peso_liquido) || $peso_liquido <= 0)){
                return $this->responder(400, "Campo peso líquido deve ser um numero maior que zero");
            }

            $ProdutoModels = new ProdutosModels();
            $ProdutoModels->atualizar_Produto($id_produto, $id, $nome, $codigo, $descricao, $codigo_de_barras, $valor, $peso_bruto, $peso_liquido);
            return $this->responder(200, "Produto atualizado com sucesso!!!");
        } catch (ValorDuplicado $e) {
            return $this->responder(409, $this->mensagem_duplicado($e));
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao atualizar produto, tente mais tarde!");
        }
    }

    public function deletar_Produto(){
        try {
            $id = $_SESSION['id_usuario'] ?? null;
            $id_produto = $_GET['id'] ?? null;
            if ($id_produto === null || trim((string)$id_produto) === '') {
                return $this->responder(400, "Campo obrigatório: id");
            }
            $ProdutoModels = new ProdutosModels();
            $ProdutoModels->deletar_Produto($id_produto, $id);
            return $this->responder(200, "Produto deletado com sucesso!!!");
        } catch (RegistroNaoEncontrado $e) {
            return $this->responder(404, $this->mensagem_nao_encontrado($e));
        } catch (FalhaDeConexao $e) {
            return $this->responder(500, "Falha ao conectar no banco de dados");
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->responder(500, "Falha ao deletar produto, tente mais tarde!");
        }
    }

    private function mensagem_duplicado(ValorDuplicado $e){
        return match ($e->getUnique()) {
            'uk_produtos_codigo_barras' => 'Esse código de barras já está cadastrado, tente outro!',
            'uk_produtos_codigo' => 'Esse codigo já está cadastrado, tente outro!',
            default => 'Esse valor já está cadastrado, tente outro!',
        };
    }

    private function mensagem_nao_encontrado(RegistroNaoEncontrado $e){
        return match ($e->getRegistro()) {
            'produto' => 'Produto nao encontrado',
            default => 'Registro nao encontrado',
        };
    }
}
