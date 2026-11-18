```php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: mainUsuario.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$dbPath = __DIR__ . '/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        quantity INTEGER NOT NULL,
        expiration_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedToken = $_POST['csrf_token'] ?? '';
        if (!hash_equals($csrfToken, $postedToken)) {
            header('Location: mainAdm.php?error=security');
            exit;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $expirationDate = trim($_POST['expiration_date'] ?? '');

            $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $expirationDate)
                && (bool) strtotime($expirationDate);

            if ($name === '' || strlen($name) > 120 || $quantity === false || !$validDate) {
                header('Location: mainAdm.php?error=invalid');
                exit;
            }

            if ($action === 'create') {
                $statement = $pdo->prepare(
                    'INSERT INTO items (name, quantity, expiration_date) VALUES (:name, :quantity, :expiration_date)'
                );
                $statement->execute([
                    ':name' => $name,
                    ':quantity' => $quantity,
                    ':expiration_date' => $expirationDate
                ]);
                header('Location: mainAdm.php?success=created');
            } else {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                $statement = $pdo->prepare(
                    'UPDATE items SET name = :name, quantity = :quantity, expiration_date = :expiration_date WHERE id = :id'
                );
                $statement->execute([
                    ':name' => $name,
                    ':quantity' => $quantity,
                    ':expiration_date' => $expirationDate,
                    ':id' => $id
                ]);
                header('Location: mainAdm.php?success=updated');
            }
            exit;
        }

        if ($action === 'update_quantity') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

            if ($id === false || $quantity === false) {
                header('Location: mainAdm.php?error=invalid');
                exit;
            }

            $statement = $pdo->prepare('UPDATE items SET quantity = :quantity WHERE id = :id');
            $statement->execute([
                ':quantity' => $quantity,
                ':id' => $id
            ]);
            header('Location: mainAdm.php?success=quantity_updated');
            exit;
        }

        if ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $statement = $pdo->prepare('DELETE FROM items WHERE id = :id');
            $statement->execute([':id' => $id]);
            header('Location: mainAdm.php?success=deleted');
            exit;
        }
    }

    $items = $pdo->query('SELECT * FROM items ORDER BY expiration_date ASC, name ASC')->fetchAll();
} catch (Throwable $e) {
    error_log('Inventory error: ' . $e->getMessage());
    $items = [];
    $databaseError = 'Não foi possível acessar o estoque.';
}

$rawName = $_SESSION['user_name'] ?? 'Administrador';
$adminName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
$parts = preg_split('/\s+/u', trim($rawName), -1, PREG_SPLIT_NO_EMPTY);
$initials = '';
if ($parts) {
    $initials .= mb_substr($parts[0], 0, 1, 'UTF-8');
    if (count($parts) > 1) {
        $initials .= mb_substr(end($parts), 0, 1, 'UTF-8');
    }
}
$initials = htmlspecialchars(mb_strtoupper($initials, 'UTF-8'), ENT_QUOTES, 'UTF-8');

function esc(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$editItem = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    foreach ($items as $item) {
        if ((int) $item['id'] === (int) $editId) {
            $editItem = $item;
            break;
        }
    }
}

$totalItems = count($items);
$totalQuantity = array_sum(array_map(fn($item) => (int) $item['quantity'], $items));
$today = new DateTimeImmutable('today');
$expiredCount = 0;
$lowStockCount = 0;
foreach ($items as $item) {
    $expiration = DateTimeImmutable::createFromFormat('!Y-m-d', $item['expiration_date']);
    if ($expiration && $expiration < $today) {
        $expiredCount++;
    }
    if ((int) $item['quantity'] <= 5) {
        $lowStockCount++;
    }
}

$message = '';
if (isset($_GET['success'])) {
    $messages = ['created' => 'Item cadastrado com sucesso.', 'updated' => 'Item atualizado com sucesso.', 'quantity_updated' => 'Quantidade atualizada com sucesso.', 'deleted' => 'Item removido com sucesso.'];
    $message = $messages[$_GET['success']] ?? '';
} elseif (isset($_GET['error'])) {
    $messages = ['invalid' => 'Preencha os campos corretamente.', 'security' => 'Sessão inválida. Recarregue a página.'];
    $message = $messages[$_GET['error']] ?? 'Não foi possível concluir a operação.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque | Painel Administrativo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page inventory-page">
<div class="dashboard-shell">
    <header class="dashboard-header">
        <a class="dashboard-brand" href="mainAdm.php">
            <span class="brand-icon">EA</span>
            <span class="brand-name">Escola Agrícola</span>
        </a>
        <nav class="dashboard-nav" aria-label="Navegação administrativa">
            <a class="active" href="mainAdm.php">Estoque</a>
            <a href="mainUsuario.php">Ver área do usuário</a>
        </nav>
        <div class="profile-actions">
            <span class="profile-link">
                <span class="profile-avatar"><?= $initials ?></span>
                <span class="profile-name"><?= $adminName ?></span>
            </span>
            <a class="logout-button" href="logout.php">Sair</a>
        </div>
    </header>

    <main>
        <section class="inventory-heading">
            <div>
                <span class="dashboard-eyebrow">GESTÃO DE INSUMOS</span>
                <h1>Controle de estoque</h1>
                <p>Cadastre, edite e acompanhe os itens disponíveis na escola.</p>
            </div>
        </section>

        <?php if (!empty($message)): ?>
            <div class="inventory-alert <?= isset($_GET['success']) ? 'success' : 'danger' ?>"><?= esc($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($databaseError)): ?>
            <div class="inventory-alert danger"><?= esc($databaseError) ?></div>
        <?php endif; ?>

        <section class="inventory-stats">
            <article><span>Tipos de itens</span><strong><?= $totalItems ?></strong></article>
            <article><span>Quantidade total</span><strong><?= $totalQuantity ?></strong></article>
            <article><span>Estoque baixo</span><strong><?= $lowStockCount ?></strong></article>
            <article><span>Itens vencidos</span><strong><?= $expiredCount ?></strong></article>
        </section>

        <section class="inventory-layout">
            <aside class="inventory-form-card">
                <h2><?= $editItem ? 'Editar item' : 'Novo item' ?></h2>
                <form method="post" action="mainAdm.php">
                    <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= (int) $editItem['id'] ?>">
                    <?php endif; ?>

                    <label for="name">Nome do item</label>
                    <input id="name" name="name" type="text" maxlength="120" required
                           placeholder="Ex.: Leite"
                           value="<?= $editItem ? esc($editItem['name']) : '' ?>">

                    <label for="quantity">Quantidade</label>
                    <input id="quantity" name="quantity" type="number" min="0" step="1" required
                           placeholder="0"
                           value="<?= $editItem ? (int) $editItem['quantity'] : '' ?>">

                    <label for="expiration_date">Data de validade</label>
                    <input id="expiration_date" name="expiration_date" type="date" required
                           value="<?= $editItem ? esc($editItem['expiration_date']) : '' ?>">

                    <button class="inventory-primary" type="submit">
                        <?= $editItem ? 'Salvar alterações' : 'Adicionar item' ?>
                    </button>
                    <?php if ($editItem): ?>
                        <a class="inventory-secondary" href="mainAdm.php">Cancelar edição</a>
                    <?php endif; ?>
                </form>
            </aside>

            <section class="inventory-table-card">
                <div class="inventory-table-heading">
                    <div>
                        <span class="dashboard-eyebrow">INVENTÁRIO</span>
                        <h2>Itens cadastrados</h2>
                    </div>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-table inventory-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantidade</th>
                                <th>Validade</th>
                                <th>Situação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="5" class="inventory-empty">Nenhum item cadastrado.</td></tr>
                        <?php else: foreach ($items as $item):
                            $expiration = DateTimeImmutable::createFromFormat('!Y-m-d', $item['expiration_date']);
                            $status = 'Válido';
                            $statusClass = 'active';
                            if ($expiration && $expiration < $today) {
                                $status = 'Vencido';
                                $statusClass = 'pending';
                            } elseif ($expiration && $expiration <= $today->modify('+30 days')) {
                                $status = 'Próximo do vencimento';
                                $statusClass = 'pending';
                            }
                        ?>
                            <tr>
                                <td><strong><?= esc($item['name']) ?></strong></td>
                                <td>
                                    <form class="quantity-update-form" method="post" action="mainAdm.php">
                                        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input
                                            class="quantity-update-input"
                                            name="quantity"
                                            type="number"
                                            min="0"
                                            step="1"
                                            required
                                            value="<?= (int) $item['quantity'] ?>"
                                            aria-label="Nova quantidade de <?= esc($item['name']) ?>"
                                        >
                                        <button class="quantity-update-button" type="submit">Salvar</button>
                                    </form>
                                </td>
                                <td><?= esc(date('d/m/Y', strtotime($item['expiration_date']))) ?></td>
                                <td><span class="admin-status <?= $statusClass ?>"><?= esc($status) ?></span></td>
                                <td class="inventory-actions">
                                    <a class="inventory-edit" href="mainAdm.php?edit=<?= (int) $item['id'] ?>">Editar</a>
                                    <form method="post" action="mainAdm.php" onsubmit="return confirm('Excluir este item?');">
                                        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="inventory-delete" type="submit">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>

    <footer class="dashboard-footer">
        <p>Escola Agrícola</p>
        <span>Gestão de estoque</span>
    </footer>
</div>
</body>
</html>
<?php exit; ?>
```