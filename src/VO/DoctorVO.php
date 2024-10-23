<?php

declare(strict_types=1);

namespace App\VO;

use RuntimeException;

final readonly class DoctorVO
{
    public function __construct(private int $id, private string $name)
    {
        if ($this->name === '') {
            throw new RuntimeException('Doctor name cannot be empty');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->normalizeName($this->name);
    }

    private function normalizeName(string $fullName): string
    {
        [, $surname] = explode(' ', $fullName);
        return (0 === stripos($surname, "o'")) ? ucwords($fullName, ' \'') : ucwords($fullName);
    }
}
