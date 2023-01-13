<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use WhiteDigital\SiteTree\ApiResource\SiteTreeApiResource;

class ValidateTreeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var SiteTreeApiResource $value */
        if (null === $value->parent && null === $value->isTranslatable) {
            $this->context->buildViolation('You must set "isTranslatable" key on root node')->addViolation();
        }
    }
}
