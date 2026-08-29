# Soft-line

Aplicação PHP para cadastro de usuários, clientes (com endereço) e produtos. Tem interface web e API JSON.

- App: `http://localhost:8000`
- Banco: MySQL 8, database `softline`

---

## Rodar com Docker

O Docker sobe a aplicação (PHP 8.3 + Apache) e o MySQL juntos. Os scripts em `database/scripts` são importados automaticamente na **primeira** inicialização do banco.

### Requisitos

- [Docker Engine](https://docs.docker.com/engine/install/) e [Docker Compose](https://docs.docker.com/compose/install/) (v2, comando `docker compose`)

No Windows e no macOS o [Docker Desktop](https://www.docker.com/products/docker-desktop/) já inclui os dois. No Linux, instale o Engine e o plugin Compose pela documentação da sua distro.

### Passos

1. Clone o repositório e entre na pasta do projeto.

2. Crie o arquivo de ambiente:

```bash
cp app/.env.example app/.env
```

No Windows (PowerShell):

```powershell
copy app\.env.example app\.env
```

O `app/.env.example` já está pronto para Docker:

```
DB_HOST=db
DB_PORT=3306
DB_DATABASE=softline
DB_USERNAME=root
DB_PASSWORD=root
```

3. Suba os containers:

```bash
docker compose up --build
```

Na primeira vez o MySQL pode demorar um pouco enquanto cria as tabelas e as procedures.

4. Abra no navegador: [http://localhost:8000](http://localhost:8000)

| Serviço | Porta no seu PC | Dentro do Docker |
|---------|-----------------|------------------|
| Aplicação | `8000` | `80` |
| MySQL | `3307` | `3306` |

Para conectar no banco com DBeaver, MySQL Workbench etc.: host `127.0.0.1`, porta `3307`, usuário `root`, senha `root`, database `softline`.

### Parar / resetar

```bash
docker compose down
```

Para apagar os dados do banco e importar os scripts de novo:

```bash
docker compose down -v
docker compose up --build
```

---

## Rodar sem Docker

Aqui você instala PHP, Composer e MySQL na máquina e sobe a aplicação localmente.

### Requisitos

- PHP 8.3+ com as extensões `pdo` e `pdo_mysql` habilitadas
- [Composer](https://getcomposer.org/)
- MySQL 8
- Apache com `mod_rewrite` **ou** o servidor embutido do PHP (comandos abaixo)

### 1. Banco de dados

Crie o banco e rode os scripts **nesta ordem** (o número no nome do arquivo indica a sequência):

1. `database/scripts/1_criar_banco.sql`
2. `database/scripts/2_tabelas.sql`
3. `database/scripts/3_procedures_produtos.sql`
4. `database/scripts/4_procedures_clientes.sql`
5. `database/scripts/5_procedures_usuario.sql`
6. `database/scripts/6_procedures_endereco.sql`

Pelo terminal:

```bash
mysql -u root -p < database/scripts/1_criar_banco.sql
mysql -u root -p < database/scripts/2_tabelas.sql
mysql -u root -p < database/scripts/3_procedures_produtos.sql
mysql -u root -p < database/scripts/4_procedures_clientes.sql
mysql -u root -p < database/scripts/5_procedures_usuario.sql
mysql -u root -p < database/scripts/6_procedures_endereco.sql
```

No PowerShell o `<` às vezes não funciona. Use:

```powershell
Get-Content database\scripts\1_criar_banco.sql | mysql -u root -p
```

(repita para os outros arquivos, na mesma ordem)

Ou abra o MySQL Workbench / phpMyAdmin e execute cada arquivo, um de cada vez.

### 2. Arquivo `.env`

```bash
cp app/.env.example app/.env
```

Edite `app/.env` para apontar para o MySQL **da sua máquina** (não use `db`, esse host só existe na rede do Docker):

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=softline
DB_USERNAME=root
DB_PASSWORD=sua_senha_do_mysql
```

Se o MySQL estiver em outra porta, ajuste `DB_PORT`.

### 3. Dependências PHP

Na raiz do projeto:

```bash
composer install
```

### 4. Subir a aplicação

**Opção A — servidor embutido do PHP** (mais simples):

Na raiz do projeto:

```bash
php -S localhost:8000 -t public public/index.php
```

Abra [http://localhost:8000](http://localhost:8000).

**Opção B — Apache** (XAMPP, Laragon, Wamp, etc.):

- DocumentRoot / VirtualHost apontando para a pasta `public/`
- `mod_rewrite` ligado (o arquivo `public/.htaccess` já faz o rewrite para o `index.php`)
- PHP 8.3+ com `pdo_mysql`

A URL depende da sua config (por exemplo `http://localhost/soft-line` ou um vhost). Se não for `http://localhost:8000`, ajuste o `baseUrl` no Postman.

---

## Importar as rotas no Postman (só API)

A pasta `postman/` tem a coleção e um environment prontos. A autenticação da API é **sessão PHP** (cookie `PHPSESSID`), não JWT. O request de Login grava esse cookie na variável `token`.

### Importar

1. Abra o Postman.
2. **Import** (ou `File > Import`).
3. Importe estes dois arquivos:
   - `postman/Soft-line.postman_collection.json` — rotas da API
   - `postman/Soft-line-Local.postman_environment.json` — variáveis (`baseUrl`, email, senha, ids)
4. No canto superior direito, selecione o environment **Soft-line Local**.
5. Em **Cookies**, permita cookies para `localhost:8000` (o Login avisa no console se o `PHPSESSID` não for salvo).

O `baseUrl` padrão é `http://localhost:8000` (Docker ou `php -S` na porta 8000). Se a app estiver em outra URL, altere `baseUrl` no environment.

### Ordem sugerida

1. **Auth → Register** — cria o usuário (`teste@email.com` / `123456` no environment).
2. **Auth → Login** — autentica e salva o `PHPSESSID` em `{{token}}`.
3. **Clientes** e **Produtos** — CRUD (as rotas protegidas já enviam `Cookie: PHPSESSID={{token}}`).
4. **Usuario** — buscar, atualizar, desconectar ou deletar.

Sem o Login antes, as rotas protegidas respondem `401 Não autorizado`.

Variáveis úteis do environment: `email`, `senha`, `token`, `id_cliente`, `id_endereco`, `id_produto`. Depois de criar um cliente/produto, atualize os ids se precisar testar GET/PUT/DELETE por id.
