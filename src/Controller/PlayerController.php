<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\CalculatedMatchRepository;
use App\Repository\PlayerRepository;
use App\Repository\PlayerSnapshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $monthAgo = strtotime(date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d")))."-91 month")));
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
                'snapshots' => $this->playerSnapshotRepository->findBy(['player' => $player], ['id' => 'ASC']),
                'playerRatings' => $this->playerRepository->getPlayersTableData()
            ],
            200,
            [],
            ['groups' => ['playerDetails', 'lastMatches']]
        );
    }

    #[Route('/findTeams', name: 'findTeams', methods: ['GET'])]
    public function getTeamsFromPlayerList(
        Request $request,
        PlayerRepository $playerRepository
    ): JsonResponse {
        $playerNames = $request->get('players');

        $playersArray = [];
        foreach ($playerNames as $playerName) {
            $playersArray[$playerName] = $playerRepository->findOneBy(['name' => $playerName]);
        }

        $finalResult = $this->computePermutations($playerNames, $playersArray);

        $finalRedTeam = $finalBlueTeam = [];
        for ($i = 0; $i < count($finalResult[0]); $i++) {
            if ($i < count($finalResult[0]) / 2) {
                $finalRedTeam[] = $finalResult[0][$i];
            } else {
                $finalBlueTeam[] = $finalResult[0][$i];
            }
        }

        return $this->json([
            "red" => $finalRedTeam,
            "blue" => $finalBlueTeam,
            "redRating" => $finalResult[1],
            "blueRating" => $finalResult[2]
        ]);
    }

    #[Route('/findTeamsByBaskets', name: 'findTeamsByBaskets', methods: ['GET'])]
    public function getTeamsByBasketsFromPlayerList(
        Request $request,
        PlayerRepository $playerRepository
    ): JsonResponse {
        $playerNames = $request->get('players');
        array_map(fn ($x) => preg_replace("/[^A-Za-z0-9 ]/", '', $x), $playerNames);

        $playersArray = $playerRepository->getPlayersDataForTeams($playerNames);
        $finalRedTeam = $finalBlueTeam = [];

        for ($i = 0; $i < count($playersArray); $i++) {
            if (!isset($playersArray[$i]) || !isset($playersArray[$i + 1])) {
                break;
            }
            $higherToFirst = (bool)random_int(0, 1);
            $finalRedTeam[] = $playersArray[$i + (int)$higherToFirst];
            $finalBlueTeam[] = $playersArray[$i + (int)(!$higherToFirst)];
            $i++;
        }


        return $this->json([
            "red" => array_map(fn($x) => $x->getName(),$finalRedTeam),
            "blue" => array_map(fn($x) => $x->getName(),$finalBlueTeam),
            "redRating" => $this->getAverageRatingForTeam($finalRedTeam),
            "blueRating" => $this->getAverageRatingForTeam($finalBlueTeam)
        ]);
    }

    private function computePermutations($array, $playersArray): array
    {
        $minDiff = 999999;
        $finalPermutation = [];
        $finalBlueTeamRating = 0;
        $finalRedTeamRating = 0;

        $recurse = function ($array, $start_i = 0) use (&$result, &$recurse, $playersArray, &$minDiff, &$finalPermutation, &$finalRedTeamRating, &$finalBlueTeamRating) {
            if ($start_i === count($array) - 1) {
                $redTeam = $blueTeam = 0;
                for ($j = 0; $j < count($array); $j++) {
                    if ($j < count($array) / 2) {
                        $redTeam += $playersArray[$array[$j]]->getRating();
                    } else {
                        $blueTeam += $playersArray[$array[$j]]->getRating();
                    }
                }
                if ($redTeam < $blueTeam) {
                    $diff = $blueTeam - $redTeam;

                    if ($diff < $minDiff) {
                        $minDiff = $diff;
                        $finalPermutation = $array;
                        $finalBlueTeamRating = $blueTeam / (count($array) / 2);
                        $finalRedTeamRating = $redTeam / (count($array) / 2);
                    }
                }
            }

            for ($i = $start_i; $i < count($array); $i++) {
                $t = $array[$i];
                $array[$i] = $array[$start_i];
                $array[$start_i] = $t;
                $recurse($array, $start_i + 1);
                $t = $array[$i];
                $array[$i] = $array[$start_i];
                $array[$start_i] = $t;
            }
        };

        $recurse($array);

        return [$finalPermutation, $finalRedTeamRating, $finalBlueTeamRating];
    }

    function getAverageRatingForTeam(array $team)
    {
        $acc = 0;
        foreach ($team as $player) {
            $acc += $player->getRating();
        }
        return round($acc / count($team), 2);
    }
}
