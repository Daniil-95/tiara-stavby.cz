<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/../bootstrap.php';
$database = $container->getByType(Nette\Database\Explorer::class);

$email = strtolower(trim((string) readline('Admin email: ')));
$name = trim((string) readline('Admin name: '));
$password = (string) readline('Admin password (12+ characters): ');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($password) < 12) {
	fwrite(STDERR, "Enter a valid email, name and password of at least 12 characters.\n");
	exit(1);
}
$database->table('users')->insert([
	'name' => $name,
	'email' => $email,
	'password_hash' => Nette\Security\Passwords::hash($password),
	'role' => 'admin',
	'active' => 1,
]);
fwrite(STDOUT, "Admin account created.\n");
