<?php

declare(strict_types=1);

namespace App\VO;

final readonly class DoctorsApiConnectionConfig
{
    public function __construct(
        private string $baseUri,
        private string $userName,
        private string $password
    ) {
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getAuthValue(): string
    {
        return base64_encode(sprintf('%s:%s', $this->userName, $this->password));
    }
}
