<?php

namespace App\Repository;

use App\Entity\PlayerSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlayerSnapshot|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerSnapshot|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerSnapshot[]    findAll()
 * @method PlayerSnapshot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerSnapshot::class);
    }
}
