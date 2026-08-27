<?php

namespace App\Exceptions;

class ValorDuplicado extends \RuntimeException
{
    private const MENSAGEM_TECNICA = 'Violacao de restricao unica no banco de dados';

    private string $unique;

    public function __construct(string $unique)
    {
        parent::__construct(self::MENSAGEM_TECNICA);
        $this->unique = $unique;
    }

    public function getUnique(): string
    {
        return $this->unique;
    }

    public static function fromPdo(\PDOException $e): self
    {
        $erro = $e->getMessage();
        if (preg_match("/for key '([^']+)'/i", $erro, $matches)) {
            $unique = $matches[1];
            if (str_contains($unique, '.')) {
                $unique = substr($unique, strrpos($unique, '.') + 1);
            }
            return new self($unique);
        }
        return new self('desconhecido');
    }
}
