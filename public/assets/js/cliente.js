function dadosEndereco() {
    return {
        cep: soDigitos(document.getElementById("cep").value),
        estado: document.getElementById("estado").value.trim().toUpperCase(),
        cidade: document.getElementById("cidade").value.trim(),
        bairro: document.getElementById("bairro").value.trim(),
        rua: document.getElementById("rua").value.trim(),
        numero: document.getElementById("numero").value.trim(),
    };
}

function validarEndereco() {
    const cep = document.getElementById("cep");
    const estado = document.getElementById("estado");
    const cidade = document.getElementById("cidade");
    const bairro = document.getElementById("bairro");
    const rua = document.getElementById("rua");
    const numero = document.getElementById("numero");

    const cepOk = soDigitos(cep.value).length === 8;
    mostrarErro(cep, document.getElementById("erro-cep"), cepOk ? "" : "Informe um CEP com 8 dígitos.");

    const estadoOk = campoObrigatorio(estado, document.getElementById("erro-estado"), "Informe o estado.");
    const cidadeOk = campoObrigatorio(cidade, document.getElementById("erro-cidade"), "Informe a cidade.");
    const bairroOk = campoObrigatorio(bairro, document.getElementById("erro-bairro"), "Informe o bairro.");
    const ruaOk = campoObrigatorio(rua, document.getElementById("erro-rua"), "Informe a rua.");
    const numeroOk = campoObrigatorio(numero, document.getElementById("erro-numero"), "Informe o número.");

    return cepOk && estadoOk && cidadeOk && bairroOk && ruaOk && numeroOk;
}

function validarDocumento(input, elementoErro) {
    const digitos = obterDigitosDocumento(input);
    if (!digitos) {
        mostrarErro(input, elementoErro, "Informe o documento.");
        return false;
    }
    if (digitos.length !== 11 && digitos.length !== 14) {
        mostrarErro(input, elementoErro, "Informe um CPF (11 dígitos) ou CNPJ (14 dígitos).");
        return false;
    }
    mostrarErro(input, elementoErro, "");
    return true;
}

function textoEndereco(endereco) {
    return `${mascararCep(endereco.cep)} · ${endereco.rua}, ${endereco.numero} · ${endereco.bairro} · ${endereco.cidade}/${endereco.estado}`;
}

function preencherEnderecoPorCep(dados) {
    if (!dados) {
        return;
    }

    document.getElementById("rua").value = dados.rua ?? "";
    document.getElementById("bairro").value = dados.bairro ?? "";
    document.getElementById("cidade").value = dados.cidade ?? "";
    document.getElementById("estado").value = dados.estado ?? "";
    document.getElementById("numero").focus();
}

function ligarBuscaCep() {
    const cepInput = document.getElementById("cep");
    if (!cepInput || cepInput.dataset.cepLigado === "true") {
        return;
    }

    cepInput.dataset.cepLigado = "true";

    const camposEndereco = ["estado", "cidade", "bairro", "rua"]
        .map((id) => document.getElementById(id))
        .filter(Boolean);
    const erroCep = document.getElementById("erro-cep");

    async function consultarCep() {
        const digitos = soDigitos(cepInput.value);
        if (digitos.length !== 8) {
            return;
        }

        if (digitos === cepInput.dataset.ultimoCepConsultado) {
            return;
        }

        cepInput.dataset.ultimoCepConsultado = digitos;
        mostrarErro(cepInput, erroCep, "");

        camposEndereco.forEach((campo) => {
            campo.disabled = true;
        });
        cepInput.disabled = true;

        try {
            const dados = await buscarEnderecoPorCep(digitos);
            if (dados) {
                preencherEnderecoPorCep(dados);
                mostrarErro(cepInput, erroCep, "");
            } else {
                mostrarErro(cepInput, erroCep, "CEP não encontrado. Preencha manualmente.");
            }
        } catch {
            mostrarErro(cepInput, erroCep, "Não foi possível consultar o CEP. Preencha manualmente.");
        } finally {
            camposEndereco.forEach((campo) => {
                campo.disabled = false;
            });
            cepInput.disabled = false;
        }
    }

    cepInput.addEventListener("blur", consultarCep);
    cepInput.addEventListener("input", () => {
        if (soDigitos(cepInput.value).length === 8) {
            consultarCep();
        }
    });
}

async function excluirCliente(id) {
    const confirmar = await confirmarAcao(
        "Excluir cliente",
        "O cliente e os endereços vinculados serão removidos. Deseja continuar?"
    );

    if (!confirmar) {
        return false;
    }

    const { ok, corpo } = await requisitar(`/api/cliente?id=${encodeURIComponent(id)}`, {
        method: "DELETE",
    });

    if (!ok) {
        await mostrarAlerta("Não foi possível excluir", corpo.message || "Falha ao excluir o cliente.");
        return false;
    }

    return true;
}

async function carregarListaClientes() {
    const status = document.getElementById("lista-status");
    const vazio = document.getElementById("lista-vazia");
    const tabela = document.getElementById("lista-tabela");
    const corpo = document.getElementById("lista-corpo");

    try {
        const { ok, corpo: resposta } = await requisitar("/api/clientes");
        const clientes = Array.isArray(resposta.data) ? resposta.data : [];

        status.hidden = true;

        if (!ok) {
            status.hidden = false;
            status.textContent = resposta.message || "Falha ao listar clientes.";
            return;
        }

        if (clientes.length === 0) {
            vazio.hidden = false;
            tabela.hidden = true;
            return;
        }

        vazio.hidden = true;
        tabela.hidden = false;
        corpo.innerHTML = clientes.map((cliente) => `
            <tr>
                <td>${escapar(valorRegistro(cliente, "codigo"))}</td>
                <td>${escapar(valorRegistro(cliente, "nome"))}</td>
                <td>${escapar(valorRegistro(cliente, "fantasia"))}</td>
                <td>${htmlDocumentoLista(valorRegistro(cliente, "documento"))}</td>
                <td>
                    <div class="tabela-acoes">
                        <a class="botao botao--pequeno botao--secundario" href="/clientes/visualizar?id=${escapar(valorRegistro(cliente, "id"))}">Ver</a>
                        <button type="button" class="botao botao--pequeno botao--perigo" data-excluir="${escapar(valorRegistro(cliente, "id"))}">Excluir</button>
                    </div>
                </td>
            </tr>
        `).join("");

        ligarCamposDocumento();

        corpo.querySelectorAll("[data-excluir]").forEach((botao) => {
            botao.addEventListener("click", async () => {
                const removido = await excluirCliente(botao.dataset.excluir);
                if (removido) {
                    carregarListaClientes();
                }
            });
        });
    } catch (erro) {
        status.hidden = false;
        status.textContent = "Não foi possível conectar ao servidor.";
    }
}

async function carregarFormularioCliente() {
    const form = document.getElementById("form-cliente");
    const id = parametroUrl("id");
    const alerta = document.getElementById("form-alerta");
    const botao = document.getElementById("botao-salvar");
    const codigo = document.getElementById("codigo");
    const nome = document.getElementById("nome");
    const fantasia = document.getElementById("fantasia");
    const documento = document.getElementById("documento");
    const blocoEndereco = document.getElementById("bloco-endereco");
    const tituloEndereco = document.getElementById("titulo-endereco");

    if (id) {
        document.getElementById("pagina-titulo").textContent = "Editar Cliente";
        document.getElementById("pagina-subtitulo").textContent = "Altere os dados e confirme";
        document.title = "Editar Cliente";
        blocoEndereco.hidden = true;
        tituloEndereco.hidden = true;
        blocoEndereco.querySelectorAll("input, select").forEach((campo) => {
            campo.required = false;
        });

        const { ok, corpo } = await requisitar(`/api/cliente?id=${encodeURIComponent(id)}`);
        if (!ok || !corpo.data) {
            mostrarAlertaForm(alerta, corpo.message || "Cliente não encontrado.", true);
            botao.disabled = true;
            return;
        }

        const cliente = corpo.data;
        codigo.value = cliente.codigo ?? "";
        nome.value = cliente.nome ?? "";
        fantasia.value = cliente.fantasia ?? "";
        definirDocumentoCampo(document.getElementById("campo-documento-cadastro"), cliente.documento, false);
    }

    vigiarFormulario(form);
    ligarBuscaCep();

    function validarCliente() {
        const codigoOk = campoInteiro(codigo, document.getElementById("erro-codigo"));
        const nomeOk = campoObrigatorio(nome, document.getElementById("erro-nome"), "Informe o nome.");
        const fantasiaOk = campoObrigatorio(fantasia, document.getElementById("erro-fantasia"), "Informe a fantasia.");
        const documentoOk = validarDocumento(documento, document.getElementById("erro-documento"));
        const enderecoOk = id ? true : validarEndereco();

        if (nome.value.trim().length > 60) {
            mostrarErro(nome, document.getElementById("erro-nome"), "O nome deve ter no máximo 60 caracteres.");
            return false;
        }

        if (fantasia.value.trim().length > 100) {
            mostrarErro(fantasia, document.getElementById("erro-fantasia"), "A fantasia deve ter no máximo 100 caracteres.");
            return false;
        }

        return codigoOk && nomeOk && fantasiaOk && documentoOk && enderecoOk;
    }

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        mostrarAlertaForm(alerta, "", false);

        if (!validarCliente()) {
            return;
        }

        botao.disabled = true;

        try {
            const dados = {
                codigo: Number(codigo.value.trim()),
                nome: nome.value.trim(),
                fantasia: fantasia.value.trim(),
                documento: obterDigitosDocumento(documento),
            };

            if (!id) {
                Object.assign(dados, dadosEndereco());
            } else {
                dados.id = Number(id);
            }

            const { ok, corpo } = await requisitar("/api/cliente" + (id ? "" : "s"), {
                method: id ? "PUT" : "POST",
                body: JSON.stringify(dados),
            });

            if (!ok) {
                mostrarAlertaForm(alerta, corpo.message || "Falha ao salvar o cliente.", true);
                return;
            }

            await mostrarAlerta(
                id ? "Cliente atualizado" : "Cliente cadastrado",
                corpo.message || "Cliente salvo com sucesso."
            );
            liberarSaidaFormulario();
            window.location.href = "/clientes";
        } catch (erro) {
            mostrarAlertaForm(alerta, "Não foi possível conectar ao servidor.", true);
        } finally {
            botao.disabled = false;
        }
    });
}

function preencherFormularioEndereco(endereco) {
    const cepInput = document.getElementById("cep");

    document.getElementById("id-endereco").value = endereco ? endereco.id : "";
    cepInput.value = endereco ? mascararCep(endereco.cep) : "";
    if (endereco) {
        cepInput.dataset.ultimoCepConsultado = soDigitos(endereco.cep);
    } else {
        delete cepInput.dataset.ultimoCepConsultado;
    }
    document.getElementById("estado").value = endereco ? endereco.estado : "";
    document.getElementById("cidade").value = endereco ? endereco.cidade : "";
    document.getElementById("bairro").value = endereco ? endereco.bairro : "";
    document.getElementById("rua").value = endereco ? endereco.rua : "";
    document.getElementById("numero").value = endereco ? endereco.numero : "";
    document.getElementById("titulo-form-endereco").textContent = endereco ? "Editar endereço" : "Novo endereço";
    document.getElementById("botao-cancelar-endereco").hidden = !endereco;
}

async function carregarVisualizarCliente() {
    const id = parametroUrl("id");
    const status = document.getElementById("ver-status");
    const formCliente = document.getElementById("form-cliente-ver");
    const secaoEnderecos = document.getElementById("secao-enderecos");
    const listaEnderecos = document.getElementById("lista-enderecos");
    const formEndereco = document.getElementById("form-endereco");
    const alertaEndereco = document.getElementById("alerta-endereco");
    const alertaCliente = document.getElementById("form-alerta-cliente");
    const botaoEditar = document.getElementById("botao-editar-cliente");
    const acoesCliente = document.getElementById("acoes-cliente");
    const inputCodigo = document.getElementById("input-codigo");
    const inputNome = document.getElementById("input-nome");
    const inputFantasia = document.getElementById("input-fantasia");
    const inputDocumento = document.getElementById("input-documento");
    const campoDocumento = document.getElementById("campo-documento-ver");
    let editandoCliente = false;

    if (!id) {
        window.location.href = "/clientes";
        return;
    }

    function preencherCliente(cliente) {
        document.getElementById("ver-codigo").textContent = cliente.codigo ?? "";
        document.getElementById("ver-nome").textContent = cliente.nome ?? "";
        document.getElementById("ver-fantasia").textContent = cliente.fantasia ?? "";
        definirDocumentoCampo(campoDocumento, cliente.documento, false);

        inputCodigo.value = cliente.codigo ?? "";
        inputNome.value = cliente.nome ?? "";
        inputFantasia.value = cliente.fantasia ?? "";
    }

    function validarClienteInline() {
        const codigoOk = campoInteiro(inputCodigo, document.getElementById("erro-codigo"));
        const nomeOk = campoObrigatorio(inputNome, document.getElementById("erro-nome"), "Informe o nome.");
        const fantasiaOk = campoObrigatorio(inputFantasia, document.getElementById("erro-fantasia"), "Informe a fantasia.");
        const documentoOk = validarDocumento(inputDocumento, document.getElementById("erro-documento"));

        if (inputNome.value.trim().length > 60) {
            mostrarErro(inputNome, document.getElementById("erro-nome"), "O nome deve ter no máximo 60 caracteres.");
            return false;
        }

        if (inputFantasia.value.trim().length > 100) {
            mostrarErro(inputFantasia, document.getElementById("erro-fantasia"), "A fantasia deve ter no máximo 100 caracteres.");
            return false;
        }

        return codigoOk && nomeOk && fantasiaOk && documentoOk;
    }

    async function atualizarTela() {
        const { ok, corpo } = await requisitar(`/api/cliente?id=${encodeURIComponent(id)}`);
        if (!ok || !corpo.data) {
            status.hidden = false;
            status.textContent = corpo.message || "Cliente não encontrado.";
            formCliente.hidden = true;
            acoesCliente.hidden = true;
            secaoEnderecos.hidden = true;
            return null;
        }

        const cliente = corpo.data;
        preencherCliente(cliente);

        status.hidden = true;
        formCliente.hidden = false;
        acoesCliente.hidden = false;
        secaoEnderecos.hidden = false;

        const enderecos = Array.isArray(cliente.endereco) ? cliente.endereco : [];
        if (enderecos.length === 0) {
            listaEnderecos.innerHTML = `<p class="vazio">Nenhum endereço cadastrado.</p>`;
        } else {
            listaEnderecos.innerHTML = enderecos.map((endereco) => `
                <article class="endereco">
                    <p>${escapar(textoEndereco(endereco))}</p>
                    <div class="endereco-acoes">
                        <button type="button" class="botao botao--pequeno" data-editar-endereco="${escapar(endereco.id)}">Editar</button>
                        <button type="button" class="botao botao--pequeno botao--perigo" data-excluir-endereco="${escapar(endereco.id)}">Excluir</button>
                    </div>
                </article>
            `).join("");
        }

        listaEnderecos.querySelectorAll("[data-editar-endereco]").forEach((botao) => {
            botao.addEventListener("click", () => {
                const endereco = enderecos.find((item) => String(item.id) === botao.dataset.editarEndereco);
                preencherFormularioEndereco(endereco);
                if (typeof formEndereco.atualizarEstadoInicial === "function") {
                    formEndereco.atualizarEstadoInicial();
                }
                formEndereco.scrollIntoView({ behavior: "smooth", block: "start" });
            });
        });

        listaEnderecos.querySelectorAll("[data-excluir-endereco]").forEach((botao) => {
            botao.addEventListener("click", async () => {
                const confirmar = await confirmarAcao(
                    "Excluir endereço",
                    "Deseja remover este endereço?"
                );
                if (!confirmar) {
                    return;
                }

                const { ok: okDelete, corpo: corpoDelete } = await requisitar(
                    `/api/endereco?id=${encodeURIComponent(botao.dataset.excluirEndereco)}&id_cliente=${encodeURIComponent(id)}`,
                    { method: "DELETE" }
                );

                if (!okDelete) {
                    await mostrarAlerta("Não foi possível excluir", corpoDelete.message || "Falha ao excluir o endereço.");
                    return;
                }

                preencherFormularioEndereco(null);
                if (typeof formEndereco.atualizarEstadoInicial === "function") {
                    formEndereco.atualizarEstadoInicial();
                }
                await atualizarTela();
            });
        });

        return cliente;
    }

    try {
        await atualizarTela();
    } catch (erro) {
        status.textContent = "Não foi possível conectar ao servidor.";
        return;
    }

    vigiarFormulario(formEndereco);
    vigiarFormulario(formCliente);
    ligarBuscaCep();

    botaoEditar.addEventListener("click", async () => {
        mostrarAlertaForm(alertaCliente, "", false);

        if (!editandoCliente) {
            editandoCliente = true;
            alternarEdicaoFicha(formCliente, true);
            if (campoDocumento) {
                atualizarExibicaoDocumentoCampo(campoDocumento);
            }
            botaoEditar.textContent = "Salvar";
            return;
        }

        if (!validarClienteInline()) {
            return;
        }

        botaoEditar.disabled = true;

        try {
            const { ok, corpo } = await requisitar("/api/cliente", {
                method: "PUT",
                body: JSON.stringify({
                    id: Number(id),
                    codigo: Number(inputCodigo.value.trim()),
                    nome: inputNome.value.trim(),
                    fantasia: inputFantasia.value.trim(),
                    documento: obterDigitosDocumento(inputDocumento),
                }),
            });

            if (!ok) {
                mostrarAlertaForm(alertaCliente, corpo.message || "Falha ao salvar o cliente.", true);
                return;
            }

            editandoCliente = false;
            alternarEdicaoFicha(formCliente, false);
            botaoEditar.textContent = "Editar";
            await atualizarTela();
            if (typeof formCliente.atualizarEstadoInicial === "function") {
                formCliente.atualizarEstadoInicial();
            }
            liberarSaidaFormulario();
            await mostrarAlerta("Cliente atualizado", corpo.message || "Cliente salvo com sucesso.");
        } catch (erro) {
            mostrarAlertaForm(alertaCliente, "Não foi possível conectar ao servidor.", true);
        } finally {
            botaoEditar.disabled = false;
        }
    });

    document.getElementById("botao-cancelar-endereco").addEventListener("click", async () => {
        if (formularioSujo) {
            const confirmar = await confirmarAcao(
                "Cancelar edição?",
                "As alterações deste endereço serão perdidas. Deseja continuar?"
            );
            if (!confirmar) {
                return;
            }
        }
        preencherFormularioEndereco(null);
        if (typeof formEndereco.atualizarEstadoInicial === "function") {
            formEndereco.atualizarEstadoInicial();
        }
        mostrarAlertaForm(alertaEndereco, "", false);
    });

    formEndereco.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        mostrarAlertaForm(alertaEndereco, "", false);

        if (!validarEndereco()) {
            return;
        }

        const botao = document.getElementById("botao-salvar-endereco");
        botao.disabled = true;

        try {
            const idEndereco = document.getElementById("id-endereco").value;
            const dados = {
                id_cliente: Number(id),
                ...dadosEndereco(),
            };

            let ok;
            let corpo;
            if (idEndereco) {
                dados.id = Number(id);
                dados.id_endereco = Number(idEndereco);
                ({ ok, corpo } = await requisitar("/api/cliente", {
                    method: "PUT",
                    body: JSON.stringify(dados),
                }));
            } else {
                ({ ok, corpo } = await requisitar("/api/endereco", {
                    method: "POST",
                    body: JSON.stringify(dados),
                }));
            }

            if (!ok) {
                mostrarAlertaForm(alertaEndereco, corpo.message || "Falha ao salvar o endereço.", true);
                return;
            }

            preencherFormularioEndereco(null);
            if (typeof formEndereco.atualizarEstadoInicial === "function") {
                formEndereco.atualizarEstadoInicial();
            }
            await atualizarTela();
        } catch (erro) {
            mostrarAlertaForm(alertaEndereco, "Não foi possível conectar ao servidor.", true);
        } finally {
            botao.disabled = false;
        }
    });
}

if (document.getElementById("lista-corpo") && window.location.pathname.startsWith("/clientes")) {
    carregarListaClientes();
}

if (document.getElementById("form-cliente")) {
    carregarFormularioCliente();
}

if (document.getElementById("form-cliente-ver") && window.location.pathname.startsWith("/clientes")) {
    carregarVisualizarCliente();
}
