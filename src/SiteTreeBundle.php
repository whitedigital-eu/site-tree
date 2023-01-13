<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function array_merge;
use function array_merge_recursive;
use function array_unique;

class SiteTreeBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('languages')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('types')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (true === ($config['enabled'] ?? false)) {
            $builder->setParameter('whitedigital.site_tree.enabled', $config['enabled']);
            $builder->setParameter('whitedigital.site_tree.languages', [] === ($l = $config['languages'] ?? []) ? ['all', ] : $l);
            $builder->setParameter('whitedigital.site_tree.types', array_merge($config['types'] ?? [], ['html', 'redirect', ]));
            $container->import('../config/services.php');
        }

        $container->import('../config/decorator.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $siteTree = array_merge(...$builder->getExtensionConfig('site_tree')) ?? [];

        if (true === ($siteTree['enabled'] ?? false)) {
            $paths = array_merge_recursive(...$builder->getExtensionConfig('api_platform'))['mapping']['paths'];
            $paths[] = '%kernel.project_dir%/vendor/whitedigital-eu/site-tree/src/ApiResource';
            $paths = array_unique($paths);

            $container->extension('api_platform', [
                'mapping' => [
                    'paths' => $paths,
                ],
            ]);
        }
    }
}
