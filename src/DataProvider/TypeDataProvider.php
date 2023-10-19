<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\SiteTree\Api\Resource\TypeResource;

final readonly class TypeDataProvider implements ProviderInterface
{
    public function __construct(
        private ParameterBagInterface $bag,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $types = [];
        foreach ($this->bag->get('whitedigital.site_tree.types') as $type => $data) {
            $resource = new TypeResource();
            $resource->type = $type;
            $resource->isSingle = (bool) ($data['single'] ?? false);

            $types[] = $resource;
        }

        return $types;
    }
}
