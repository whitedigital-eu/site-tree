<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Validator\Constraints;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_keys;
use function implode;
use function in_array;

class AllowedTypeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly TranslatorInterface $translator,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!in_array($value, $types = array_keys($this->bag->get('whitedigital.site_tree.types')), true)) {
            $this->context->buildViolation($this->translator->trans('invalid_parameter_list_allowed', ['%parameter%' => $value, '%allowed%' => implode(', ', $types)], domain: 'SiteTree'))->addViolation();
        }
    }
}
