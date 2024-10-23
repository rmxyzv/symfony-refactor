<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SlotRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "slots")]
#[ORM\Entity(repositoryClass: SlotRepository::class)]
final class Slot
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Doctor::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Doctor $doctor = null;

    #[ORM\Column(type: 'datetime')]
    private DateTimeImmutable $start;

    #[ORM\Column(type: 'datetime')]
    private DateTimeImmutable $end;

    #[ORM\Column(type: 'datetime')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->createdAt = new DateTimeImmutable();
    }

    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEnd(DateTimeImmutable $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }
}
