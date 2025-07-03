<?php
// Inicia a sessão para poder acessá-la
session_start();

// Remove todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão completamente
session_destroy();

// Redireciona o usuário para a página de login
header("Location: /Breno/Bank/src/pages/login.html");
exit();
