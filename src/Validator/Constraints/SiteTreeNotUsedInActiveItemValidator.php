<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteTreeNotUsedInActiveItemValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ParameterBagInterface $bag,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        foreach ($this->bag->get('whitedigital.site_tree.types') as $type) {
            if ([] !== $this->em->getRepository($type['entity'])->findBy(['node' => $value->id, 'isActive' => true])) {
                $this->context->buildViolation($this->translator->trans('one_active_node_allowed', ['entity' => $type['entity']], domain: 'SiteTree'))->addViolation();
                break;
            }
        }
    }
}
