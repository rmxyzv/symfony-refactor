<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DoctorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Table(name: "doctors")]
#[ORM\Entity(repositoryClass: DoctorRepository::class)]
final class Doctor
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "boolean")]
    private bool $error = false;

    /**
     * @var Collection<int, Slot>
     */
    #[ORM\OneToMany(mappedBy: "doctor", targetEntity: Slot::class, cascade: ["persist", "remove"])]
    private Collection $slots;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slots = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function markError(): void
    {
        $this->error = true;
    }

    public function clearError(): void
    {
        $this->error = false;
    }

    public function hasError(): bool
    {
        return $this->error;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
