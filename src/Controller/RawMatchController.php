<?php

namespace App\Controller;

use App\Command\RegenerateCommand;
use App\Entity\CalculatedMatch;
use App\Entity\Goal;
use App\Entity\TeamSnapshot;
use App\Repository\CalculatedMatchRepository;
use App\Service\MatchCalculatorService;
use App\Service\MatchParserService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;

class RawMatchController extends AbstractController
{

    public function __construct(
        protected string $DISCORD_WEBHOOK_URL
    ) {
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     * @throws Exception
     */
    #[Route('/raw/match', name: 'raw_match')]
    public function index(
        Request $request,
        KernelInterface $kernel,
        CalculatedMatchRepository $calculatedMatchRepository,
        MatchCalculatorService $matchCalculatorService,
        MatchParserService $matchParserService
    ): Response {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get("file");
        $clientOriginalName = $uploadedFile->getClientOriginalName();
        file_put_contents(RegenerateCommand::$unparsedFilesDir.'/'.filter_var($clientOriginalName, FILTER_SANITIZE_STRING), $uploadedFile->getContent());

        $out = "";
        exec("node /var/www/parser/haxball/replay.js convert /var/www/files/replayData/unparsed/$clientOriginalName /var/www/files/replayData/preprocessed/$clientOriginalName.bin.json", $out);

        $calculatedMatch = $matchCalculatorService->process(
            $matchParserService->parseMatch(
                $matchParserService->getDataFromFile($clientOriginalName. ".bin.json"),
                $clientOriginalName
            )
        );

        exec("rm /var/www/files/replayData/preprocessed/$clientOriginalName.bin.json", $out);

        if ($calculatedMatch === null){
            throw new Exception('Duplicate or no end positions found');
        }

        (new Client())->post(
            $this->DISCORD_WEBHOOK_URL,
            ['json' => $this->generateDiscordEmbed($calculatedMatch)]
        );

        return $this->json($calculatedMatch->getId());
    }

    private function generateDiscordEmbed(CalculatedMatch $cm): array
    {
        $result = [
            'tts' => false,
            'embeds' => [],
            'content' => "New match has been uploaded!"
        ];
        $result['embeds'][] = [
            "url" => "https://purely-imaginary.github.io/#/showMatch/".$cm->getId(),
            "title" => "Match results!",
            "description" => $this->matchToDescription($cm)
        ];
        $result['embeds'][] = [
            "color" => 14177041,
            "description" => $this->teamToDescription($cm->getTeamSnapshot(true))
        ];

        $result['embeds'][] = [
            "color" => 1127128,
            "description" => $this->teamToDescription($cm->getTeamSnapshot(false))
        ];

        return $result;
    }

    private function matchToDescription(CalculatedMatch $cm): string
    {
        $matchData[] = "**".($cm->didRedWon() ? 'Red' : 'Blue')." wins!**";
        $matchData[] = '**'.$cm->getTeamSnapshot(true)->getScore().' : '.$cm->getTeamSnapshot(false)->getScore().'**';
        $matchData[] = "\nMatch length: ".$cm->getNiceEndTime();

        $fastestGoal = $cm->getFastestGoal();
        if ($fastestGoal[1] < 5) {
            /** @var $fastestGoal <Goal, int> */
            $matchData[] =
                'Blitzkrieg Order goes to **'.
                $fastestGoal[0]->getPlayer()->getName().
                "** for fastest goal: **".
                $fastestGoal[1].
                '** seconds from whistle at '.
                $cm->getNiceTime($fastestGoal[0]->getTime()).
                "!";
        }
        //TODO: Player's rating table with justify
        //TODO: Player events (new best rating, position change)
        return implode("\n", $matchData);
    }

    private function teamToDescription(TeamSnapshot $ts): string
    {
        $teamData = ['**'.($ts->isRed() ? 'RED' : 'BLUE')." TEAM:**\n"];
        $teamData[] = "Average rating: **".round($ts->getAvgTeamRating()).'**';
        $teamData[] = $ts->getRatingChange() === 0.0 ?
            "NEW PLAYERS IN MATCH - NO POINTS HAS BEEN GIVEN" :
            "Rating change: **".$ts->getNiceRatingChange()."**";
        foreach ($ts->getPlayerSnapshots() as $playerSnapshot) {
            $teamData[] = "`".$playerSnapshot->getPlayer()->getName().
                "`: ".$playerSnapshot->getNiceRating(0).
                " -> ".$playerSnapshot->getPlayer()->getNiceRating(0);
        }
        return implode("\n", $teamData);
    }

    #[Route('/getFile/', name: 'getFile', methods: ['GET'])]
    public function getFileForReplay(
        Request $request,
        CalculatedMatchRepository $calculatedMatchRepository
    ): Response {
        $calculatedMatch = $calculatedMatchRepository->find($request->get('id'));
        // Provide a name for your file with extension
        $folder = RegenerateCommand::$unparsedFilesDir;
        $fileContent = file_get_contents($folder."/".$calculatedMatch->getFilename());
        // The dynamically created content of the file
        // Return a response with a specific content
        $response = new Response($fileContent);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $calculatedMatch->getFilename()
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;
    }
}
