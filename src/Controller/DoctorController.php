<?php

declare(strict_types=1);

namespace App\Controller;

use App\DoctorSlotsMockReceiver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final readonly class DoctorController
{
    #[Route('/doctors', name: 'doctors.getAll', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $doctors = DoctorSlotsMockReceiver::getDoctors();

        return new JsonResponse([
            'data' => $doctors,
        ]);
    }
}
