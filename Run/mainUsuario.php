<?php
/*
|--------------------------------------------------------------------------
| PAINEL DO USUÁRIO - ESCOLA AGRÍCOLA
|--------------------------------------------------------------------------
| - Verifica se o usuário está autenticado.
| - Impede que administradores acessem a área de estudantes.
| - Exibe o nome e as iniciais do usuário.
*/

// ==========================================================
// 1. INICIALIZAÇÃO DA SESSÃO
// ==========================================================

session_start();

// ==========================================================
// 2. VERIFICAÇÃO DE AUTENTICAÇÃO
// ==========================================================

// Se não existir uma sessão de usuário, volta ao login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// ==========================================================
// 3. VERIFICAÇÃO DO TIPO DE USUÁRIO
// ==========================================================

$userRole = $_SESSION['user_role'] ?? '';

if ($userRole !== 'user') {

    // Se for administrador, direciona para o painel administrativo.
    if ($userRole === 'admin') {
        header('Location: mainAdm.php');
        exit;
    }

    // Qualquer perfil desconhecido retorna ao login.
    header('Location: login.html');
    exit;
}

// ==========================================================
// 4. DADOS DO USUÁRIO
// ==========================================================

// Recupera o nome salvo durante o login.
$rawUserName = $_SESSION['user_name'] ?? 'Estudante';

// Protege o nome contra inserção de código HTML.
$userName = htmlspecialchars(
    $rawUserName,
    ENT_QUOTES,
    'UTF-8'
);

// Recupera o e-mail.
$userEmail = htmlspecialchars(
    $_SESSION['user_email'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

// ==========================================================
// 5. GERAÇÃO DAS INICIAIS DO USUÁRIO
// ==========================================================

$nameParts = preg_split(
    '/\s+/u',
    trim($rawUserName),
    -1,
    PREG_SPLIT_NO_EMPTY
);

$initials = '';

if (!empty($nameParts)) {

    // Primeira letra do primeiro nome.
    $initials .= mb_substr(
        $nameParts[0],
        0,
        1,
        'UTF-8'
    );

    // Primeira letra do último sobrenome.
    if (count($nameParts) > 1) {
        $initials .= mb_substr(
            end($nameParts),
            0,
            1,
            'UTF-8'
        );
    }

}

$initials = htmlspecialchars(
    mb_strtoupper($initials, 'UTF-8'),
    ENT_QUOTES,
    'UTF-8'
);

// ==========================================================
// 6. CONSULTA AO BANCO DE DADOS
// ==========================================================

try {

    // Conexão com o banco de dados SQLite.
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');

    // Configurações de erro e modo de fetch.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Criação da tabela de itens, se não existir.
    $pdo->exec("CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        quantity INTEGER NOT NULL,
        expiration_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Consulta todos os itens cadastrados, inclusive os que estão sem estoque.
    $items = $pdo->query(
        'SELECT * FROM items ORDER BY expiration_date ASC, name ASC'
    )->fetchAll();

} catch (Throwable $e) {

    // Em caso de erro, registra no log e define mensagem de erro para o usuário.
    error_log('Erro na visualização de estoque: ' . $e->getMessage());
    $items = [];
    $databaseError = 'Não foi possível carregar o estoque.';

}

// ==========================================================
// 7. CÁLCULOS PARA EXIBIÇÃO
// ==========================================================

// Calcula a quantidade total de itens cadastrados.
$totalQuantity = array_sum(array_map(fn($item) => (int) $item['quantity'], $items));

// Data atual para comparações.
$today = new DateTimeImmutable('today');

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Estoque | Escola Agrícola</title>

    <link rel="stylesheet" href="style.css">

</head>

<body class="dashboard-page inventory-user-page">

    <div class="dashboard-shell">

        <!-- =================================================
             CABEÇALHO E NAVEGAÇÃO
        ================================================== -->

        <header class="dashboard-header">

            <a class="dashboard-brand" href="mainUsuario.php">

                <span class="brand-icon">EA</span>

                <span class="brand-name">
                    Escola Agrícola
                </span>

            </a>

            <nav class="dashboard-nav" aria-label="Navegação do usuário">

                <a class="active" href="mainUsuario.php">
                    Estoque
                </a>

            </nav>

            <!-- Perfil e botão de saída -->

            <div class="profile-actions">

                <span class="profile-link">

                    <span class="profile-avatar">
                        <?= $initials ?>
                    </span>

                    <span class="profile-name">
                        <?= $userName ?>
                    </span>

                </span>

                <a class="logout-button" href="logout.php">
                    Sair
                </a>

            </div>

        </header>

        <!-- =================================================
             CONTEÚDO PRINCIPAL
        ================================================== -->

        <main>

            <!-- Cabeçalho da seção de inventário -->

            <section class="inventory-heading user-inventory-heading">

                <div>

                    <span class="dashboard-eyebrow">
                        CONSULTA DE ESTOQUE
                    </span>

                    <h1>
                        Olá, <?= $userName ?>!
                    </h1>

                    <p>
                        Consulte todos os itens cadastrados, suas quantidades e datas de validade.
                    </p>

                </div>

            </section>

            <!-- Mensagem de erro do banco de dados -->

            <?php if (!empty($databaseError)): ?>
                <div class="inventory-alert danger">
                    <?= escUser($databaseError) ?>
                </div>
            <?php endif; ?>

            <!-- Estatísticas do inventário -->

            <section class="inventory-stats user-inventory-stats">

                <article>
                    <span>Tipos cadastrados</span>
                    <strong><?= count($items) ?></strong>
                </article>

                <article>
                    <span>Quantidade total</span>
                    <strong><?= $totalQuantity ?></strong>
                </article>

            </section>

            <!-- Tabela de itens do inventário -->

            <section class="inventory-table-card">

                <div class="inventory-table-heading">

                    <div>

                        <span class="dashboard-eyebrow">
                            ESTOQUE ATUALIZADO
                        </span>

                        <h2>
                            Itens do estoque
                        </h2>

                    </div>

                </div>

                <div class="admin-table-wrapper">

                    <table class="admin-table inventory-table">

                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantidade disponível</th>
                                <th>Data de validade</th>
                                <th>Situação</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="inventory-empty">
                                    Nenhum item cadastrado no estoque.
                                </td>
                            </tr>
                        <?php else: foreach ($items as $item):
                            $expiration = DateTimeImmutable::createFromFormat('!Y-m-d', $item['expiration_date']);
                            $status = 'Disponível';
                            $statusClass = 'active';
                            if ($expiration && $expiration < $today) {
                                $status = 'Vencido';
                                $statusClass = 'pending';
                            } elseif ((int) $item['quantity'] <= 0) {
                                $status = 'Sem estoque';
                                $statusClass = 'pending';
                            } elseif ($expiration && $expiration <= $today->modify('+30 days')) {
                                $status = 'Vence em breve';
                                $statusClass = 'pending';
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong><?= escUser($item['name']) ?></strong>
                                </td>
                                <td>
                                    <?= (int) $item['quantity'] ?>
                                </td>
                                <td>
                                    <?= escUser(date('d/m/Y', strtotime($item['expiration_date']))) ?>
                                </td>
                                <td>
                                    <span class="admin-status <?= $statusClass ?>">
                                        <?= escUser($status) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>

                    </table>

                </div>

            </section>

        </main>

        <!-- =================================================
             RODAPÉ
        ================================================== -->

        <footer class="dashboard-footer">

            <p>
                Escola Agrícola
            </p>

            <span>
                Consulta de estoque
            </span>

        </footer>

    </div>

</body>
</html>
<?php exit; ?>
