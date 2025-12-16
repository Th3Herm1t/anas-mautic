<?php

declare(strict_types=1);

namespace MauticPlugin\MauticAnasBusinessBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        $application = $this->getApplication();
        if (null === $application) {
            throw new \RuntimeException('Could not get Application instance');
        }

        // 1. Refresh Plugins
        $io->section('1. Refreshing Plugins...');
        $reloadInput = new ArrayInput(['command' => 'mautic:plugins:reload']);
        $application->doRun($reloadInput, $output);

        // 2. Load Fixtures
        $io->section('2. Loading Business Fixtures...');
        $fixturesArgs = [
            'command' => 'doctrine:fixtures:load',
            '--group' => 'anas_business',
            '--no-interaction' => true,
        ];

        if (!$input->getOption('purge')) {
            $fixturesArgs['--append'] = true;
        }

        $fixturesInput = new ArrayInput($fixturesArgs);
        $application->doRun($fixturesInput, $output);

        $io->success('Business Logic Synchronized Successfully!');
        $io->text([
            'Segments Created: B2C, B2B, Trial Active, etc.',
            'Emails Created: Templates A-H',
            'Campaigns Created: Signup, Trial, Recovery logic',
        ]);

        return Command::SUCCESS;
    }
}

