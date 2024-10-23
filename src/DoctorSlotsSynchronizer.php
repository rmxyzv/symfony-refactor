<?php

declare(strict_types=1);

namespace App;

use App\VO\DoctorVO;
use App\VO\SlotVO;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Psr\Log\LoggerInterface;
use Throwable;

// @TODO 2. Unit tests
// @TODO 3. need to add DDD

final readonly class DoctorSlotsSynchronizer
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DoctorsApiClientInterface $doctorsApiClient,
        private DoctorBatchProcessor $doctorBatchProcessor,
        private DoctorSlotsBatchProcessor $doctorSlotsBatchProcessor,
        private LoggerInterface $logger
    ) {
    }

    public function synchronizeDoctorSlots(): void
    {
        $this->entityManager->beginTransaction();
        try {
            $doctors = $this->doctorsApiClient->getAllDoctors();
            $this->processDoctors($doctors);
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            $this->logger->error('Error during doctors synchronization', ['error' => $e->getMessage()]);
        }
    }

    private function processDoctors(Generator $doctors): void
    {
        /** @var iterable<int, DoctorVO[]>|Generator $doctorsSlots */
        $doctorsUpsertResult = $this->doctorBatchProcessor->batchUpsert($doctors);

        /** @var iterable<int, SlotVO[]>|Generator $doctorsSlots */
        $doctorsSlots = $this->doctorsApiClient->getSlotsByDoctors($doctorsUpsertResult);
        $this->doctorSlotsBatchProcessor->batchUpsert($doctorsSlots);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
