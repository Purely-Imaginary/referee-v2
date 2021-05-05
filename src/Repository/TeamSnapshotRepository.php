<?php

namespace App\Repository;

use App\Entity\TeamSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeamSnapshot|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamSnapshot|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamSnapshot[]    findAll()
 * @method TeamSnapshot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamSnapshot::class);
    }
}
