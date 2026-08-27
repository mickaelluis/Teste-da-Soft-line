<?php 

/**
 * Bootstrap da aplicação Soft-line.
 */

$backendRoot = dirname(__DIR__) . '/app';

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

if (class_exists(\Dotenv\Dotenv::class) && is_file($backendRoot . '/.env')) {
    \Dotenv\Dotenv::createImmutable($backendRoot)->safeLoad();
}

session_start();

use App\router\Router;

$router = new Router();


// Rotas Frontend (páginas)
$router->get('/', 'App\Controllers\AuthController\AuthPages@login');
$router->get('/login', 'App\Controllers\AuthController\AuthPages@login');
$router->get('/cadastro', 'App\Controllers\AuthController\AuthPages@cadastro');
$router->get('/home', 'App\Controllers\Home\HomePages@index', "App\Middlewares\AuthMiddleware@verificar");

$router->get('/produtos', 'App\Controllers\Produto\ProdutoPages@lista', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/produtos/cadastro', 'App\Controllers\Produto\ProdutoPages@cadastro', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/produtos/visualizar', 'App\Controllers\Produto\ProdutoPages@visualizar', "App\Middlewares\AuthMiddleware@verificar");

$router->get('/clientes', 'App\Controllers\Cliente\ClientePages@lista', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/clientes/cadastro', 'App\Controllers\Cliente\ClientePages@cadastro', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/clientes/visualizar', 'App\Controllers\Cliente\ClientePages@visualizar', "App\Middlewares\AuthMiddleware@verificar");

$router->get('/usuario', 'App\Controllers\Usuario\UsuarioPages@index', "App\Middlewares\AuthMiddleware@verificar");

// Rotas Backend (API JSON)
$router->post('/api/login', 'App\Controllers\AuthController\Login@entrar');
$router->post('/api/register', 'App\Controllers\AuthController\Register@cadastrar');
$router->get('/api/clientes', 'App\Controllers\Cliente\Clientes@listar_Clientes', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/api/cliente', 'App\Controllers\Cliente\Clientes@buscar_Cliente', "App\Middlewares\AuthMiddleware@verificar");
$router->post('/api/clientes', 'App\Controllers\Cliente\Clientes@inserir_Clientes', "App\Middlewares\AuthMiddleware@verificar");
$router->put('/api/clientes', 'App\Controllers\Cliente\Clientes@atualizar_Cliente', "App\Middlewares\AuthMiddleware@verificar");
$router->delete('/api/cliente', 'App\Controllers\Cliente\Clientes@deletar_Cliente', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/api/produtos', 'App\Controllers\Produto\Produto@listar_Produtos', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/api/produto', 'App\Controllers\Produto\Produto@buscar_Produto', "App\Middlewares\AuthMiddleware@verificar");
$router->post('/api/produtos', 'App\Controllers\Produto\Produto@inserir_Produto', "App\Middlewares\AuthMiddleware@verificar");
$router->put('/api/produtos', 'App\Controllers\Produto\Produto@atualizar_Produto', "App\Middlewares\AuthMiddleware@verificar");
$router->delete('/api/produto', 'App\Controllers\Produto\Produto@deletar_Produto', "App\Middlewares\AuthMiddleware@verificar");
$router->get('/api/usuario', 'App\Controllers\Usuario\Usuario@buscar_Usuario', "App\Middlewares\AuthMiddleware@verificar");
$router->put('/api/usuario', 'App\Controllers\Usuario\Usuario@atualizar_Usuario', "App\Middlewares\AuthMiddleware@verificar");
$router->delete('/api/usuario', 'App\Controllers\Usuario\Usuario@deletar_Usuario', "App\Middlewares\AuthMiddleware@verificar");
$router->post('/api/desconectar', 'App\Controllers\Usuario\Usuario@desconectar', "App\Middlewares\AuthMiddleware@verificar");

$router->run();
