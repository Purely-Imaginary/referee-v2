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

    // /**
    //  * @return TeamSnapshot[] Returns an array of TeamSnapshot objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TeamSnapshot
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
