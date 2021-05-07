<?php

namespace App\Controller;

use App\Repository\PlayerSnapshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerSnapshotController extends AbstractController
{

    public function __construct(
        public PlayerSnapshotRepository $playerSnapshotRepository
    ) {
    }

    #[Route('/player/snapshot/filtered', name: 'player_snapshot')]
    public function index(): Response
    {
        $filteredSnapshots = $this->playerSnapshotRepository->getFilteredSnapshots();
        $dayNormalized = [];
        $lastMatch = [];
        foreach ($filteredSnapshots as $filteredSnapshot) {
            $lastMatch[$filteredSnapshot->getPlayer()->getId()] = max(strtotime($filteredSnapshot->getTime()), $lastMatch[$filteredSnapshot->getPlayer()->getId()] ?? 0);
        }

        $monthAgo = strtotime(date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month")));
        foreach ($filteredSnapshots as $filteredSnapshot) {
            if ($lastMatch[$filteredSnapshot->getPlayer()->getId()] > $monthAgo) {
                $dayNormalized[$filteredSnapshot->getPlayer()->getId() . '-' . substr($filteredSnapshot->getTime(), 0, 10)] = $filteredSnapshot;
            }
        }
        return $this->json(array_values($dayNormalized), 200, [], ['groups' => 'ratingChart']);
    }
}
