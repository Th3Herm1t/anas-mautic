<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'mautic:business:sync',
    description: 'Synchronizes AnasArabic Business Logic (Segments, Emails, Campaigns) via Fixtures.'
)]
class SyncBusinessLogicCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('purge', null, InputOption::VALUE_NONE, 'Purge existing business data before loading (Caution!)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('AnasArabic Business Logic Sync');

        $projectDir = realpath(__DIR__ . '/../../../../'); // Root of Mautic (above docroot)
        $consolePath = $projectDir . '/bin/console';

        // 1. Install Plugin (ensure it's tracked)
        $io->section('1. refreshing Plugins...');
        $this->runSubCommand([$consolePath, 'mautic:plugins:reload'], $io);

        // 2. Load Fixtures
        $io->section('2. Loading Business Fixtures...');
        $args = [
            $consolePath,
            'doctrine:fixtures:load',
            '--group=anas_business',
            '--no-interaction'
        ];

        if (!$input->getOption('purge')) {
            $args[] = '--append';
        }

        $this->runSubCommand($args, $io);

        $io->success('Business Logic Synchronized Successfully!');
        $io->text([
            'Segments Created: B2C, B2B, Trial Active, etc.',
            'Emails Created: Templates A-H',
            'Campaigns Created: Signup, Trial, Recovery logic',
        ]);

        return 0;
    }

    private function runSubCommand(array $cmd, SymfonyStyle $io): void
    {
        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer, false, OutputInterface::OUTPUT_RAW);
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
