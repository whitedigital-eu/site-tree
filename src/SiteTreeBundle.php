<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineApiPlatformMappings;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineOrmMappings;
use WhiteDigital\SiteTree\Entity\Html;
use WhiteDigital\SiteTree\Entity\Redirect;

use function array_merge_recursive;
use function ucfirst;

class SiteTreeBundle extends AbstractBundle
{
    use DefineApiPlatformMappings;
    use DefineOrmMappings;

    private const MAPPINGS = [
        'type' => 'attribute',
        'dir' => __DIR__ . '/Entity',
        'alias' => 'SiteTree',
        'prefix' => 'WhiteDigital\SiteTree\Entity',
        'is_bundle' => false,
        'mapping' => true,
    ];

    private const PATHS = [
        '%kernel.project_dir%/vendor/whitedigital-eu/site-tree/src/ApiResource',
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('types')
                    ->useAttributeAsKey('type')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('entity')->defaultValue(null)->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('entity_prefix')->defaultValue('App\\Entity')->end()
                ->scalarNode('entity_manager')->defaultValue('default')->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (true === ($config['enabled'] ?? false)) {
            $builder->setParameter('whitedigital.site_tree.enabled', $config['enabled']);

            $types = [
                'html' => [
                    'entity' => Html::class,
                ],
                'redirect' => [
                    'entity' => Redirect::class,
                ],
            ];

            foreach ($config['types'] as $type => $value) {
                $types[$type] = [
                    'entity' => $value['entity'] ?? $config['entity_prefix'] . '\\' . ucfirst($type),
                ];
            }

            $builder->setParameter('whitedigital.site_tree.types', $types);

            $container->import('../config/services.php');
        }

        $container->import('../config/decorator.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $siteTree = array_merge_recursive(...$builder->getExtensionConfig('site_tree') ?? []);
        $audit = array_merge_recursive(...$builder->getExtensionConfig('whitedigital') ?? [])['audit'] ?? [];

        if (true === ($siteTree['enabled'] ?? true)) {
            $mappings = $this->getOrmMappings($builder, $siteTree['entity_manager'] ?? 'default');

            $this->addDoctrineConfig($container, $siteTree['entity_manager'] ?? 'default', $mappings, 'SiteTree', self::MAPPINGS);
            $this->addApiPlatformPaths($container, self::PATHS);

            if (true === ($audit['enabled'] ?? false)) {
                $this->addDoctrineConfig($container, $audit['audit_entity_manager'], $mappings, 'SiteTree', self::MAPPINGS);
            }
        }
    }
}
