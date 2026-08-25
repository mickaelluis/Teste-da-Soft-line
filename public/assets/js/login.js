function mostrarErro(input, elementoErro, mensagem) {
    input.classList.toggle("invalid", Boolean(mensagem));
    elementoErro.textContent = mensagem;
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
                <h2 class="alerta__titulo">${titulo}</h2>
                <p class="alerta__texto">${texto}</p>
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

async function enviarJson(url, dados) {
    const resposta = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(dados),
    });

    const corpo = await resposta.json().catch(() => ({}));
    return { ok: resposta.ok, status: resposta.status, corpo };
}

const formCadastro = document.getElementById("form-cadastro");

if (formCadastro) {
    const nome = document.getElementById("nome");
    const email = document.getElementById("email");
    const senha = document.getElementById("senha");
    const confirmarSenha = document.getElementById("confirmar-senha");
    const erroNome = document.getElementById("erro-nome");
    const erroEmail = document.getElementById("erro-email");
    const erroSenha = document.getElementById("erro-senha");
    const erroConfirmarSenha = document.getElementById("erro-confirmar-senha");
    const botao = formCadastro.querySelector('button[type="submit"]');

    function validarNome() {
        const valor = nome.value.trim();

        if (!valor) {
            mostrarErro(nome, erroNome, "Informe o nome.");
            return false;
        }

        mostrarErro(nome, erroNome, "");
        return true;
    }

    function validarEmail() {
        const valor = email.value.trim();

        if (!valor) {
            mostrarErro(email, erroEmail, "Informe o e-mail.");
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            mostrarErro(email, erroEmail, "Informe um e-mail válido.");
            return false;
        }

        mostrarErro(email, erroEmail, "");
        return true;
    }

    function validarSenha() {
        const valor = senha.value;

        if (!valor) {
            mostrarErro(senha, erroSenha, "Informe a senha.");
            return false;
        }

        if (valor.length < 4) {
            mostrarErro(senha, erroSenha, "A senha deve ter no mínimo 4 caracteres.");
            return false;
        }

        mostrarErro(senha, erroSenha, "");
        return true;
    }

    function validarConfirmarSenha() {
        const valor = confirmarSenha.value;

        if (!valor) {
            mostrarErro(confirmarSenha, erroConfirmarSenha, "Confirme a senha.");
            return false;
        }

        if (valor !== senha.value) {
            mostrarErro(confirmarSenha, erroConfirmarSenha, "As senhas não coincidem.");
            return false;
        }

        mostrarErro(confirmarSenha, erroConfirmarSenha, "");
        return true;
    }

    nome.addEventListener("blur", validarNome);
    email.addEventListener("blur", validarEmail);
    senha.addEventListener("blur", validarSenha);
    confirmarSenha.addEventListener("blur", validarConfirmarSenha);

    formCadastro.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const nomeOk = validarNome();
        const emailOk = validarEmail();
        const senhaOk = validarSenha();
        const confirmarOk = validarConfirmarSenha();

        if (!(nomeOk && emailOk && senhaOk && confirmarOk)) {
            return;
        }

        botao.disabled = true;

        try {
            const { ok, corpo } = await enviarJson("/api/register", {
                name: nome.value.trim(),
                email: email.value.trim(),
                senha: senha.value,
                confirm_senha: confirmarSenha.value,
            });

            if (!ok) {
                mostrarErro(email, erroEmail, corpo.message || "Falha ao cadastrar.");
                return;
            }

            await mostrarAlerta(
                "Cadastro concluído",
                "Usuário criado, faça login para acessar sua conta."
            );
            window.location.href = "/login";
        } catch (erro) {
            mostrarErro(email, erroEmail, "Não foi possível conectar ao servidor.");
        } finally {
            botao.disabled = false;
        }
    });
}

const formLogin = document.getElementById("form-login");

if (formLogin) {
    const usuario = document.getElementById("usuario");
    const senhaLogin = document.getElementById("senha-login");
    const erroUsuario = document.getElementById("erro-usuario");
    const erroSenhaLogin = document.getElementById("erro-senha-login");
    const botao = formLogin.querySelector('button[type="submit"]');

    function validarUsuario() {
        const valor = usuario.value.trim();

        if (!valor) {
            mostrarErro(usuario, erroUsuario, "Informe o usuário.");
            return false;
        }

        mostrarErro(usuario, erroUsuario, "");
        return true;
    }

    function validarSenhaLogin() {
        const valor = senhaLogin.value;

        if (!valor) {
            mostrarErro(senhaLogin, erroSenhaLogin, "Informe a senha.");
            return false;
        }

        mostrarErro(senhaLogin, erroSenhaLogin, "");
        return true;
    }

    usuario.addEventListener("blur", validarUsuario);
    senhaLogin.addEventListener("blur", validarSenhaLogin);

    formLogin.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const usuarioOk = validarUsuario();
        const senhaOk = validarSenhaLogin();

        if (!(usuarioOk && senhaOk)) {
            return;
        }

        botao.disabled = true;

        try {
            const { ok, corpo } = await enviarJson("/api/login", {
                email: usuario.value.trim(),
                senha: senhaLogin.value,
            });

            if (!ok) {
                mostrarErro(usuario, erroUsuario, corpo.message || "Falha ao fazer login.");
                return;
            }

            window.location.href = "/home";
        } catch (erro) {
            mostrarErro(usuario, erroUsuario, "Não foi possível conectar ao servidor.");
        } finally {
            botao.disabled = false;
        }
    });
}
