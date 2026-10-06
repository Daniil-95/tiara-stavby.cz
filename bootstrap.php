<?php

declare(strict_types=1);

use Nette\Bootstrap\Configurator;

require __DIR__ . '/vendor/autoload.php';

$productionConfig = __DIR__ . '/app/config/production.neon';
$environment = getenv('APP_ENV') ?: (is_file($productionConfig) ? 'production' : 'development');
$isProduction = $environment === 'production';
$productionValues = is_file($productionConfig)
	? (Nette\Neon\Neon::decodeFile($productionConfig)['parameters'] ?? [])
	: [];
$hasProductionEnvironment = true;
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'BASE_URL'] as $variable) {
	if (getenv($variable) === false || getenv($variable) === '') {
		$hasProductionEnvironment = false;
		break;
	}
}

$databaseParameters = [
	'dbHost' => getenv('DB_HOST') ?: ($productionValues['dbHost'] ?? '127.0.0.1'),
	'dbName' => getenv('DB_NAME') ?: ($productionValues['dbName'] ?? 'tiara_stavby'),
	'dbUser' => getenv('DB_USER') ?: ($productionValues['dbUser'] ?? 'root'),
	'dbPassword' => getenv('DB_PASSWORD') ?: ($productionValues['dbPassword'] ?? ''),
];

if ($isProduction && !$hasProductionEnvironment && !is_file($productionConfig)) {
	foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'BASE_URL'] as $variable) {
		if (getenv($variable) === false || getenv($variable) === '') {
			throw new RuntimeException(sprintf('Missing required production environment variable: %s', $variable));
		}
	}
}

$configurator = new Configurator;
$configurator->setDebugMode(!$isProduction);
$configurator->addDynamicParameters([
	'appEnvironment' => $environment,
	'appProduction' => $isProduction,
	'production' => $isProduction,
	'baseUrl' => getenv('BASE_URL') ?: ($productionValues['baseUrl'] ?? ($isProduction ? 'https://tiara-stavby.cz' : 'http://localhost')),
	'cookieSecure' => $isProduction,
] + $databaseParameters);
$configurator->setTempDirectory(__DIR__ . '/temp');
$sessionDirectory = __DIR__ . '/temp/sessions';
if (!is_dir($sessionDirectory)) mkdir($sessionDirectory, 0775, true);
$configurator->addConfig(__DIR__ . '/app/config/common.neon');
if ($isProduction && is_file($productionConfig)) {
	$configurator->addConfig($productionConfig);
}
$container = $configurator->createContainer();

if ($isProduction) {
	foreach (['dbHost', 'dbName', 'dbUser', 'dbPassword', 'baseUrl'] as $parameter) {
		$value = (string) ($container->parameters[$parameter] ?? '');
		if ($value === '' || str_starts_with($value, 'replace_with_')) {
			throw new RuntimeException(sprintf('Invalid production configuration value: %s', $parameter));
		}
	}
}

return $container;
