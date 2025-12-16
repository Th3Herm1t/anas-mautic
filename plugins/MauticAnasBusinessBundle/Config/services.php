<?php

declare(strict_types=1);

use Mautic\CoreBundle\DependencyInjection\MauticCoreExtension;
use MauticPlugin\MauticAnasBusinessBundle\Command\SyncBusinessLogicCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [];

    $services->load('MauticPlugin\\MauticAnasBusinessBundle\\', '../')
        ->exclude('../{' . implode(',', array_merge(MauticCoreExtension::DEFAULT_EXCLUDES, $excludes)) . '}');

    // Explicitly register the command
    $services->set(SyncBusinessLogicCommand::class)
        ->tag('console.command');
};
