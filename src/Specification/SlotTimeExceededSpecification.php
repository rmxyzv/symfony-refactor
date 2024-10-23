<?php

declare(strict_types=1);

namespace App\Specification;

use App\Entity\Slot;
use DateTimeImmutable;

final readonly class SlotTimeExceededSpecification
{
    // @TODO. part of logic, DDD - domain.
    public function isSatisfiedBy(Slot $slot): bool
    {
        return $slot->getCreatedAt() < new DateTimeImmutable('5 minutes ago');
    }
}
