<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineApiPlatformMappings;
use WhiteDigital\ApiResource\DependencyInjections\Traits\DefineOrmMappings;
use WhiteDigital\ApiResource\Functions;
use WhiteDigital\SiteTree\Entity\Html;
use WhiteDigital\SiteTree\Entity\Redirect;

use function array_filter;
use function array_merge_recursive;
use function str_starts_with;
use function ucfirst;

use const ARRAY_FILTER_USE_KEY;

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
        $root = $definition
            ->rootNode();

        $root
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
            ->scalarNode('entity_manager')->defaultValue('default')->end();

        $this->addMethodsNode($root);

        $root
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (true === ($config['enabled'] ?? false)) {
            foreach ((new Functions())->makeOneDimension(['whitedigital.site_tree' => $config]) as $key => $value) {
                $builder->setParameter($key, $value);
            }

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

            $allowed = [];
            foreach ($config['allowed_methods'] as $method => $enabled) {
                if (true === $enabled) {
                    $allowed[] = $method;
                }
            }
            $builder->setParameter('whitedigital.site_tree.allowed_methods', $allowed);

            $container->import('../config/services.php');
        }

        $container->import('../config/decorator.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $siteTree = array_merge_recursive(...$builder->getExtensionConfig('site_tree') ?? []);
        $audit = array_merge_recursive(...$builder->getExtensionConfig('whitedigital') ?? [])['audit'] ?? [];

        if (true === ($siteTree['enabled'] ?? true)) {
            $manager = $siteTree['entity_manager'] ?? 'default';
            $mappings = $this->getOrmMappings($builder, $manager);

            $this->addDoctrineConfig($container, $manager, $mappings, 'SiteTree', self::MAPPINGS);
            $this->addApiPlatformPaths($container, self::PATHS);

            if (true === ($audit['enabled'] ?? false)) {
                $this->addDoctrineConfig($container, $audit['audit_entity_manager'], $mappings, 'SiteTree', self::MAPPINGS);
            }

            $container->extension('stof_doctrine_extensions', [
                'orm' => [
                    $manager => [
                        'tree' => true,
                    ],
                ],
            ]);
        }
    }

    private function filterKeyStartsWith(array $input, string $startsWith): array
    {
        return array_values(array_filter(array: $input, callback: static fn ($key) => str_starts_with(haystack: (string) $key, needle: $startsWith), mode: ARRAY_FILTER_USE_KEY));
    }

    private function addMethodsNode(ArrayNodeDefinition $node): void
    {
        $c = $node
            ->children()
            ->arrayNode('allowed_methods')
            ->addDefaultsIfNotSet()
            ->children();

        foreach ($this->filterKeyStartsWith((new ReflectionClass(objectOrClass: Request::class))->getConstants(filter: ReflectionClassConstant::IS_PUBLIC), 'METHOD_') as $method) {
            $default = false;
            if (Request::METHOD_GET === $method) {
                $default = true;
            }
            $c->booleanNode($method)->defaultValue($default)->end();
        }

        $c
            ->end()
            ->end()
            ->end();
    }
}
