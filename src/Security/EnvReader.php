<?php

declare(strict_types=1);

namespace App\Security;

readonly final class EnvReader
{
    public function getEnv(string $key): ?string
    {
        return $_ENV[$key] ?? null;
    }
}
