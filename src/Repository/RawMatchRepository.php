<?php

namespace App\Repository;

use App\Entity\RawMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RawMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method RawMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method RawMatch[]    findAll()
 * @method RawMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RawMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RawMatch::class);
    }
}
