<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\EntityResourceMapper\Security\Enum\GrantType;
use WhiteDigital\SiteTree\ApiResource\ContentTypeResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Service\ContentTypeFinderService;

use function array_key_exists;
use function array_merge;

final readonly class ContentTypeDataProvider implements ProviderInterface
{
    public function __construct(
        private ParameterBagInterface $bag,
        private TranslatorInterface $translator,
        private EntityManagerInterface $em,
        private AuthorizationService $authorizationService,
        private ContentTypeFinderService $finder,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $found = $this->finder->findContentType($uriVariables['id']);

        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::ITEM_GET, $operation->getClass()));
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

            $entities = array_merge(...$entities);
            if ([] !== $entities) {
                $resource->resources = $entities;
            }

            if ([] === $entities) {
                throw new NotFoundHttpException($this->translator->trans('tree_resources_not_found', domain: 'SiteTree'));
            }
        }

        $resource->nodeId = $found->getId();
        $resource->node = SiteTreeResource::create($found, $context);
        $resource->type = $found->getType();

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
