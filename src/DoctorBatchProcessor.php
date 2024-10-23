<?php

declare(strict_types=1);

namespace App;

use App\Entity\Doctor;
use App\Repository\DoctorRepository;
use App\VO\DoctorVO;
use Doctrine\ORM\EntityManagerInterface;
use Generator;

readonly class DoctorBatchProcessor
{
    public function __construct(
        private DoctorRepository $doctorRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function batchUpsert(Generator $doctorsBatch): Generator
    {
        $doctorIds = [];
        $doctors = [];

        /** @var DoctorVO $doctor */
        foreach ($doctorsBatch as $doctorVO) {
            $doctorIds[] = $doctorVO->getId();
            $doctors[] = $doctorVO;
        }

        /** @var Doctor[] $existingDoctors */
        $existingDoctors = $this->doctorRepository->findByDoctorIds($doctorIds);
        $existingDoctorIds = array_column($existingDoctors, 'id');

        $newDoctors = array_filter(
            $doctors,
            fn (DoctorVO $doctor) => !in_array(
                $doctor->getId(),
                $existingDoctorIds,
                true
            )
        );

        $doctorsToUpdate = array_filter(
            $doctors,
            fn (DoctorVO $doctor) => in_array(
                $doctor->getId(),
                $existingDoctorIds,
                true
            )
        );

        if ($doctorsToUpdate !== []) {
            yield from $this->batchUpdateDoctors($existingDoctors, $doctorsToUpdate);
        }

        if ($newDoctors !== []) {
            yield from $this->batchInsertDoctors($newDoctors);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param Doctor[] $existingDoctors
     * @param DoctorVO[] $doctorsToUpdate
     */
    private function batchUpdateDoctors(
        array $existingDoctors,
        array $doctorsToUpdate
    ): Generator {
        $doctorsMap = [];

        foreach ($existingDoctors as $doctor) {
            $doctorsMap[$doctor->getId()] = $doctor;
        }

        foreach ($doctorsToUpdate as $doctorToUpdate) {
            $doctorId = $doctorsMap[$doctorToUpdate->getId()] ?? null;
            if ($doctorId === null) {
                continue;
            }
            /** @var Doctor $doctor */
            $doctor = $doctorsMap[$doctorId];
            $doctor->setName($doctorToUpdate->getName());
            $doctor->clearError();
            $this->entityManager->persist($doctor);
            yield $doctor;
        }
    }

    /**
     * @param DoctorVO[] $newDoctors
     */
    private function batchInsertDoctors(array $newDoctors): Generator
    {
        foreach ($newDoctors as $newDoctor) {
            $doctor = new Doctor($newDoctor->getId(), $newDoctor->getName());
            $doctor->clearError();
            $this->entityManager->persist($doctor);
            yield $doctor;
        }
    }
}
