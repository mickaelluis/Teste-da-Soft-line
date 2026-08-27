<?php

namespace App\Exceptions;

class RegistroNaoEncontrado extends \RuntimeException
{
    private const MENSAGEM_TECNICA = 'Registro nao encontrado no banco de dados';

    private string $registro;

    public function __construct(string $registro)
    {
        parent::__construct(self::MENSAGEM_TECNICA);
        $this->registro = $registro;
    }

    public function getRegistro(): string
    {
        return $this->registro;
    }
}
