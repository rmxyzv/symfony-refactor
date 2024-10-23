<?php

declare(strict_types=1);

namespace App;

use App\Security\EnvReader;
use App\VO\DoctorsApiConnectionConfig;

final class DoctorsApiConnectionConfigFactory
{
    private ?DoctorsApiConnectionConfig $config = null;

    public function __construct(private EnvReader $envReader)
    {
    }

    public function getConfig(): DoctorsApiConnectionConfig
    {
        if (null === $this->config) {
            $this->config = new DoctorsApiConnectionConfig(
                $this->envReader->getEnv('INTERNAL_API_BASE_URI'),
                $this->envReader->getEnv('INTERNAL_API_USERNAME'),
                $this->envReader->getEnv('INTERNAL_API_PASSWORD')
            );
        }

        return $this->config;
    }
}
