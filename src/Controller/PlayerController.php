<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        protected PlayerRepository $playerRepository
    ) {}

    #[Route('/playersTable', name: 'player')]
    public function index(): Response
    {
        $playersData = $this->playerRepository->getPlayersTableData();
        $monthAgo = strtotime(date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month")));
        $playersData = array_filter($playersData, fn ($v) => $v->getLastPlayed() > $monthAgo);

        return $this->json(array_values($playersData), 200, [] ,['groups' => 'playersTable']);
    }
}
