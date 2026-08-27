<?php
namespace App\router;

class Router {
    private $Rotas = [];

    public function get($rota, $Controller, $middlewares = null){
       $this -> Rotas['GET'][$rota] =
        [  
        'controller' => $Controller,
        'middleware' => $middlewares,
        ];
    }

    public function post($rota, $Controller, $middlewares = null){
       $this -> Rotas['POST'][$rota] = 
        [  
        'controller' => $Controller,
        'middleware' => $middlewares,
        ];
    }

    public function put($rota, $Controller, $middlewares = null){
       $this -> Rotas['PUT'][$rota] = 
        [  
        'controller' => $Controller,
        'middleware' => $middlewares,
        ];
    }

    public function delete($rota, $Controller, $middlewares = null){
       $this -> Rotas['DELETE'][$rota] = 
        [  
        'controller' => $Controller,
        'middleware' => $middlewares,
        ];
    }

    public function run(){
        $Methods = $_SERVER['REQUEST_METHOD'];
        $Rota = strtolower(rtrim(AppPaths::rotaReal(), '/'));
        if($Rota === ''){
            $Rota = '/';
        }
        if(isset($this->Rotas[$Methods][$Rota])){
            $rotaEncontrada = $this->Rotas[$Methods][$Rota];
            if($rotaEncontrada["middleware"] != null){
                $Middlewares = explode("@", $rotaEncontrada["middleware"]);
                $objeto = new $Middlewares[0];
                $objeto->{$Middlewares[1]}();
            }
            $Controller = explode("@", $rotaEncontrada["controller"]);
            $objeto = new $Controller[0];
            $objeto-> {$Controller[1]}();
            return;
        }
        http_response_code(404);
        if(str_contains($Rota, "/api")){
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Rota não encontrada"]);
            return;
        }
        require_once __DIR__ . '/../views/erro/404.html';
    }
}
