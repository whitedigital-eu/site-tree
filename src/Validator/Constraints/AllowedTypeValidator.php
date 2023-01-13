<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Validator\Constraints;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use function implode;
use function in_array;
use function sprintf;

class AllowedTypeValidator extends ConstraintValidator
{
    public function __construct(private readonly ParameterBagInterface $bag)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!in_array($value, $types = $this->bag->get('whitedigital.site_tree.types'), true)) {
            $this->context->buildViolation(sprintf('Wrong type "%s". Allowed types: "%s"', $value, implode(', ', $types)))->addViolation();
        }
    }
}
