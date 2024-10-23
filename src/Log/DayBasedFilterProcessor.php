<?php

declare(strict_types=1);

namespace App\Log;

use DateTimeImmutable;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final readonly class DayBasedFilterProcessor implements ProcessorInterface
{
    private const SKIP_ERROR_LOG_DAY = 'Sun';

    public function __invoke(LogRecord $record)
    {
        if ((new DateTimeImmutable())->format('D') === self::SKIP_ERROR_LOG_DAY) {
            return false;
        }

        return $record;
    }
}
