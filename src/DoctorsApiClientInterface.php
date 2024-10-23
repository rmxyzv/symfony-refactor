<?php

declare(strict_types=1);

namespace App;

use Generator;

interface DoctorsApiClientInterface
{
    public function getAllDoctors(): Generator;

    public function getSlotsByDoctors(Generator $doctors): Generator;
}
