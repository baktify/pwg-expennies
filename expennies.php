<?php

declare(strict_types = 1);

use App\Config;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console;

$container = require 'bootstrap.php';
$config    = $container->get(Config::class);

$entityManager     = $container->get(EntityManagerInterface::class);
$dependencyFactory = DependencyFactory::fromEntityManager(
    new PhpFile(CONFIG_PATH . '/migrations.php'),
    new ExistingEntityManager($entityManager)
);

$cliApp = new Console\Application($config->get('app_name'), $config->get('app_version'));
$cliApp->setCatchExceptions(true);

ConsoleRunner::addCommands($cliApp, new SingleManagerProvider($entityManager));

$migrationCommands = require CONFIG_PATH . '/commands/migration_commands.php';
$customCommands    = require CONFIG_PATH . '/commands/commands.php';

$cliApp->addCommands($migrationCommands($dependencyFactory));
$cliApp->addCommands(array_map(fn($command) => $container->get($command), $customCommands));

$cliApp->run();
