<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree;

use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineApiPlatformMappings;
use WhiteDigital\EntityResourceMapper\DependencyInjection\Traits\DefineOrmMappings;
use WhiteDigital\EntityResourceMapper\EntityResourceMapperBundle;
use WhiteDigital\SiteTree\DependencyInjection\CompilerPass\ContentTypeFinderCompilerPass;
use WhiteDigital\SiteTree\Entity\AbstractNodeEntity;
use WhiteDigital\SiteTree\Entity\Html;
use WhiteDigital\SiteTree\Entity\Redirect;

use function array_filter;
use function array_merge_recursive;
use function class_exists;
use function is_subclass_of;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function strtr;
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

    private const API_RESOURCE_PATH = '%kernel.project_dir%/vendor/whitedigital-eu/site-tree/src/Api/Resource';

    /** @noinspection PhpExpressionResultUnusedInspection */
    public function configure(DefinitionConfigurator $definition): void
    {
        $root = $definition
            ->rootNode();

        $root
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('types')
                ->useAttributeAsKey('type')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('entity')->defaultValue(null)->end()
                        ->booleanNode('single')->defaultValue(false)->end()
                        ->scalarNode('level')->defaultValue(1)->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('entity_prefix')->defaultValue('App\\Entity')->end()
            ->scalarNode('entity_manager')->defaultValue('default')->end()
            ->scalarNode('index_template')->defaultNull()->end()
            ->arrayNode('excluded_path_prefixes')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('excluded_path_prefixes_dev')
                ->scalarPrototype()->end()
            ->end()
            ->scalarNode('redirect_root_to_slug')->defaultNull()->end()
            ->scalarNode('custom_api_resource_path')->defaultNull()->end()
            ->arrayNode('sitemap')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('include_invisible')->defaultFalse()->end()
                ->end()
            ->end();

        $this->addMethodsNode($root);

        $root
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (EntityResourceMapperBundle::makeOneDimension(['whitedigital.site_tree' => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        if (null === $builder->getParameter('whitedigital.site_tree.index_template')) {
            throw new InvalidConfigurationException('"index_template" parameter must be set');
        }

        $types = [
            Html::TYPE => [
                'entity' => Html::class,
                'single' => false,
                'level' => 1,
            ],
            Redirect::TYPE => [
                'entity' => Redirect::class,
                'single' => false,
                'level' => 2,
            ],
        ];

        foreach ($config['types'] as $type => $value) {
            $entity = $value['entity'] ?? $config['entity_prefix'] . '\\' . ucfirst($type);
            if (!class_exists($entity)) {
                throw new InvalidConfigurationException(sprintf('Can\'t use type %s if entity %s does not exists', $type, $entity));
            }

            if (!is_subclass_of($entity, AbstractNodeEntity::class)) {
                throw new InvalidConfigurationException(sprintf('Type entities must extend %s, wrong parent on %s', AbstractNodeEntity::class, $entity));
            }

            $types[$type] = [
                'entity' => $entity,
                'single' => (bool) ($value['single'] ?? false),
                'level' => (int) ($value['level'] ?? 1),
            ];
        }

        uasort($types, static fn ($a, $b) => $a['level'] <=> $b['level']);

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

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $extensionConfig = self::getConfig('site_tree', $builder);
        $auditExtensionConfig = self::getConfig('audit', $builder);
        $manager = $extensionConfig['entity_manager'] ?? 'default';

        $this->addDoctrineConfig($container, $manager, 'SiteTree', self::MAPPINGS);

        if ([] !== $auditExtensionConfig) {
            $mappings = $this->getOrmMappings($builder, $auditExtensionConfig['default_entity_manager'] ?? 'default');
            $this->addDoctrineConfig($container, $auditExtensionConfig['audit_entity_manager'] ?? 'audit', 'SiteTree', self::MAPPINGS, $mappings);
        }

        $stof = [
            'orm' => [
                $manager => [
                    'tree' => true,
                ],
            ],
        ];

        if (null !== ($locale = self::getLocale($builder))) {
            $stof['default_locale'] = $locale;
        }

        $container->extension('stof_doctrine_extensions', $stof);
        $this->configureApiPlatformExtension($container, $extensionConfig);
    }

    public static function getLocale(ContainerBuilder $builder): ?string
    {
        $framework = self::getConfig('framework', $builder);
        $locale = $framework['default_locale'];
        if (str_contains($locale, '%') && !str_contains($locale, '%env')) {
            if ($builder->hasParameter($key = strtr($locale, ['%' => '']))) {
                $locale = $builder->getParameter($key);
            }
        }

        if (str_contains($locale, '%env')) {
            $locale = $_ENV[strtr($locale, ['%env(' => '', ')%' => ''])] ?? null;
        }

        return $locale;
    }

    public static function getConfig(string $package, ContainerBuilder $builder): array
    {
        return array_merge_recursive(...$builder->getExtensionConfig($package));
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ContentTypeFinderCompilerPass());
    }

    private function filterKeyStartsWith(array $input, string $startsWith): array
    {
        return array_values(array_filter(array: $input, callback: static fn ($key) => str_starts_with(haystack: (string) $key, needle: $startsWith), mode: ARRAY_FILTER_USE_KEY));
    }

    /** @noinspection PhpExpressionResultUnusedInspection */
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
            $c->booleanNode(strtolower($method))->defaultValue($default)->end();
        }

        $c
            ->end()
            ->end()
            ->end();
    }

    private function configureApiPlatformExtension(ContainerConfigurator $container, array $extensionConfig): void
    {
        if (!array_key_exists('custom_api_resource_path', $extensionConfig)) {
            $this->addApiPlatformPaths($container, [self::API_RESOURCE_PATH]);
        } elseif (!empty($extensionConfig['custom_api_resource_path'])) {
            $this->addApiPlatformPaths($container, [$extensionConfig['custom_api_resource_path']]);
        }
    }
}
