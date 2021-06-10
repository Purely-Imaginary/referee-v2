<?php

namespace App\Controller;

use App\Command\RegenerateCommand;
use App\Entity\CalculatedMatch;
use App\Repository\CalculatedMatchRepository;
use App\Repository\PlayerRepository;
use App\Service\MatchCalculatorService;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calculatedMatch')]
class CalculatedMatchController extends AbstractController
{
    public function __construct(protected string $DISCORD_WEBHOOK_URL)
    {
    }

    #[Route('/new', name: 'calculated_match_new', methods: ['POST'])]
    public function new(Request $request, MatchCalculatorService $matchCalculator): Response
    {
        $data = json_decode($request->getContent(), true);

        $data['rawPositionsAtEnd'] = $data['rawPositionsAtEnd'] ?? $data['endTimestamp'];
        $data['startingGameTime'] = $data['startingGameTime'] ?? 0;
        $data['time'] = $data['time'] ?? date("Y m d H:i", ($data['endTimestamp'] / 1000));
        $data['gameTime'] = $data['gameTime'] ?? $data['duration'];
        $data['goalsData'] = $data['goalsData'] ?? $data['goals'];

        $filename = "HBReplay-" . date("Y-m-d-H\hi\m", ($data['endTimestamp'] / 1000)) . ".hbr2.bin.json";
        file_put_contents(RegenerateCommand::$processedFilesDir . '/' . $filename, json_encode($data));

        $newMatch = $matchCalculator->process($matchCalculator->getDataFromFile($filename));

        if ($newMatch === null) {
            return new Response("Match already in DB", 409);
        }

        (new Client())->post(
            $this->DISCORD_WEBHOOK_URL,
            ['json' => $matchCalculator->generateDiscordEmbed($newMatch)]
        );


        return new Response();
    }

    #[Route('/getLastMatches', name: 'calculated_match_index_last', methods: ['GET'])]
    public function getLastMatches(
        CalculatedMatchRepository $calculatedMatchRepository
    ): JsonResponse {
        $lastMatches = $calculatedMatchRepository->getLastMatches(30);

        return $this->json($lastMatches, Response::HTTP_OK, [], ['groups' => 'lastMatches']);
    }

    #[Route('/getById/{calculatedMatch}', name: 'calculated_match_get_by_id', methods: ['GET'])]
    public function getMatch(
        CalculatedMatch $calculatedMatch,
        CalculatedMatchRepository $calculatedMatchRepository
    ): JsonResponse {
        return $this->json($calculatedMatch, Response::HTTP_OK, [], ['groups' => ['Default', 'matchDetails']]);
    }
}
