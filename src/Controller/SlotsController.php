<?php

declare(strict_types=1);

namespace App\Controller;

use App\DoctorSlotsMockReceiver;
use App\Shared\RequestMode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/api')]
final readonly class SlotsController
{
    #[Route('/doctors/slots', name: 'slots.getBulk', methods: ['POST'])]
    public function postSlots(Request $request): JsonResponse
    {
        // @IMPORTANT NOTE.
        // In this case, GET method is not suitable because $request body in an internal request can be quite large
        // it may overload the network / internal service (microservice) if we use GET method.
        try {
            $requestData = $request->toArray();
            /** @var string|null $mode */
            $mode = $requestData['mode'] ?? null;

            if (RequestMode::READ === $mode) {
                $doctorIds = $requestData['doctorIds'] ?? [];
                $slots = DoctorSlotsMockReceiver::getSlotsByDoctors($doctorIds);

                return new JsonResponse([
                    'data' => $slots,
                ]);
            }
        } catch (Throwable) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                'Something went wrong while trying to read slots.'
            );
        }

        // could be implemented later
        throw new HttpException(
            Response::HTTP_BAD_REQUEST,
            'Only READ mode requests are allowed.'
        );
    }
}
