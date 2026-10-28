<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: index.html');
	exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!is_string($email) || !is_string($password)) {
	header('Location: index.html?error=invalid', true, 303);
	exit;
}

$email = strtolower(trim($email));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
	header('Location: index.html?error=invalid', true, 303);
	exit;
}

try {
	$databasePath = getenv('SCHOOL_DB_PATH') ?: __DIR__ . '/database.sqlite';
	$pdo = new PDO('sqlite:' . $databasePath);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	$usersTable = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users'")->fetchColumn();
	if ($usersTable === false) {
		$schema = file_get_contents(__DIR__ . '/data.sql');
		if ($schema === false) {
			throw new RuntimeException('Unable to read data.sql.');
		}
		$pdo->exec($schema);
	}

	$statement = $pdo->prepare('SELECT id, password, role FROM users WHERE email = :email LIMIT 1');
	$statement->execute(['email' => $email]);
	$user = $statement->fetch();

	if (!$user || !hash_equals((string) $user['password'], $password)) {
		header('Location: index.html?error=invalid', true, 303);
		exit;
	}

	$destinations = [
		'admin' => 'mainAdm.html',
		'user' => 'mainUsuario.html',
	];
	$destination = $destinations[$user['role']] ?? null;

	if ($destination === null) {
		header('Location: index.html?error=invalid', true, 303);
		exit;
	}

	header('Location: ' . $destination, true, 303);
	exit;
} catch (Throwable $exception) {
	error_log($exception->getMessage());
	header('Location: index.html?error=service', true, 303);
	exit;
}
