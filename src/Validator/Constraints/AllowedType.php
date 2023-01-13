<?php declare(strict_types = 1);

namespace WhiteDigital\SiteTree\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AllowedType extends Constraint
{
}
