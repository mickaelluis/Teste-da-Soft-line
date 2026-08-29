function parametroUrl(nome) {
    return new URLSearchParams(window.location.search).get(nome);
}

function escapar(texto) {
    const elemento = document.createElement("div");
    elemento.textContent = texto == null ? "" : String(texto);
    return elemento.innerHTML;
}

function valorRegistro(registro, chave) {
    if (!registro || !chave) {
        return "";
    }

    if (registro[chave] != null && registro[chave] !== "") {
        return registro[chave];
    }

    const encontrada = Object.keys(registro).find(
        (nome) => nome.toLowerCase() === chave.toLowerCase()
    );

    return encontrada != null ? registro[encontrada] : "";
}

function htmlDocumentoLista(documento) {
    const digitos = soDigitos(documento);
    if (!digitos) {
        return "";
    }

    return `
        <div class="campo-documento campo-documento--lista" data-documento="${escapar(digitos)}" data-visivel="false">
            <span class="campo-documento__valor">${escapar(mascararDocumentoOculto(digitos))}</span>
            <button type="button" class="campo-documento__toggle" aria-label="Mostrar documento"></button>
        </div>
    `;
}

function mostrarErro(input, elementoErro, mensagem) {
    if (input) {
        input.classList.toggle("invalid", Boolean(mensagem));
    }
    if (elementoErro) {
        elementoErro.textContent = mensagem;
    }
}

function mostrarAlertaForm(elemento, mensagem, erro) {
    if (!mensagem) {
        return;
    }

    mostrarToast(mensagem, { erro: Boolean(erro) });
}

function obterContainerToast() {
    let container = document.getElementById("toast-container");
    if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container";
        container.setAttribute("aria-live", "polite");
        document.body.appendChild(container);
    }
    return container;
}

function fecharToast(toast) {
    if (!toast || toast.dataset.fechando === "true") {
        return;
    }

    toast.dataset.fechando = "true";
    toast.classList.add("toast--saindo");
    toast.addEventListener("animationend", () => toast.remove(), { once: true });
}

function mostrarToast(mensagem, opcoes = {}) {
    const { erro = false, duracao = 5000 } = opcoes;
    if (!mensagem) {
        return;
    }

    const container = obterContainerToast();
    const toast = document.createElement("div");
    toast.className = `toast${erro ? " toast--erro" : " toast--sucesso"}`;
    toast.setAttribute("role", "alert");
    toast.innerHTML = `
        <p class="toast__texto">${escapar(mensagem)}</p>
        <button type="button" class="toast__fechar" aria-label="Fechar">&times;</button>
    `;

    container.appendChild(toast);

    const timer = setTimeout(() => fecharToast(toast), duracao);
    toast.querySelector(".toast__fechar").addEventListener("click", () => {
        clearTimeout(timer);
        fecharToast(toast);
    });
}

function mostrarAlerta(titulo, texto) {
    return new Promise((resolver) => {
        const overlay = document.createElement("div");
        overlay.className = "alerta-overlay";
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        overlay.innerHTML = `
            <div class="alerta">
                <div class="alerta__icone" aria-hidden="true">✓</div>
                <h2 class="alerta__titulo">${escapar(titulo)}</h2>
                <p class="alerta__texto">${escapar(texto)}</p>
                <button type="button" class="botao" id="alerta-ok">OK</button>
            </div>
        `;

        document.body.appendChild(overlay);

        const botaoOk = overlay.querySelector("#alerta-ok");
        botaoOk.focus();

        botaoOk.addEventListener("click", () => {
            overlay.remove();
            resolver();
        });
    });
}

function confirmarAcao(titulo, texto) {
    return new Promise((resolver) => {
        const overlay = document.createElement("div");
        overlay.className = "alerta-overlay";
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        overlay.innerHTML = `
            <div class="alerta">
                <div class="alerta__icone" aria-hidden="true">!</div>
                <h2 class="alerta__titulo">${escapar(titulo)}</h2>
                <p class="alerta__texto">${escapar(texto)}</p>
                <button type="button" class="botao" id="alerta-ok">Confirmar</button>
                <button type="button" class="botao botao--secundario" id="alerta-cancelar">Cancelar</button>
            </div>
        `;

        document.body.appendChild(overlay);

        overlay.querySelector("#alerta-ok").addEventListener("click", () => {
            overlay.remove();
            resolver(true);
        });

        overlay.querySelector("#alerta-cancelar").addEventListener("click", () => {
            overlay.remove();
            resolver(false);
        });
    });
}

async function requisitar(url, opcoes = {}) {
    const resposta = await fetch(url, {
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
            ...(opcoes.headers || {}),
        },
        ...opcoes,
    });

    if (resposta.status === 401) {
        window.location.href = "/login";
        throw new Error("Não autorizado");
    }

    const corpo = await resposta.json().catch(() => ({}));
    return { ok: resposta.ok, status: resposta.status, corpo };
}

function soDigitos(valor) {
    return String(valor || "").replace(/\D/g, "");
}

function mascararDocumento(valor) {
    const digitos = soDigitos(valor).slice(0, 14);

    if (digitos.length <= 11) {
        return digitos
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d)/, "$1.$2")
            .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    }

    return digitos
        .replace(/(\d{2})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1/$2")
        .replace(/(\d{4})(\d{1,2})$/, "$1-$2");
}

function mascararDocumentoOculto(valor) {
    const digitos = soDigitos(valor);
    if (!digitos) {
        return "";
    }

    return mascararDocumento(digitos).replace(/\d/g, "*");
}

function iconeOlhoAberto() {
    return `<svg class="campo-documento__icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>`;
}

function iconeOlhoFechado() {
    return `<svg class="campo-documento__icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"/><line x1="3" y1="3" x2="21" y2="21"/></svg>`;
}

function obterDigitosDocumento(input) {
    const container = input?.closest(".campo-documento");
    if (container && container.dataset.documento != null) {
        return container.dataset.documento;
    }
    return soDigitos(input?.value);
}

function inputDocumentoEmEdicao(input) {
    if (!input) {
        return false;
    }
    if (input.closest(".ficha--editavel")) {
        return !input.hidden;
    }
    return true;
}

function atualizarExibicaoDocumentoCampo(container) {
    if (!container) {
        return;
    }

    const digitos = container.dataset.documento || "";
    const visivel = container.dataset.visivel === "true";
    const texto = container.querySelector(".ficha__valor");
    const textoLista = container.querySelector(".campo-documento__valor");
    const input = container.querySelector('input[data-mascara="documento"]');
    const botao = container.querySelector(".campo-documento__toggle");
    const exibicao = visivel ? mascararDocumento(digitos) : mascararDocumentoOculto(digitos);

    if (texto) {
        texto.textContent = exibicao;
    }

    if (textoLista) {
        textoLista.textContent = exibicao;
    }

    if (input) {
        input.value = exibicao;
        input.readOnly = !visivel && !inputDocumentoEmEdicao(input);
    }

    if (botao) {
        botao.innerHTML = visivel ? iconeOlhoFechado() : iconeOlhoAberto();
        botao.setAttribute("aria-label", visivel ? "Ocultar documento" : "Mostrar documento");
        botao.setAttribute("aria-pressed", visivel ? "true" : "false");
        botao.classList.toggle("is-visivel", visivel);
    }
}

function aplicarDigitosDocumentoInput(container, input, digitos) {
    const normalizado = soDigitos(digitos).slice(0, 14);
    container.dataset.documento = normalizado;
    const visivel = container.dataset.visivel === "true";
    input.value = visivel ? mascararDocumento(normalizado) : mascararDocumentoOculto(normalizado);
}

function processarEntradaDocumentoOculto(container, input, evento) {
    let digitos = container.dataset.documento || "";

    if (
        (evento.inputType === "insertText" || evento.inputType === "insertFromPaste") &&
        evento.data
    ) {
        const novos = soDigitos(evento.data);
        if (!novos) {
            evento.preventDefault();
            return;
        }
        digitos = (digitos + novos).slice(0, 14);
    } else if (evento.inputType === "insertReplacementText" && evento.data) {
        digitos = soDigitos(evento.data).slice(0, 14);
    } else if (
        evento.inputType === "deleteContentBackward" ||
        evento.inputType === "deleteContentForward"
    ) {
        digitos = digitos.slice(0, -1);
    } else {
        return;
    }

    evento.preventDefault();
    aplicarDigitosDocumentoInput(container, input, digitos);
}

function definirDocumentoCampo(container, digitos, visivel = false) {
    if (!container) {
        return;
    }

    container.dataset.documento = soDigitos(digitos);
    container.dataset.visivel = visivel ? "true" : "false";
    atualizarExibicaoDocumentoCampo(container);
}

function ligarCampoDocumento(container) {
    if (!container || container.dataset.documentoLigado === "true") {
        return;
    }

    container.dataset.documentoLigado = "true";

    const botao = container.querySelector(".campo-documento__toggle");
    const input = container.querySelector('input[data-mascara="documento"]');

    if (input && container.dataset.documento == null) {
        container.dataset.documento = soDigitos(input.value).slice(0, 14);
    }

    atualizarExibicaoDocumentoCampo(container);

    if (botao) {
        botao.addEventListener("click", async () => {
            const visivel = container.dataset.visivel === "true";

            if (!visivel) {
                const confirmar = await confirmarAcao(
                    "Mostrar documento",
                    "Isso irá mostrar o documento do cliente!"
                );
                if (!confirmar) {
                    return;
                }
            }

            container.dataset.visivel = visivel ? "false" : "true";
            atualizarExibicaoDocumentoCampo(container);
        });
    }

    if (input) {
        input.addEventListener("beforeinput", (evento) => {
            if (input.readOnly || container.dataset.visivel === "true") {
                return;
            }
            processarEntradaDocumentoOculto(container, input, evento);
        });

        input.addEventListener("input", () => {
            if (input.readOnly || container.dataset.visivel !== "true") {
                return;
            }
            aplicarDigitosDocumentoInput(container, input, soDigitos(input.value));
        });
    }
}

function ligarCamposDocumento() {
    document.querySelectorAll(".campo-documento").forEach(ligarCampoDocumento);
}

function mascararCep(valor) {
    return soDigitos(valor).slice(0, 8).replace(/(\d{5})(\d)/, "$1-$2");
}

async function buscarEnderecoPorCep(cep, opcoes = {}) {
    const digitos = soDigitos(cep);
    if (digitos.length !== 8) {
        return null;
    }

    const timeout = opcoes.timeout ?? 5000;

    async function fetchJson(url) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeout);

        try {
            const resposta = await fetch(url, { signal: controller.signal });
            if (!resposta.ok) {
                return null;
            }
            return await resposta.json();
        } catch {
            return null;
        } finally {
            clearTimeout(timer);
        }
    }

    const viaCep = await fetchJson(`https://viacep.com.br/ws/${digitos}/json/`);
    if (viaCep && !viaCep.erro) {
        return {
            rua: viaCep.logradouro ?? "",
            bairro: viaCep.bairro ?? "",
            cidade: viaCep.localidade ?? "",
            estado: viaCep.uf ?? "",
        };
    }

    const brasilApi = await fetchJson(`https://brasilapi.com.br/api/cep/v1/${digitos}`);
    if (brasilApi && brasilApi.cep) {
        return {
            rua: brasilApi.street ?? "",
            bairro: brasilApi.neighborhood ?? "",
            cidade: brasilApi.city ?? "",
            estado: brasilApi.state ?? "",
        };
    }

    return null;
}

function decimalParaNumero(valor) {
    if (valor == null || valor === "") {
        return NaN;
    }

    const texto = String(valor).trim();
    if (!texto) {
        return NaN;
    }

    const normalizado = texto.includes(",")
        ? texto.replace(/\./g, "").replace(",", ".")
        : texto;

    return Number(normalizado);
}

function formatarDecimal(valor, casas) {
    const numero = typeof valor === "number" ? valor : decimalParaNumero(valor);
    if (!Number.isFinite(numero)) {
        return "";
    }

    return numero.toLocaleString("pt-BR", {
        minimumFractionDigits: casas,
        maximumFractionDigits: casas,
    });
}

function mascararDecimalDigitacao(valor, casas, maxInteiros = 8) {
    let texto = String(valor || "").replace(/[^\d,]/g, "");
    const partes = texto.split(",");

    if (partes.length > 2) {
        texto = partes[0] + "," + partes.slice(1).join("");
    }

    let [inteiro = "", decimal] = texto.split(",");
    inteiro = inteiro.slice(0, maxInteiros);

    if (decimal !== undefined) {
        decimal = decimal.slice(0, casas);
        return `${inteiro},${decimal}`;
    }

    return inteiro;
}

function limiteDecimal(casas, maxInteiros) {
    return Math.pow(10, maxInteiros) - Math.pow(10, -casas);
}

function formatarDecimalLimitado(valor, casas, maxInteiros) {
    const numero = decimalParaNumero(valor);
    if (!Number.isFinite(numero)) {
        return mascararDecimalDigitacao(valor, casas, maxInteiros);
    }

    const limitado = Math.min(numero, limiteDecimal(casas, maxInteiros));
    return formatarDecimal(limitado, casas);
}

function obterLimitesDecimalInput(input, tipo) {
    const casas = Number(input?.dataset.decimais || (tipo === "peso" ? 3 : 2));
    const maxInteiros = Number(input?.dataset.maxInteiros || (tipo === "peso" ? 7 : 8));
    return { casas, maxInteiros };
}

function ligarMascaras() {
    document.querySelectorAll("[data-mascara]").forEach((input) => {
        const tipo = input.dataset.mascara;

        input.addEventListener("input", () => {
            if (input.readOnly) {
                return;
            }
            if (tipo === "documento") {
                if (input.closest(".campo-documento")) {
                    return;
                }
                input.value = mascararDocumento(input.value);
            }
            if (tipo === "cep") {
                input.value = mascararCep(input.value);
            }
            if (tipo === "moeda") {
                const { casas, maxInteiros } = obterLimitesDecimalInput(input, tipo);
                input.value = mascararDecimalDigitacao(input.value, casas, maxInteiros);
            }
            if (tipo === "peso") {
                const { casas, maxInteiros } = obterLimitesDecimalInput(input, tipo);
                input.value = mascararDecimalDigitacao(input.value, casas, maxInteiros);
            }
        });

        input.addEventListener("blur", () => {
            if (!input.value) {
                return;
            }
            if (tipo === "moeda") {
                const { casas, maxInteiros } = obterLimitesDecimalInput(input, tipo);
                input.value = formatarDecimalLimitado(input.value, casas, maxInteiros);
            }
            if (tipo === "peso") {
                const { casas, maxInteiros } = obterLimitesDecimalInput(input, tipo);
                input.value = formatarDecimalLimitado(input.value, casas, maxInteiros);
            }
        });
    });
}

function campoObrigatorio(input, elementoErro, mensagem) {
    const valor = input.value.trim();
    if (!valor) {
        mostrarErro(input, elementoErro, mensagem);
        return false;
    }
    mostrarErro(input, elementoErro, "");
    return true;
}

function campoInteiro(input, elementoErro) {
    const valor = input.value.trim();
    if (!valor) {
        mostrarErro(input, elementoErro, "Informe o código.");
        return false;
    }
    if (!/^\d+$/.test(valor)) {
        mostrarErro(input, elementoErro, "O código deve ser um número inteiro.");
        return false;
    }
    mostrarErro(input, elementoErro, "");
    return true;
}

function campoDecimal(input, elementoErro, rotulo) {
    const valor = input.value.trim();
    if (!valor) {
        mostrarErro(input, elementoErro, `Informe o ${rotulo}.`);
        return false;
    }

    const tipo = input.dataset.mascara === "peso" ? "peso" : "moeda";
    const { casas, maxInteiros } = obterLimitesDecimalInput(input, tipo);
    const numero = decimalParaNumero(valor);
    const maximo = limiteDecimal(casas, maxInteiros);

    if (!Number.isFinite(numero) || numero <= 0) {
        mostrarErro(input, elementoErro, `O ${rotulo} deve ser um número maior que zero.`);
        return false;
    }

    if (numero > maximo) {
        mostrarErro(
            input,
            elementoErro,
            `O ${rotulo} deve ser no máximo ${formatarDecimal(maximo, casas)}.`
        );
        return false;
    }

    mostrarErro(input, elementoErro, "");
    return true;
}

function alternarEdicaoFicha(form, editando) {
    if (!form) {
        return;
    }

    form.classList.toggle("is-editando", editando);
    form.querySelectorAll(".ficha__valor").forEach((elemento) => {
        elemento.hidden = editando;
    });
    form.querySelectorAll(".ficha__input").forEach((elemento) => {
        elemento.hidden = !editando;
    });
    form.querySelectorAll(".campo-documento").forEach(atualizarExibicaoDocumentoCampo);
}

function marcarNav() {
    const caminho = window.location.pathname;

    document.querySelectorAll("[data-nav]").forEach((link) => {
        const chave = link.dataset.nav;
        const ativo =
            (chave === "home" && caminho === "/home") ||
            (chave === "produtos" && caminho.startsWith("/produtos")) ||
            (chave === "clientes" && caminho.startsWith("/clientes")) ||
            (chave === "usuario" && caminho.startsWith("/usuario"));

        link.classList.toggle("is-ativo", ativo);
    });
}

let formularioSujo = false;
let liberarNavegacao = false;

function marcarFormularioSujo(sujo) {
    formularioSujo = Boolean(sujo);
}

function liberarSaidaFormulario() {
    formularioSujo = false;
    liberarNavegacao = true;
}

function snapshotFormulario(form) {
    const dados = new FormData(form);
    const itens = [];
    dados.forEach((valor, chave) => {
        itens.push(`${chave}=${String(valor)}`);
    });
    return itens.join("&");
}

function vigiarFormulario(form) {
    if (!form) {
        return;
    }

    let estadoInicial = snapshotFormulario(form);

    function atualizarSujidade() {
        formularioSujo = snapshotFormulario(form) !== estadoInicial;
    }

    form.addEventListener("input", atualizarSujidade);
    form.addEventListener("change", atualizarSujidade);

    form.atualizarEstadoInicial = () => {
        estadoInicial = snapshotFormulario(form);
        formularioSujo = false;
    };
}

async function confirmarSaidaComAlteracoes() {
    if (!formularioSujo || liberarNavegacao) {
        return true;
    }

    return confirmarAcao(
        "Sair sem salvar?",
        "Existem alterações que ainda não foram salvas. Deseja sair mesmo assim?"
    );
}

async function sair() {
    const confirmar = await confirmarAcao(
        "Sair da conta",
        "Deseja realmente sair e encerrar a sessão?"
    );

    if (!confirmar) {
        return;
    }

    if (!(await confirmarSaidaComAlteracoes())) {
        return;
    }

    liberarSaidaFormulario();

    try {
        await requisitar("/api/desconectar", { method: "POST", body: "{}" });
    } catch (erro) {
        // segue para o login mesmo se a API falhar
    }
    window.location.href = "/login";
}

document.addEventListener("DOMContentLoaded", () => {
    marcarNav();
    ligarMascaras();
    ligarCamposDocumento();

    const botaoSair = document.getElementById("botao-sair");
    if (botaoSair) {
        botaoSair.addEventListener("click", sair);
    }

    document.addEventListener("click", async (evento) => {
        const link = evento.target.closest("a[href]");
        if (!link || !formularioSujo || liberarNavegacao) {
            return;
        }

        const href = link.getAttribute("href");
        if (!href || href.startsWith("#") || href.startsWith("javascript:")) {
            return;
        }

        evento.preventDefault();
        const confirmar = await confirmarSaidaComAlteracoes();
        if (!confirmar) {
            return;
        }

        liberarSaidaFormulario();
        window.location.href = link.href;
    }, true);

    window.addEventListener("beforeunload", (evento) => {
        if (!formularioSujo || liberarNavegacao) {
            return;
        }
        evento.preventDefault();
        evento.returnValue = "";
    });
});
