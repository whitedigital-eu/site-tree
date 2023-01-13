<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\DataProcessor\Traits;

use ReflectionException;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\SiteTree\Attribute\Translatable;
use WhiteDigital\SiteTree\Contracts\TreeEntity;
use WhiteDigital\SiteTree\Functions;

use function array_diff_key;
use function array_flip;
use function array_keys;
use function get_object_vars;
use function implode;
use function sprintf;
use function var_export;

trait ValidateResource
{
    protected function validateResource(BaseResource $resource): void
    {
        foreach (get_object_vars($resource) as $key => $value) {
            try {
                $prop = new ReflectionProperty($resource, $key);
            } catch (ReflectionException) {
                continue;
            }

            $attr = $prop->getAttributes(Translatable::class);
            if ([] !== $attr) {
                $allow = $prop->getAttributes(NotBlank::class);
                if ([] === $allow && null === $value) {
                    continue;
                }

                if (!(new Functions())->isAssociative($value)) {
                    throw new PreconditionFailedHttpException(sprintf('"%s" is in wrong format. "%s" expected, "%s" given', $key, var_export(['en' => 'example'], true), var_export($value, true)));
                }

                $missing = array_diff_key(array_flip($this->bag->get('whitedigital.site_tree.languages')), $value);
                if ([] !== $missing) {
                    throw new UnprocessableEntityHttpException(sprintf('Missing translation keys. "%s" excpected, "%s" given', implode(', ', $this->bag->get('whitedigital.site_tree.languages')), implode(', ', array_keys($value))));
                }
            }
        }
    }

    protected function validateEntity(TreeEntity $entity, BaseResource $resource, array $context, ?BaseEntity $existingEntity = null): ?TreeEntity
    {
        $exception = null;

        try {
            $this->validateResource($resource);
        } catch (UnprocessableEntityHttpException $exception) {
        }

        if ($entity->getNode()->getIsTranslatable() && null !== $exception) {
            throw $exception;
        }

        return $entity;
    }
}
