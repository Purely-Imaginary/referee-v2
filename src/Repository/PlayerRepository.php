<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @return Player[]
     */
    public function getPlayersTableData(): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.playerSnapshots', 'ps')
            ->where('p.rating is not null')
            ->orderBy('p.rating', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @return Player[]
     */
    public function getPlayersDataForTeams(array $playerNames): array
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where($qb->expr()->in('p.name', $playerNames))
            ->orderBy('p.rating', 'DESC')
            ->getQuery()->getResult();
    }
}
