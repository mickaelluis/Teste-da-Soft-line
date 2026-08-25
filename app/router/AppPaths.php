<?php
namespace App\router;

/**
 * Caminhos públicos da aplicação.
 * APP_BASE = pasta no browser; rotas PHP/JS usam strip antes do match.
 */
class AppPaths
{   
   public static function rotaReal(){
        $URL = $_SERVER['REQUEST_URI'];
        $script = $_SERVER['SCRIPT_NAME'];
        $caminhoReal= dirname($script);
        $tamanho = strlen($caminhoReal);
        if(strpos($URL, '?') !== false){
                $Possicao = strpos($URL, '?');
                $URLcortadaDOIS = substr($URL, 0, $Possicao);
                $URL = $URLcortadaDOIS;
        };
        if($tamanho > 1){
            $URLcortada = substr($URL, $tamanho);
            return $URLcortada;
        };
        return $URL;
    }
}
