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

spl_autoload_register(function ($class) use ($backendRoot) {
    $path = str_replace('\\', '/', $class) . '.php';
    if (strpos($path, 'App/') === 0) {
        $path = substr($path, 4);
    }
    $file = $backendRoot . '/' . $path;
    if (file_exists($file)) {
        require_once $file;
    }
});

session_start();

use App\router\Router;
use App\router\AppPaths;

$caminho = AppPaths::rotaReal();
if ($caminho !== '/' && is_file(__DIR__ . $caminho)) {
    readfile(__DIR__ . $caminho);
    exit;
}

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

$router->run();
