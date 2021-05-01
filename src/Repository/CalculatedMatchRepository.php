<?php

namespace App\Repository;

use App\Entity\CalculatedMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CalculatedMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalculatedMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalculatedMatch[]    findAll()
 * @method CalculatedMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalculatedMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalculatedMatch::class);
    }

    /**
     * @return CalculatedMatch[]
     */
    public function getLastMatches(int $amount)
    {
        return $this->createQueryBuilder('cm')
            ->orderBy('cm.time', 'DESC')
            ->getQuery()
            ->setMaxResults($amount)
            ->getResult();
    }
}
