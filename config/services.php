<?php declare(strict_types = 1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load(namespace: 'WhiteDigital\\SiteTree\\', resource: __DIR__ . '/../src/*')
        ->exclude(excludes: [__DIR__ . '/../src/{Entity}']);

    $services->load(namespace: 'WhiteDigital\\SiteTree\\DataProvider\\', resource: __DIR__ . '/../src/DataProvider/*')
        ->arg(key: '$collectionExtensions', value: tagged_iterator('api_platform.doctrine.orm.query_extension.collection'));
};
