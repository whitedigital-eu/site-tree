<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;
use WhiteDigital\SiteTree\Api\Resource\SiteTreeResource;

use function abs;
use function end;
use function explode;
use function in_array;
use function str_replace;

class SiteTreeMovementDataProcessor extends SiteTreeDataProcessor
{
    public const UP = 'up';
    public const DOWN = 'down';
    public const TOP = 'top';
    public const BOTTOM = 'bottom';

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if ($operation instanceof Patch) {
            $entity = $this->move($operation, $data, $uriVariables, $context);
            if (null !== $entity) {
                $this->flushAndRefresh($entity);
                $this->entityManager->getRepository($this->getEntityClass())->recover();
                $this->flushAndRefresh($entity);

                return $this->createResource($entity, $context);
            }
        }

        throw new BadRequestHttpException($this->translator->trans('operation_not_supported', domain: 'SiteTree'));
    }

    /**
     * @throws Exception
     */
    private function moveNode(mixed $data, string $position, array $uriVariables = [], array $context = [], ?int $places = null): ?BaseEntity
    {
        $this->authorizationService->authorizeSingleObject($data, AuthorizationService::ITEM_PATCH);
        $existingEntity = $this->findById($this->getEntityClass(), $data->id);

        $entity = $this->createEntity($data, $context, $existingEntity);

        if ('{position}' === $position) {
            $siblings = $this->entityManager->getRepository($this->getEntityClass())->findBy(['level' => $entity->getLevel(), 'parent' => $entity->getParent()], ['root' => 'ASC', 'left' => 'ASC']);
            $current = 0;
            foreach ($siblings as $key => $sibling) {
                if ($sibling->getId() === $entity->getId()) {
                    $current = $key;
                }
            }
            $new = $current - (int) $uriVariables['position'];
            match (true) {
                $new > 0 => $this->entityManager->getRepository($this->getEntityClass())->moveUp($entity, abs($new)),
                $new < 0 => $this->entityManager->getRepository($this->getEntityClass())->moveDown($entity, abs($new)),
                default => null,
            };
        } else {
            match ($position) {
                self::UP, self::TOP => $this->entityManager->getRepository($this->getEntityClass())->moveUp($entity, $places ?? true),
                self::DOWN, self::BOTTOM => $this->entityManager->getRepository($this->getEntityClass())->moveDown($entity, $places ?? true),
                default => null,
            };
        }

        return $entity;
    }

    /**
     * @throws Exception
     */
    private function move(Operation $operation, mixed $data, array $uriVariables = [], array $context = []): ?BaseEntity
    {
        if (in_array(SiteTreeResource::MOVE, $operation->getDenormalizationContext()['groups'] ?? [], true)) {
            $parts = explode('/', $operation->getName());
            $position = str_replace('_patch', '', end($parts));

            return match ($position) {
                self::UP, self::DOWN => $this->moveNode($data, $position, $uriVariables, $context, 1),
                self::TOP, self::BOTTOM, '{position}' => $this->moveNode($data, $position, $uriVariables, $context),
                default => null,
            };
        }

        return null;
    }
}
