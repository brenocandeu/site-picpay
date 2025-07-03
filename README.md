# 🏦 Picpay - Projeto de Banco Digital Moderno

![Picpay Showcase](https://placehold.co/1200x400/0a0a0a/21c25e?text=Picpay%20Showcase)

## 📖 Sobre o Projeto

O **Picpay** é um projeto full-stack que simula a interface e as funcionalidades essenciais de um banco digital moderno. O foco foi criar uma experiência de usuário (UX) elegante, tecnológica e segura, com um design responsivo, tema claro/escuro e um backend robusto para gerenciar as operações.

Este projeto foi construído do zero, abrangendo desde a concepção do design e identidade visual até a implementação segura do backend com PHP e MySQL.

---

## ✨ Funcionalidades Principais

O sistema é dividido em duas grandes áreas: a parte pública (páginas de marketing e autenticação) e a parte privada (dashboard do usuário).

### Frontend (Interface do Usuário)

* **Página Inicial (`index.html`):**
    * Design moderno e responsivo com seções bem definidas (Hero, Estatísticas, Features, Sustentabilidade, etc.).
    * Animações sutis com a biblioteca AOS (Animate on Scroll).
    * Efeitos de "glow" e transições elegantes em elementos interativos.
* **Tema Claro e Escuro:**
    * Alternador de tema funcional que salva a preferência do usuário no navegador (`localStorage`).
    * Toda a aplicação, incluindo a área logada, se adapta ao tema escolhido.
* **Fluxo de Autenticação Completo:**
    * **Cadastro em 2 Passos:** O usuário preenche os dados e recebe um código de verificação por e-mail para ativar a conta, prevenindo contas falsas.
    * **Login Seguro:** Autenticação via CPF e senha.
    * **Recuperação de Senha:** Fluxo de 3 passos (CPF -> Código por E-mail -> Nova Senha) para garantir que apenas o dono da conta possa redefinir a senha.
    * **Validação de Formulário:** Validação em tempo real e no envio, com feedback claro para o usuário.
    * **Máscaras de Input:** Formatação automática para campos como CPF, data e celular.
* **Dashboard do Usuário (`dashboard.php`):**
    * Interface no estilo SPA (Single Page Application) com navegação dinâmica sem recarregamento de página.
    * **Página Inicial:** Resumo de saldo (com função de ocultar/mostrar), atalhos para ações rápidas e histórico de transações.
    * **Página de Perfil:** Interface organizada em cards onde o usuário pode visualizar seus dados e alterar a senha de forma segura.
    * **Assistente de IA:** Simulação de um chatbot flutuante para ajuda ao usuário.

### Backend (PHP)

* **Arquitetura de API:** O backend foi construído como uma API RESTful, separando completamente as responsabilidades do frontend.
* **Segurança:**
    * **Senhas Criptografadas:** Utilização de `password_hash()` e `password_verify()` (algoritmo BCRYPT), o padrão mais seguro do PHP.
    * **Prevenção de SQL Injection:** Uso de PDO com *Prepared Statements* em todas as consultas ao banco de dados.
    * **Proteção de Páginas:** Sistema de Sessões PHP para garantir que apenas usuários logados possam acessar o dashboard.
    * **Validação no Servidor:** Todos os dados recebidos pela API são validados no lado do servidor.
* **Envio de E-mail:** Integração com a biblioteca **PHPMailer** via **Composer** para o envio confiável de e-mails transacionais (verificação de conta e recuperação de senha).

---

## 🛠️ Tecnologias Utilizadas

* **Frontend:**
    * HTML5
    * CSS3 (com Variáveis CSS para o Theming)
    * JavaScript (ES6+)
    * Bootstrap 5 (para o grid e componentes base)
    * [Animate on Scroll (AOS)](https://michalsnik.github.io/aos/)
* **Backend:**
    * PHP 8+
    * MySQL / MariaDB
    * [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* **Gerenciador de Pacotes:**
    * Composer

---

## 📂 Estrutura de Pastas

O projeto segue uma estrutura organizada e escalável:

```
/BANK/
├── api/            # Backend (PHP)
│   ├── config/
│   │   └── database.php
│   ├── login.php
│   ├── register.php
│   ├── verify-email.php
│   ├── request-reset.php
│   ├── verify-code.php
│   ├── change-password.php
│   ├── logout.php
│   ├── update-profile.php
│   └── reset-password.php
│
└── src/            # Frontend (HTML, CSS, JS)
    ├── assets/
    ├── css/
    │   ├── global.css
    │   └── pages/
    │       ├── auth.css
    │       ├── dashboard.css
    │       └── pagina_inicial.css
    ├── pages/
    │   ├── cadastro.html
    │   ├── dashboard.php  # Note que este é .php para o guarda de autenticação
    │   ├── esqueci_senha.html
    │   └── login.html
    ├── scripts/
    │   └── commons/
    │       ├── auth.js
    │       ├── dashboard.js
    │       ├── pagina_inicial.js
    │       └── esqueci_senha.js
    └── index.html
```

---

## 🚀 Como Executar o Projeto Localmente

Siga os passos abaixo para rodar o projeto em seu ambiente local.

### Pré-requisitos
* Um ambiente de servidor local como **XAMPP** ou WAMP (com Apache, MySQL e PHP).
* **Composer** instalado globalmente no seu sistema.

### Passos
1.  **Clone o Repositório:**
    ```bash
    git clone https://github.com/brenocandeu/site-picpay.git
    ```

2.  **Mova para o `htdocs`:**
    * Mova a pasta inteira do projeto para dentro da pasta `htdocs` do seu XAMPP (geralmente em `C:\xampp\htdocs\`).

3.  **Instale as Dependências do PHP:**
    * Abra um terminal na pasta raiz do projeto (`/BANK/`).
    * Rode o comando:
        ```bash
        composer require phpmailer/phpmailer
        ```
    * Isso irá instalar o PHPMailer e criar a pasta `vendor`.

4.  **Crie e Configure o Banco de Dados:**
    * Inicie os serviços **Apache** e **MySQL** no seu painel do XAMPP.
    * Acesse `http://localhost/phpmyadmin` e crie um novo banco de dados (ex: `bank`).
    * Execute o script SQL abaixo para criar a tabela `users` com todas as colunas necessárias:
        ```sql
        CREATE TABLE users (
          id int(11) NOT NULL AUTO_INCREMENT,
          cpf varchar(14) NOT NULL,
          full_name varchar(255) NOT NULL,
          birth_date date NOT NULL,
          phone varchar(20) NOT NULL,
          email varchar(255) NOT NULL,
          password_hash varchar(255) NOT NULL,
          created_at timestamp NOT NULL DEFAULT current_timestamp(),
          reset_code varchar(6) DEFAULT NULL,
          reset_expires_at datetime DEFAULT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY cpf (cpf),
          UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ```

5.  **Configure o Backend:**
    * No arquivo `api/config/database.php`, ajuste as credenciais (`$host`, `$db_name`, `$username`, `$password`) para corresponder à sua configuração do MySQL.
    * Nos arquivos `api/register.php` e `api/request-reset.php`, insira as credenciais do seu e-mail e a **Senha de App** gerada para o PHPMailer funcionar.

6.  **Acesse o Projeto:**
    * Abra seu navegador e acesse: `http://localhost/BANK/src/`

---

## 📸 Screenshots

**Página Inicial**      
![Landing Page](https://placehold.co/800x450/0a0a0a/21c25e?text=Landing+Page)

**Painel de Login**     
![Login Panel](https://placehold.co/800x450/0a0a0a/21c25e?text=Login+Panel)

**Dashboard do Usuário**
![User Dashboard](https://placehold.co/800x450/0a0a0a/21c25e?text=Dashboard)

---

## 👨‍💻 Autor

**Breno Candeu**

* LinkedIn: [https://www.linkedin.com/in/brenocandeu/](https://www.linkedin.com/in/brenocandeu/)
* GitHub: [https://github.com/brenocandeu](https://github.com/brenocandeu)
* Email: [brenocandeu16@gmail.com](mailto:brenocandeu16@gmail.com)

---

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.
