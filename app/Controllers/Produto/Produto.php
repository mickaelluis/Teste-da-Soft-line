//        $dados = json_decode(file_get_contents('php://input'), true);
        $nome = $dados['nome'] ?? null;
        $codigo = $dados['codigo'] ?? null;
        $descricao = $dados['descricao'] ?? null;
        $codigo_de_barras = $dados['codigo_de_barras'] ?? null;
        $valor = $dados['valor'] ?? null;
        $peso_bruto = $dados['peso_bruto'] ?? null;
        $peso_liquido = $dados['peso_liquido'] ?? null; 