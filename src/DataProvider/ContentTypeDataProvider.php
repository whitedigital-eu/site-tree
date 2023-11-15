<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\EntityResourceMapper\Mapper\EntityToResourceMapper;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\EntityResourceMapper\Security\Enum\GrantType;
use WhiteDigital\SiteTree\Api\Resource\ContentTypeResource;
use WhiteDigital\SiteTree\Api\Resource\SiteTreeResource;
use WhiteDigital\SiteTree\Contracts\ContentTypeFinderInterface;
use WhiteDigital\SiteTree\Entity\SiteTree;

use function array_key_exists;
use function array_merge;

readonly class ContentTypeDataProvider implements ProviderInterface
{
    public function __construct(
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
        private AuthorizationService $authorizationService,
        private ContentTypeFinderInterface $finder,
        private EntityToResourceMapper $mapper,
    ) {}

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $found = $this->finder->findContentType($uriVariables['slug']);

        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::ITEM_GET, $operation->getClass()));
        $this->authorizationService->authorizeSingleObject($found, AuthorizationService::ITEM_GET);

        $entities = [[]];
        $resource = new ContentTypeResource();

        if (!$found instanceof SiteTree) {
            $resource->exactResource = $this->mapper->map($found);
            $found = $found->getNode();
        } else {
            foreach ($this->bag->get('whitedigital.site_tree.types') as $type) {
                $items = $this->em->getRepository($type['entity'])->findBy(['node' => $found]);
                if ([] !== $items) {
                    $entities[] = $items;
                }
            }

            $entities = array_merge(...$entities);
            if ([] === $entities) {
                throw new NotFoundHttpException($this->translator->trans('tree_resources_not_found', domain: 'SiteTree'));
            }

            $resource->linkedResources = [];
            foreach ($entities as $entity) {
                $resource->linkedResources[] = $this->mapper->map($entity);
            }
        }

        $resource->nodeId = $found->getId();
        $resource->node = SiteTreeResource::create($found, $context);
        $resource->type = $found->getType();
        $resource->slug = $uriVariables['slug'];

        return $resource;
    }

    protected function override(string $operation, string $class): bool
    {
        try {
            $property = (new ReflectionClass($this->authorizationService))->getProperty('resources')->getValue($this->authorizationService);
        } catch (ReflectionException) {
            return false;
        }

        if (isset($property[$class])) {
            $attributes = $property[$class];
        } else {
            return false;
        }

        $allowed = array_merge($attributes[AuthorizationService::ALL] ?? [], $attributes[$operation] ?? []);
        if ([] !== $allowed && array_key_exists(AuthenticatedVoter::PUBLIC_ACCESS, $allowed)) {
            if (GrantType::ALL === $allowed[AuthenticatedVoter::PUBLIC_ACCESS]) {
                return true;
            }

            throw new InvalidConfigurationException('Public access only allowed with "all" grant type');
        }

        return false;
    }
}
