<?php

declare(strict_types=1);

namespace App\VO;

use App\Entity\Doctor;
use DateTimeImmutable;

final readonly class SlotVO
{
    public function __construct(
        private int $id,
        private Doctor $doctor,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
    ) {
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }
}
