async function carregarUsuario() {
    const form = document.getElementById("form-usuario");
    const alerta = document.getElementById("form-alerta");
    const botao = document.getElementById("botao-salvar");
    const botaoExcluir = document.getElementById("botao-excluir-conta");
    const nome = document.getElementById("nome");
    const email = document.getElementById("email");
    const senha = document.getElementById("senha");
    const confirmarSenha = document.getElementById("confirmar-senha");

    try {
        const { ok, corpo } = await requisitar("/api/usuario");
        if (!ok || !corpo.data) {
            mostrarAlertaForm(alerta, corpo.message || "Falha ao carregar o usuário.", true);
            botao.disabled = true;
            return;
        }

        nome.value = corpo.data.nome ?? "";
        email.value = corpo.data.email ?? "";
    } catch (erro) {
        mostrarAlertaForm(alerta, "Não foi possível conectar ao servidor.", true);
        botao.disabled = true;
        return;
    }

    vigiarFormulario(form);

    function validarEmail() {
        const valor = email.value.trim();
        if (!valor) {
            mostrarErro(email, document.getElementById("erro-email"), "Informe o e-mail.");
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            mostrarErro(email, document.getElementById("erro-email"), "Informe um e-mail válido.");
            return false;
        }
        mostrarErro(email, document.getElementById("erro-email"), "");
        return true;
    }

    function validarSenha() {
        const valor = senha.value;
        const confirmacao = confirmarSenha.value;

        if (!valor && !confirmacao) {
            mostrarErro(senha, document.getElementById("erro-senha"), "");
            mostrarErro(confirmarSenha, document.getElementById("erro-confirmar-senha"), "");
            return true;
        }

        if (valor.length < 4) {
            mostrarErro(senha, document.getElementById("erro-senha"), "A senha deve ter no mínimo 4 caracteres.");
            return false;
        }

        mostrarErro(senha, document.getElementById("erro-senha"), "");

        if (valor !== confirmacao) {
            mostrarErro(confirmarSenha, document.getElementById("erro-confirmar-senha"), "As senhas não coincidem.");
            return false;
        }

        mostrarErro(confirmarSenha, document.getElementById("erro-confirmar-senha"), "");
        return true;
    }

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        mostrarAlertaForm(alerta, "", false);

        const nomeOk = campoObrigatorio(nome, document.getElementById("erro-nome"), "Informe o nome.");
        const emailOk = validarEmail();
        const senhaOk = validarSenha();

        if (!(nomeOk && emailOk && senhaOk)) {
            return;
        }

        botao.disabled = true;

        try {
            const dados = {
                nome: nome.value.trim(),
                email: email.value.trim(),
            };

            if (senha.value) {
                dados.senha = senha.value;
                dados.confirm_senha = confirmarSenha.value;
            }

            const { ok, corpo } = await requisitar("/api/usuario", {
                method: "PUT",
                body: JSON.stringify(dados),
            });

            if (!ok) {
                mostrarAlertaForm(alerta, corpo.message || "Falha ao atualizar o usuário.", true);
                return;
            }

            senha.value = "";
            confirmarSenha.value = "";
            if (typeof form.atualizarEstadoInicial === "function") {
                form.atualizarEstadoInicial();
            }
            await mostrarAlerta("Usuário atualizado", corpo.message || "Dados salvos com sucesso.");
        } catch (erro) {
            mostrarAlertaForm(alerta, "Não foi possível conectar ao servidor.", true);
        } finally {
            botao.disabled = false;
        }
    });

    botaoExcluir.addEventListener("click", async () => {
        const confirmar = await confirmarAcao(
            "Excluir conta",
            "Todos os produtos e clientes deste usuário serão removidos. Deseja continuar?"
        );

        if (!confirmar) {
            return;
        }

        try {
            const { ok, corpo } = await requisitar("/api/usuario", { method: "DELETE" });
            if (!ok) {
                mostrarAlertaForm(alerta, corpo.message || "Falha ao excluir a conta.", true);
                return;
            }
            liberarSaidaFormulario();
            window.location.href = "/login";
        } catch (erro) {
            mostrarAlertaForm(alerta, "Não foi possível conectar ao servidor.", true);
        }
    });
}

if (document.getElementById("form-usuario")) {
    carregarUsuario();
}
