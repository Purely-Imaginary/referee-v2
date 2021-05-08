<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\CalculatedMatchRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerSnapshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        protected PlayerRepository $playerRepository,
        protected PlayerSnapshotRepository $playerSnapshotRepository,
        protected CalculatedMatchRepository $calculatedMatchRepository
    ) {
    }

    #[Route('/playersTable', name: 'playersTable')]
    public function index(): Response
    {
        $playersData = $this->playerRepository->getPlayersTableData();
        $monthAgo = strtotime(date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month")));
        $playersData = array_filter($playersData, fn ($v) => $v->getLastPlayed() > $monthAgo);

        return $this->json(array_values($playersData), 200, [], ['groups' => 'playersTable']);
    }

    #[Route('/player/{id}', name: 'getPlayerById')]
    public function getPlayerById(Player $player): Response
    {
        return $this->json(
            [
                'player' => $player,
                'matchHistory' => $this->calculatedMatchRepository->getPlayerMatches($player),
                'snapshots' => $this->playerSnapshotRepository->findBy(['player' => $player], ['id' => 'DESC']),
                'playerRatings' => $this->playerRepository->getPlayersTableData()
            ],
            200,
            [],
            ['groups' => ['playerDetails', 'lastMatches']]
        );
    }


}
