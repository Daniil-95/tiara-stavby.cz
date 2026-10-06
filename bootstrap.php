<?php

declare(strict_types=1);

use Nette\Bootstrap\Configurator;

require __DIR__ . '/vendor/autoload.php';

$productionConfig = __DIR__ . '/app/config/production.neon';
$host = strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''), 2)[0]);
$devHosts = ['tiara-stavby.local', 'localhost', '127.0.0.1', '::1'];
$environment = getenv('APP_ENV');
$isProduction = $environment === 'production'
	|| ($environment !== 'development' && PHP_SAPI !== 'cli' && !in_array($host, $devHosts, true));

if ($isProduction && !is_file($productionConfig)) {
	throw new RuntimeException('Production configuration is missing: app/config/production.neon');
}

$configurator = new Configurator;
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->setDebugMode(!$isProduction);
$logDirectory = __DIR__ . '/log';
if (!is_dir($logDirectory)) {
	mkdir($logDirectory, 0775, true);
}
$configurator->enableTracy($logDirectory);
$configurator->addDynamicParameters([
	'appEnvironment' => $isProduction ? 'production' : 'development',
	'appProduction' => $isProduction,
	'production' => $isProduction,
	'cookieSecure' => $isProduction,
	'env' => getenv(),
]);

$sessionDirectory = __DIR__ . '/temp/sessions';
if (!is_dir($sessionDirectory)) {
	mkdir($sessionDirectory, 0775, true);
}

$configurator->addConfig(__DIR__ . '/app/config/common.neon');
$configurator->addConfig(__DIR__ . ($isProduction ? '/app/config/production.neon' : '/app/config/local.neon'));

return $configurator->createContainer();
