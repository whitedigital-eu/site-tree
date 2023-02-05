<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\SiteTree\ApiResource\ContentTypeResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Functions;

use function array_merge;

final readonly class ContentTypeDataProvider implements ProviderInterface
{
    private Functions $functions;

    public function __construct(
        private ParameterBagInterface $bag,
        TranslatorInterface $translator,
        private EntityManagerInterface $em,
        private AuthorizationService $authorizationService,
    ) {
        $this->functions = new Functions($this->em, $this->bag, $translator, $this->em->getRepository(SiteTree::class));
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $found = $this->functions->findContentType($uriVariables['id']);

        $this->authorizationService->authorizeSingleObject($found, AuthorizationService::ITEM_GET);

        $entities = [[]];
        $resource = new ContentTypeResource();
        if (!$found instanceof SiteTree) {
            $resource->resource = $found;
            $found = $found->getNode();
        } else {
            foreach ($this->bag->get('whitedigital.site_tree.types') as $type) {
                $items = $this->em->getRepository($type['entity'])->findBy(['node' => $found, 'isActive' => true]);
                if ([] !== $items) {
                    $entities[] = $items;
                }
            }
        }
        $entities = array_merge(...$entities);
        if ([] !== $entities) {
            $resource->resources = $entities;
        }

        $resource->nodeId = $found->getId();
        $resource->node = SiteTreeResource::create($found, $context);
        $resource->type = $found->getType();

        return $resource;
    }
}
