<?php

declare(strict_types=1);

namespace App;

use App\Entity\Slot;
use App\Repository\SlotRepository;
use App\Specification\SlotTimeExceededSpecification;
use App\VO\SlotVO;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use RuntimeException;

readonly class DoctorSlotsBatchProcessor
{
    public function __construct(
        private SlotRepository $slotRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function batchUpsert(Generator $slotsByDoctorIds): Generator
    {
        $doctorIds = [];
        $slots = [];

        /** @var iterable<int, SlotVO[]> $slotsByDoctorIds */
        foreach ($slotsByDoctorIds as $doctorId => $doctorSlots) {
            $doctorIds[] = $doctorId;
            foreach ($doctorSlots as $slot) {
                $slots[] = $slot;
            }
        }

        if ([] === $doctorIds) {
            throw new RuntimeException('Can not proceed without doctorIds');
        }

        /** @var Slot[] $existingSlots */
        $existingSlots = $this->slotRepository->findByDoctorIds($doctorIds);
        $existingSlotIds = array_column($existingSlots, 'id');

        $newSlots = array_filter(
            $slots,
            fn (SlotVO $slot) => !in_array(
                $slot->getId(),
                $existingSlotIds,
                true
            )
        );

        $slotsToUpdate = array_filter(
            $slots,
            fn (SlotVO $slot) => in_array(
                $slot->getId(),
                $existingSlotIds,
                true
            )
        );

        if ($slotsToUpdate !== []) {
            yield from $this->batchUpdateSlots($existingSlots, $slotsToUpdate);
        }

        if ($newSlots !== []) {
            yield from $this->batchInsertSlots($newSlots);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param Slot[] $existingSlots
     * @param SlotVO[] $slotsToUpdate
     */
    private function batchUpdateSlots(
        array $existingSlots,
        array $slotsToUpdate
    ): Generator {
        $slotsMap = [];

        foreach ($existingSlots as $slot) {
            if ($slot->getId() === null) {
                continue;
            }
            $slotsMap[$slot->getId()] = $slot;
        }

        foreach ($slotsToUpdate as $slotToUpdate) {
            $slotId = $slotsMap[$slotToUpdate->getId()] ?? null;
            if ($slotId === null) {
                continue;
            }
            /** @var Slot $slot */
            $slot = $slotsMap[$slotId];

            if ($slot->getStart() !== $slotToUpdate->getStartDate()) {
                continue;
            }

            if ((new SlotTimeExceededSpecification())->isSatisfiedBy($slot)) {
                $slot->setEnd($slotToUpdate->getEndDate());
            }
            $this->entityManager->persist($slot);
            yield $slot;
        }
    }

    /**
     * @param SlotVO[] $newSlots
     */
    private function batchInsertSlots(array $newSlots): Generator
    {
        foreach ($newSlots as $newSlot) {
            $slot = new Slot($newSlot->getStartDate(), $newSlot->getEndDate());
            $slot->setDoctor($newSlot->getDoctor());
            $this->entityManager->persist($slot);
            yield $slot;
        }
    }
}
