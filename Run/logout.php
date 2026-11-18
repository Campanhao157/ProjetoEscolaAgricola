
<?php

/*
|--------------------------------------------------------------------------
| ENCERRAMENTO DE SESSÃO - ESCOLA AGRÍCOLA
|--------------------------------------------------------------------------
|
| Este arquivo é responsável por:
|
| 1. Iniciar a sessão atual;
| 2. Limpar os dados armazenados;
| 3. Remover o cookie de sessão;
| 4. Destruir a sessão;
| 5. Redirecionar para a página de login.
|
*/

// Inicia a sessão atual.
session_start();

// Limpa todos os dados armazenados na sessão.
$_SESSION = [];

// Verifica se a sessão utiliza cookies.
if (ini_get('session.use_cookies')) {

    // Recupera as configurações atuais do cookie.
    $params = session_get_cookie_params();

    // Expira o cookie de sessão no navegador.
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destrói a sessão no servidor.
session_destroy();

// Retorna para a página de login.
header('Location: login.html');
exit;

