<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctorRepository extends EntityRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        $classMetadata = $em->getClassMetadata(Doctor::class);
        parent::__construct($em, $classMetadata);
    }

    public function findByDoctorIds(array $doctorIds): array
    {
        $qb = $this->em->createQueryBuilder();

        return $qb->select('d')
            ->from(Doctor::class, 'd')
            ->where($qb->expr()->in('d.id', ':doctorIds'))
            ->setParameter('doctorIds', $doctorIds)
            ->getQuery()
            ->getResult();
    }
}
