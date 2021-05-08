<?php

namespace App\Repository;

use App\Entity\CalculatedMatch;
use App\Entity\Player;
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

    /**
     * @return CalculatedMatch[]
     */
    public function getPlayerMatches(Player $player)
    {
        return $this->createQueryBuilder('cm')
            ->join('cm.teamSnapshots', 'ts')
            ->join('ts.playerSnapshots','ps')
            ->where('ps.player = :player')
            ->setParameter(':player', $player)
            ->orderBy('cm.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getLastMatchId(): int
    {
        return $this->createQueryBuilder('cm')
            ->select('cm.id')
            ->orderBy('cm.id', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }
}
