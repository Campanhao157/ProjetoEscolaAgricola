
<?php
/*
|--------------------------------------------------------------------------
| SISTEMA DE AUTENTICAÇÃO - ESCOLA AGRÍCOLA
|--------------------------------------------------------------------------
| Responsabilidades:
| - Conectar ao banco de dados SQLite.
| - Realizar login de usuários.
| - Cadastrar novos usuários.
| - Validar dados e proteger formulários com CSRF.
| - Diferenciar administradores de usuários comuns.
| - Gerenciar sessões.
*/

// ==========================================================
// 1. CONFIGURAÇÕES DE SESSÃO
// ==========================================================

$isHttps = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// ==========================================================
// 2. CONFIGURAÇÕES DO SISTEMA
// ==========================================================

$loginPage = 'login.html';
$registerPage = 'cadastro.php';

$destinations = [
    'admin' => 'mainAdm.php',
    'user'  => 'mainUsuario.php'
];

// ==========================================================
// 3. FUNÇÃO DE REDIRECIONAMENTO
// ==========================================================

function redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

// ==========================================================
// 4. CONEXÃO COM O BANCO DE DADOS
// ==========================================================

try {
    $databasePath = defined('SCHOOL_DB_PATH')
        ? SCHOOL_DB_PATH
        : __DIR__ . '/database.sqlite';

    $pdo = new PDO('sqlite:' . $databasePath);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    // Verifica se a tabela de usuários existe.
    $tableExists = $pdo->query("
        SELECT name
        FROM sqlite_master
        WHERE type = 'table'
        AND name = 'users'
    ")->fetch();

    // Se não existir, tenta inicializar o banco pelo data.sql.
    if (!$tableExists) {
        $sqlFile = __DIR__ . '/data.sql';

        if (!file_exists($sqlFile)) {
            throw new RuntimeException(
                'Arquivo data.sql não encontrado.'
            );
        }

        $sql = file_get_contents($sqlFile);

        if ($sql === false) {
            throw new RuntimeException(
                'Não foi possível ler data.sql.'
            );
        }

        $pdo->exec($sql);
    }

} catch (Throwable $e) {

    error_log(
        'Erro na conexão com o banco: ' . $e->getMessage()
    );

    redirect($loginPage . '?error=service');
}

// ==========================================================
// 5. ACEITA SOMENTE REQUISIÇÕES POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($loginPage);
}

// Identifica se a ação é cadastro ou login.
$action = $_POST['action'] ?? 'login';

// ==========================================================
// 6. PROCESSAMENTO DO CADASTRO
// ==========================================================

if ($action === 'register') {

    // Validação do token CSRF.
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        empty($submittedToken) ||
        empty($sessionToken) ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        unset($_SESSION['csrf_token']);

        redirect($registerPage . '?error=security');
    }

    // O token não poderá ser reutilizado.
    unset($_SESSION['csrf_token']);

    // Recupera os dados enviados pelo formulário.
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validação do nome.
    if ($name === '' || mb_strlen($name) < 2) {
        redirect($registerPage . '?error=name');
    }

    // Validação do e-mail.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect($registerPage . '?error=email');
    }

    // Validação da senha.
    if (
        !is_string($password) ||
        strlen($password) < 8 ||
        strlen($password) > 72
    ) {
        redirect($registerPage . '?error=password');
    }

    // Confirmação da senha.
    if ($password !== $confirmPassword) {
        redirect($registerPage . '?error=match');
    }

    try {

        // Verifica se já existe uma conta com esse e-mail.
        $checkEmail = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $checkEmail->execute([
            ':email' => $email
        ]);

        if ($checkEmail->fetch()) {
            redirect($registerPage . '?error=exists');
        }

        // Cria um hash seguro da senha.
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Todo cadastro público começa como usuário comum.
        // A criação de administradores deve ser controlada.
        $insertUser = $pdo->prepare("
            INSERT INTO users (name, email, password, role)
            VALUES (:name, :email, :password, 'user')
        ");

        $insertUser->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $passwordHash
        ]);

        // Redireciona para o login após o cadastro.
        redirect($loginPage . '?registered=1');

    } catch (Throwable $e) {

        error_log(
            'Erro ao cadastrar usuário: ' . $e->getMessage()
        );

        redirect($registerPage . '?error=service');
    }
}

// ==========================================================
// 7. PROCESSAMENTO DO LOGIN
// ==========================================================

if ($action !== 'login') {
    redirect($loginPage);
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !is_string($password) ||
    $password === ''
) {
    redirect($loginPage . '?error=invalid');
}

try {

    // Procura o usuário pelo e-mail.
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch();

    // Usuário não encontrado.
    if (!$user) {
        redirect($loginPage . '?error=invalid');
    }

    $storedPassword = (string) $user['password'];

    // Verifica se a senha armazenada é um hash.
    $passwordIsHash = (
        password_get_info($storedPassword)['algoName'] !== 'unknown'
    );

    if ($passwordIsHash) {

        // Verifica a senha usando o hash.
        $passwordValid = password_verify(
            $password,
            $storedPassword
        );

    } else {

        // Compatibilidade com senhas antigas em texto puro.
        $passwordValid = hash_equals(
            $storedPassword,
            $password
        );
    }

    if (!$passwordValid) {
        redirect($loginPage . '?error=invalid');
    }

    // Atualiza hashes antigos ou com algoritmo desatualizado.
    if (
        !$passwordIsHash ||
        password_needs_rehash(
            $storedPassword,
            PASSWORD_DEFAULT
        )
    ) {

        $newHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $updatePassword = $pdo->prepare("
            UPDATE users
            SET password = :password
            WHERE id = :id
        ");

        $updatePassword->execute([
            ':password' => $newHash,
            ':id' => $user['id']
        ]);
    }

    // Verifica se o perfil possui uma página autorizada.
    $role = $user['role'] ?? '';

    if (!isset($destinations[$role])) {
        redirect($loginPage . '?error=invalid');
    }

    // ======================================================
    // 8. CRIAÇÃO DA SESSÃO DO USUÁRIO
    // ======================================================

    // Evita reutilização do identificador anterior da sessão.
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $role;
    $_SESSION['logged_in_at'] = time();

    // ======================================================
    // 9. REDIRECIONAMENTO CONFORME O PERFIL
    // ======================================================

    redirect($destinations[$role]);

} catch (Throwable $e) {

    error_log(
        'Erro durante o login: ' . $e->getMessage()
    );

    redirect($loginPage . '?error=service');
}
