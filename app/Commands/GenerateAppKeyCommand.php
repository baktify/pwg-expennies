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
        $envPath = ROOT_PATH . '/.env';

        if (!file_exists($envPath)) {
            $output->writeln('Environment file does not exist');
            return Command::FAILURE;
        }

        $appKeyValue = base64_encode(random_bytes(32));

        $envFileContents = file_get_contents($envPath);

        $pattern = '/^APP_KEY=.*/m';
        if (preg_match($pattern, $envFileContents)) {
            $envFileContents = preg_replace($pattern, 'APP_KEY=' . $appKeyValue, $envFileContents);
        } else {
            $envFileContents .= PHP_EOL . 'APP_KEY=' . $appKeyValue;
        }

        file_put_contents($envPath, $envFileContents);

        $output->writeln('App key was successfully generated');

        return Command::SUCCESS;
    }
}