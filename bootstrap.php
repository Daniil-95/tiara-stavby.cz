<?php

declare(strict_types=1);

use Nette\Bootstrap\Configurator;

require __DIR__ . '/vendor/autoload.php';

$configurator = new Configurator;
$configurator->setDebugMode(getenv('APP_ENV') === 'development');
$configurator->setTempDirectory(__DIR__ . '/temp');
$sessionDirectory = __DIR__ . '/temp/sessions';
if (!is_dir($sessionDirectory)) mkdir($sessionDirectory, 0775, true);
$configurator->addConfig(__DIR__ . '/app/config/common.neon');
$container = $configurator->createContainer();

return $container;
