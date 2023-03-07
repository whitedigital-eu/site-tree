<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use Doctrine\DBAL\Exception;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProcessor\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

use function end;
use function explode;
use function in_array;
use function preg_match;
use function rtrim;
use function str_replace;

final class SiteTreeDataProcessor extends AbstractDataProcessor
{
    public function getEntityClass(): string
    {
        return SiteTree::class;
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (!$operation instanceof DeleteOperationInterface) {
            if ($operation instanceof Patch) {
                if (null === ($entity = $this->move($operation, $data, $context))) {
                    $entity = $this->patch($data, $operation, $context);
                }
            } else {
                $entity = $this->post($data, $operation, $context);
            }

            $this->flushAndRefresh($entity);
            /* @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->entityManager->getRepository($this->getEntityClass())->recover();
            $this->flushAndRefresh($entity);

            return $this->createResource($entity, $context);
        }

        $this->remove($data, $operation);

        return null;
    }

    /**
     * @throws Exception
     */
    protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): SiteTree
    {
        $entity = SiteTree::create($resource, $context, $existingEntity);

        if (null !== $existingEntity) {
            $repo = $this->entityManager->getRepository($this->getEntityClass());
            /* @var SiteTreeRepository $repo */
            $entity->setRoot($repo->getRootById($existingEntity->getId()));
            $entity->setParent($repo->getParentById($existingEntity->getId()));
        }

        $level = null === $entity->getParent() ? 0 : $entity->getParent()->getLevel() + 1;
        $slug = $entity->getSlug();

        if (0 < $level && '' === rtrim($slug, '/')) {
            throw new BadRequestHttpException($this->translator->trans('empty_slug_above_zero', domain: 'SiteTree'));
        }

        if ([] !== $this->entityManager->getRepository($this->getEntityClass())->findBy(['level' => $level, 'slug' => $slug])) {
            throw new UnprocessableEntityHttpException($this->translator->trans('tree_node_already_exists', ['%level%' => $level, '%slug%' => $slug], domain: 'SiteTree'));
        }

        return $entity;
    }

    /**
     * @throws ExceptionInterface
     * @throws ReflectionException
     * @throws ResourceClassNotFoundException
     */
    protected function createResource(BaseEntity $entity, array $context): SiteTreeResource
    {
        return SiteTreeResource::create($entity, $context);
    }

    protected function removeWithFkCheck(BaseEntity $entity): void
    {
        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->entityManager->getRepository($this->getEntityClass())->removeFromTree($entity);

        try {
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            preg_match('/DETAIL: (.*)/', $exception->getMessage(), $matches);
            throw new AccessDeniedHttpException($this->translator->trans('unable_to_delete_record', ['detail' => $matches[1]], domain: 'ApiResource'), $exception);
        }

        $this->entityManager->clear();
    }

    /**
     * @throws Exception
     *
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    private function moveNode(mixed $data, string $position, array $context = [], ?int $places = null): ?BaseEntity
    {
        $this->authorizationService->authorizeSingleObject($data, AuthorizationService::ITEM_PATCH);
        $existingEntity = $this->findById($this->getEntityClass(), $data->id);

        $entity = $this->createEntity($data, $context, $existingEntity);

        match ($position) {
            'up', 'top' => $this->entityManager->getRepository($this->getEntityClass())->moveUp($entity, $places ?? true),
            'down', 'bottom' => $this->entityManager->getRepository($this->getEntityClass())->moveDown($entity, $places ?? true),
            default => null,
        };

        return $entity;
    }

    /**
     * @throws Exception
     */
    private function move(Operation $operation, mixed $data, array $context = []): ?BaseEntity
    {
        if (in_array(SiteTreeResource::MOVE, $operation->getDenormalizationContext()['groups'] ?? [], true)) {
            $parts = explode('/', $operation->getName());
            $position = str_replace('_patch', '', end($parts));

            return match ($position) {
                'up', 'down' => $this->moveNode($data, $position, $context, 1),
                'top', 'bottom' => $this->moveNode($data, $position, $context),
                default => null,
            };
        }

        return null;
    }
}
