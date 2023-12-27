<?php

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-key',
    description: 'Creates application key',
    aliases: ['generate-key']
)]
class GenerateAppKeyCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $envFile = '.env';
        $envPath = ROOT_PATH . '/' . $envFile;

        if (!file_exists($envPath)) {
            $output->writeln('Environment file does not exist');
            return Command::FAILURE;
        }

        if ($this->appKeyExists($envPath)) {
            $output->writeln('APP_KEY already exists.');
            $output->writeln('Remove the APP_KEY variable from "' . $envFile . '"');
            return Command::FAILURE;
        };

        $secretKey = bin2hex(random_bytes(25));

        file_put_contents(
            $envPath,
            PHP_EOL . 'APP_KEY=' . $secretKey,
            FILE_APPEND
        );

        return Command::SUCCESS;
    }

    public function appKeyExists($envPath)
    {
        $result = false;

        $resource = fopen($envPath, 'r');
        while ($line = fgets($resource)) {
            if (str_starts_with($line, 'APP_KEY=')) {
                $result = true;
            }
        }

        return $result;
    }
}