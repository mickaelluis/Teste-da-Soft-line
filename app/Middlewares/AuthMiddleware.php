<?php
namespace App\Middlewares;

use App\router\AppPaths;

class AuthMiddleware {
    public function verificar() {
        if (!isset($_SESSION['email'])) {
            $Rota = AppPaths::rotaReal();
            if(str_contains($Rota, "/api")){
                http_response_code(401);
                echo json_encode(["status" => "error", "message" => "Não autorizado"]);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
}