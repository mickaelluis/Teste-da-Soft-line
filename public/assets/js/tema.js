const CHAVE_TEMA = "tema";

function temaSalvo() {
    return localStorage.getItem(CHAVE_TEMA) || "claro";
}

function atualizarBotao(tema) {
    const botao = document.getElementById("botao-tema");

    if (!botao) {
        return;
    }

    const escuro = tema === "escuro";
    botao.textContent = escuro ? "Modo claro" : "Modo escuro";
    botao.setAttribute("aria-pressed", escuro ? "true" : "false");
    botao.setAttribute("aria-label", escuro ? "Ativar modo claro" : "Ativar modo escuro");
}

function aplicarTema(tema) {
    document.documentElement.dataset.tema = tema;
    localStorage.setItem(CHAVE_TEMA, tema);
    atualizarBotao(tema);
}

function criarBotaoTema() {
    if (document.getElementById("botao-tema")) {
        return;
    }

    const botao = document.createElement("button");
    botao.type = "button";
    botao.id = "botao-tema";
    botao.className = "tema-botao";
    botao.addEventListener("click", () => {
        const proximo = document.documentElement.dataset.tema === "escuro" ? "claro" : "escuro";
        aplicarTema(proximo);
    });

    document.body.appendChild(botao);
    atualizarBotao(temaSalvo());
}

aplicarTema(temaSalvo());

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", criarBotaoTema);
} else {
    criarBotaoTema();
}
