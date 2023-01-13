<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function str_starts_with;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final readonly class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private ParameterBagInterface $bag,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $valid = $this->bag->has($key = 'whitedigital.site_tree.enabled') && true === $this->bag->get($key);
        $openApi = $this->decorated->__invoke($context);
        $paths = $openApi->getPaths()->getPaths();

        $filteredPaths = new Model\Paths();
        foreach ($paths as $path => $pathItem) {
            if (str_starts_with($path, '/api/wd/') && !$valid) {
                continue;
            }

            $filteredPaths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($filteredPaths);
    }
}
