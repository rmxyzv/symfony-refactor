<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Slot;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SlotRepository extends EntityRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        $classMetadata = $em->getClassMetadata(Slot::class);
        parent::__construct($em, $classMetadata);
    }

    public function findByDoctorIds(array $doctorIds): array
    {
        $qb = $this->em->createQueryBuilder();

        return $qb->select('s')
            ->from(Slot::class, 's')
            ->join('s.doctor', 'd')
            ->where($qb->expr()->in('d.id', ':doctorIds'))
            ->setParameter('doctorIds', $doctorIds)
            ->getQuery()
            ->getResult();
    }
}
