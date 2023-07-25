<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\EntityResourceMapper\DataProcessor\AbstractDataProcessor;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\ApiResource\SiteTreeResource;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\SiteTree\Repository\SiteTreeRepository;

use function in_array;
use function preg_match;
use function rtrim;

class SiteTreeDataProcessor extends AbstractDataProcessor
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
                $entity = $this->patch($data, $operation, $context);
            } else {
                $entity = $this->post($data, $operation, $context);
            }

            $this->flushAndRefresh($entity);
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

        $select = ['level' => $level, 'slug' => $slug];
        if (0 < $level) {
            $select = ['slug' => $slug, 'parent' => $entity->getParent()];
        }

        $res = $this->entityManager->getRepository($this->getEntityClass())->findBy($select);
        if (in_array($entity, $res, true)) {
            foreach ($res as $key => $item) {
                if ($item === $entity) {
                    unset($res[$key]);
                    break;
                }
            }
        }

        if ([] !== $res) {
            throw new UnprocessableEntityHttpException($this->translator->trans('tree_node_already_exists', ['%level%' => $level, '%slug%' => $slug], domain: 'SiteTree'));
        }

        $entities = new ArrayCollection();
        foreach ($this->bag->get('whitedigital.site_tree.types') as $type) {
            $items = $this->entityManager->getRepository($type['entity'])->findBy(['node' => $entity->getParent(), 'slug' => $slug]);
            if ([] !== $items) {
                foreach ($items as $item) {
                    if (!$entities->contains($item)) {
                        $entities->add($item);
                    }
                }
            }
        }

        if ($entities->count()) {
            throw new UnprocessableEntityHttpException($this->translator->trans('item_exists_in_parent', ['%slug%' => $slug], domain: 'SiteTree'));
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
        $this->entityManager->getRepository($this->getEntityClass())->removeFromTree($entity);

        try {
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            preg_match('/DETAIL: (.*)/', $exception->getMessage(), $matches);
            throw new AccessDeniedHttpException($this->translator->trans('unable_to_delete_record', ['detail' => $matches[1]], domain: 'EntityResourceMapper'), $exception);
        }

        $this->entityManager->clear();
    }
}
