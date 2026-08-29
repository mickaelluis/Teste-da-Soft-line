async function excluirProduto(id) {
    const confirmar = await confirmarAcao(
        "Excluir produto",
        "Essa ação não pode ser desfeita. Deseja continuar?"
    );

    if (!confirmar) {
        return false;
    }

    const { ok, corpo } = await requisitar(`/api/produto?id=${encodeURIComponent(id)}`, {
        method: "DELETE",
    });

    if (!ok) {
        await mostrarAlerta("Não foi possível excluir", corpo.message || "Falha ao excluir o produto.");
        return false;
    }

    return true;
}

async function carregarListaProdutos() {
    const status = document.getElementById("lista-status");
    const vazio = document.getElementById("lista-vazia");
    const tabela = document.getElementById("lista-tabela");
    const corpo = document.getElementById("lista-corpo");

    try {
        const { ok, corpo: resposta } = await requisitar("/api/produtos");
        const produtos = Array.isArray(resposta.data) ? resposta.data : [];

        status.hidden = true;

        if (!ok) {
            status.hidden = false;
            status.textContent = resposta.message || "Falha ao listar produtos.";
            return;
        }

        if (produtos.length === 0) {
            vazio.hidden = false;
            tabela.hidden = true;
            return;
        }

        vazio.hidden = true;
        tabela.hidden = false;
        corpo.innerHTML = produtos.map((produto) => `
            <tr>
                <td>${escapar(valorRegistro(produto, "codigo"))}</td>
                <td>${escapar(valorRegistro(produto, "nome"))}</td>
                <td>${escapar(formatarDecimal(valorRegistro(produto, "valor"), 2))}</td>
                <td>${escapar(valorRegistro(produto, "codigo_de_barras"))}</td>
                <td>
                    <div class="tabela-acoes">
                        <a class="botao botao--pequeno botao--secundario" href="/produtos/visualizar?id=${escapar(valorRegistro(produto, "id"))}">Ver</a>
                        <button type="button" class="botao botao--pequeno botao--perigo" data-excluir="${escapar(valorRegistro(produto, "id"))}">Excluir</button>
                    </div>
                </td>
            </tr>
        `).join("");

        corpo.querySelectorAll("[data-excluir]").forEach((botao) => {
            botao.addEventListener("click", async () => {
                const removido = await excluirProduto(botao.dataset.excluir);
                if (removido) {
                    carregarListaProdutos();
                }
            });
        });
    } catch (erro) {
        status.hidden = false;
        status.textContent = "Não foi possível conectar ao servidor.";
    }
}

async function carregarFormularioProduto() {
    const form = document.getElementById("form-produto");
    const id = parametroUrl("id");
    const alerta = document.getElementById("form-alerta");
    const botao = document.getElementById("botao-salvar");
    const codigo = document.getElementById("codigo");
    const nome = document.getElementById("nome");
    const descricao = document.getElementById("descricao");
    const codigoBarras = document.getElementById("codigo-barras");
    const valor = document.getElementById("valor");
    const pesoBruto = document.getElementById("peso-bruto");
    const pesoLiquido = document.getElementById("peso-liquido");

    if (id) {
        document.getElementById("pagina-titulo").textContent = "Editar Produto";
        document.getElementById("pagina-subtitulo").textContent = "Altere os dados e confirme";
        document.title = "Editar Produto";

        const { ok, corpo } = await requisitar(`/api/produto?id=${encodeURIComponent(id)}`);
        if (!ok || !corpo.data) {
            mostrarAlertaForm(alerta, corpo.message || "Produto não encontrado.", true);
            botao.disabled = true;
            return;
        }

        const produto = corpo.data;
        codigo.value = produto.codigo ?? "";
        nome.value = produto.nome ?? "";
        descricao.value = produto.descricao ?? "";
        codigoBarras.value = produto.codigo_de_barras ?? "";
        valor.value = formatarDecimal(produto.valor, 2);
        pesoBruto.value = formatarDecimal(produto.peso_bruto, 3);
        pesoLiquido.value = formatarDecimal(produto.peso_liquido, 3);
    }

    vigiarFormulario(form);

    function validar() {
        const codigoOk = campoInteiro(codigo, document.getElementById("erro-codigo"));
        const nomeOk = campoObrigatorio(nome, document.getElementById("erro-nome"), "Informe o nome.");
        const descricaoOk = campoObrigatorio(descricao, document.getElementById("erro-descricao"), "Informe a descrição.");
        const barrasOk = campoObrigatorio(codigoBarras, document.getElementById("erro-codigo-barras"), "Informe o código de barras.");
        const valorOk = campoDecimal(valor, document.getElementById("erro-valor"), "valor de venda");
        const brutoOk = campoDecimal(pesoBruto, document.getElementById("erro-peso-bruto"), "peso bruto");
        const liquidoOk = campoDecimal(pesoLiquido, document.getElementById("erro-peso-liquido"), "peso líquido");

        if (descricao.value.trim().length > 60) {
            mostrarErro(descricao, document.getElementById("erro-descricao"), "A descrição deve ter no máximo 60 caracteres.");
            return false;
        }

        if (codigoBarras.value.trim().length > 14) {
            mostrarErro(codigoBarras, document.getElementById("erro-codigo-barras"), "O código de barras deve ter no máximo 14 caracteres.");
            return false;
        }

        return codigoOk && nomeOk && descricaoOk && barrasOk && valorOk && brutoOk && liquidoOk;
    }

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        mostrarAlertaForm(alerta, "", false);

        if (!validar()) {
            return;
        }

        botao.disabled = true;

        try {
            const dados = {
                nome: nome.value.trim(),
                codigo: Number(codigo.value.trim()),
                descricao: descricao.value.trim(),
                codigo_de_barras: codigoBarras.value.trim(),
                valor: decimalParaNumero(valor.value),
                peso_bruto: decimalParaNumero(pesoBruto.value),
                peso_liquido: decimalParaNumero(pesoLiquido.value),
            };

            const url = id ? "/api/produto" : "/api/produtos";
            const metodo = id ? "PUT" : "POST";
            if (id) {
                dados.id = Number(id);
            }

            const { ok, corpo } = await requisitar(url, {
                method: metodo,
                body: JSON.stringify(dados),
            });

            if (!ok) {
                mostrarAlertaForm(alerta, corpo.message || "Falha ao salvar o produto.", true);
                return;
            }

            await mostrarAlerta(
                id ? "Produto atualizado" : "Produto cadastrado",
                corpo.message || "Produto salvo com sucesso."
            );
            liberarSaidaFormulario();
            window.location.href = "/produtos";
        } catch (erro) {
            mostrarAlertaForm(alerta, "Não foi possível conectar ao servidor.", true);
        } finally {
            botao.disabled = false;
        }
    });
}

async function carregarVisualizarProduto() {
    const id = parametroUrl("id");
    const status = document.getElementById("ver-status");
    const formProduto = document.getElementById("form-produto-ver");
    const alertaProduto = document.getElementById("form-alerta-produto");
    const botaoEditar = document.getElementById("botao-editar-produto");
    const botaoExcluir = document.getElementById("botao-excluir");
    const acoesProduto = document.getElementById("acoes-produto");
    const inputCodigo = document.getElementById("input-codigo");
    const inputNome = document.getElementById("input-nome");
    const inputDescricao = document.getElementById("input-descricao");
    const inputCodigoBarras = document.getElementById("input-codigo-barras");
    const inputValor = document.getElementById("input-valor");
    const inputPesoBruto = document.getElementById("input-peso-bruto");
    const inputPesoLiquido = document.getElementById("input-peso-liquido");
    let editandoProduto = false;

    if (!id) {
        window.location.href = "/produtos";
        return;
    }

    function preencherProduto(produto) {
        document.getElementById("ver-codigo").textContent = produto.codigo ?? "";
        document.getElementById("ver-nome").textContent = produto.nome ?? "";
        document.getElementById("ver-descricao").textContent = produto.descricao ?? "";
        document.getElementById("ver-codigo-barras").textContent = produto.codigo_de_barras ?? "";
        document.getElementById("ver-valor").textContent = formatarDecimal(produto.valor, 2);
        document.getElementById("ver-peso-bruto").textContent = formatarDecimal(produto.peso_bruto, 3);
        document.getElementById("ver-peso-liquido").textContent = formatarDecimal(produto.peso_liquido, 3);

        inputCodigo.value = produto.codigo ?? "";
        inputNome.value = produto.nome ?? "";
        inputDescricao.value = produto.descricao ?? "";
        inputCodigoBarras.value = produto.codigo_de_barras ?? "";
        inputValor.value = formatarDecimal(produto.valor, 2);
        inputPesoBruto.value = formatarDecimal(produto.peso_bruto, 3);
        inputPesoLiquido.value = formatarDecimal(produto.peso_liquido, 3);
    }

    function validarProdutoInline() {
        const codigoOk = campoInteiro(inputCodigo, document.getElementById("erro-codigo"));
        const nomeOk = campoObrigatorio(inputNome, document.getElementById("erro-nome"), "Informe o nome.");
        const descricaoOk = campoObrigatorio(inputDescricao, document.getElementById("erro-descricao"), "Informe a descrição.");
        const barrasOk = campoObrigatorio(inputCodigoBarras, document.getElementById("erro-codigo-barras"), "Informe o código de barras.");
        const valorOk = campoDecimal(inputValor, document.getElementById("erro-valor"), "valor de venda");
        const brutoOk = campoDecimal(inputPesoBruto, document.getElementById("erro-peso-bruto"), "peso bruto");
        const liquidoOk = campoDecimal(inputPesoLiquido, document.getElementById("erro-peso-liquido"), "peso líquido");

        if (inputDescricao.value.trim().length > 60) {
            mostrarErro(inputDescricao, document.getElementById("erro-descricao"), "A descrição deve ter no máximo 60 caracteres.");
            return false;
        }

        if (inputCodigoBarras.value.trim().length > 14) {
            mostrarErro(inputCodigoBarras, document.getElementById("erro-codigo-barras"), "O código de barras deve ter no máximo 14 caracteres.");
            return false;
        }

        return codigoOk && nomeOk && descricaoOk && barrasOk && valorOk && brutoOk && liquidoOk;
    }

    async function atualizarTela() {
        const { ok, corpo } = await requisitar(`/api/produto?id=${encodeURIComponent(id)}`);
        if (!ok || !corpo.data) {
            status.hidden = false;
            status.textContent = corpo.message || "Produto não encontrado.";
            formProduto.hidden = true;
            acoesProduto.hidden = true;
            return;
        }

        preencherProduto(corpo.data);
        status.hidden = true;
        formProduto.hidden = false;
        acoesProduto.hidden = false;
    }

    try {
        await atualizarTela();
    } catch (erro) {
        status.textContent = "Não foi possível conectar ao servidor.";
        return;
    }

    vigiarFormulario(formProduto);

    botaoEditar.addEventListener("click", async () => {
        mostrarAlertaForm(alertaProduto, "", false);

        if (!editandoProduto) {
            editandoProduto = true;
            alternarEdicaoFicha(formProduto, true);
            botaoEditar.textContent = "Salvar";
            return;
        }

        if (!validarProdutoInline()) {
            return;
        }

        botaoEditar.disabled = true;

        try {
            const { ok, corpo } = await requisitar("/api/produto", {
                method: "PUT",
                body: JSON.stringify({
                    id: Number(id),
                    nome: inputNome.value.trim(),
                    codigo: Number(inputCodigo.value.trim()),
                    descricao: inputDescricao.value.trim(),
                    codigo_de_barras: inputCodigoBarras.value.trim(),
                    valor: decimalParaNumero(inputValor.value),
                    peso_bruto: decimalParaNumero(inputPesoBruto.value),
                    peso_liquido: decimalParaNumero(inputPesoLiquido.value),
                }),
            });

            if (!ok) {
                mostrarAlertaForm(alertaProduto, corpo.message || "Falha ao salvar o produto.", true);
                return;
            }

            editandoProduto = false;
            alternarEdicaoFicha(formProduto, false);
            botaoEditar.textContent = "Editar";
            await atualizarTela();
            if (typeof formProduto.atualizarEstadoInicial === "function") {
                formProduto.atualizarEstadoInicial();
            }
            liberarSaidaFormulario();
            await mostrarAlerta("Produto atualizado", corpo.message || "Produto salvo com sucesso.");
        } catch (erro) {
            mostrarAlertaForm(alertaProduto, "Não foi possível conectar ao servidor.", true);
        } finally {
            botaoEditar.disabled = false;
        }
    });

    botaoExcluir.addEventListener("click", async () => {
        const removido = await excluirProduto(id);
        if (removido) {
            liberarSaidaFormulario();
            window.location.href = "/produtos";
        }
    });
}

if (document.getElementById("lista-corpo") && document.getElementById("lista-status")) {
    carregarListaProdutos();
}

if (document.getElementById("form-produto")) {
    carregarFormularioProduto();
}

if (document.getElementById("form-produto-ver")) {
    carregarVisualizarProduto();
}
