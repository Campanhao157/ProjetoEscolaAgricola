<?php

/*
|--------------------------------------------------------------------------
| CADASTRO DE USUÁRIOS - ESCOLA AGRÍCOLA
|--------------------------------------------------------------------------
|
| Este arquivo é responsável por:
|
| 1. Iniciar a sessão;
| 2. Gerar o token CSRF;
| 3. Exibir o formulário de cadastro;
| 4. Encaminhar os dados para login.php.
|
*/

// Inicia a sessão.
session_start();

// Gera um token de segurança para o formulário.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mensagens de erro possíveis.
$errors = [
    'security' => 'Falha na validação de segurança. Tente novamente.',
    'invalid'  => 'Verifique os dados informados.',
    'name'     => 'Informe um nome válido.',
    'email'    => 'Informe um endereço de e-mail válido.',
    'password' => 'A senha deve ter entre 8 e 72 caracteres.',
    'match'    => 'As senhas informadas não são iguais.',
    'exists'   => 'Este e-mail já está cadastrado.'
];

// Recupera o erro enviado pelo login.php.
$errorCode = $_GET['error'] ?? '';
$errorMessage = '';

if (is_string($errorCode) && isset($errors[$errorCode])) {
    $errorMessage = $errors[$errorCode];
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#183b2c">
    <title>Criar conta | Escola Agrícola</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="register-page">
        <section class="register-container">
            <div class="register-brand">ESCOLA AGRÍCOLA</div>

            <h1>Criar sua conta</h1>

            <p class="register-description">
                Preencha os campos abaixo para criar seu acesso
                à plataforma da Escola Agrícola.
            </p>

            <?php if ($errorMessage !== ''): ?>
                <div class="register-error" role="alert">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="action" value="register">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="register-field">
                    <label for="name">Nome completo</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Digite seu nome completo"
                        maxlength="120"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="register-field">
                    <label for="email">E-mail</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="seuemail@exemplo.com"
                        maxlength="180"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="register-field">
                    <label for="password">Senha</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Crie uma senha"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="register-field">
                    <label for="confirm_password">Confirmar senha</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Digite a senha novamente"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="register-button">
                    Criar conta
                </button>
            </form>

            <div class="register-footer">
                Já possui uma conta?
                <a href="login.html">Entrar</a>
            </div>
        </section>
    </main>
</body>
</html>
