<?php

namespace App\Controller;

use App\Command\RegenerateCommand;
use App\Entity\CalculatedMatch;
use App\Entity\Goal;
use App\Entity\TeamSnapshot;
use App\Repository\CalculatedMatchRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;

class RawMatchController extends AbstractController
{

    public function __construct(protected string $DISCORD_WEBHOOK_URL)
    {
    }

    #[Route('/raw/match', name: 'raw_match')]
    public function index(
        Request $request,
        KernelInterface $kernel,
        CalculatedMatchRepository $calculatedMatchRepository
    ): Response {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get("file");
        file_put_contents(RegenerateCommand::$unparsedFilesDir . '/' . filter_var($uploadedFile->getClientOriginalName(), FILTER_SANITIZE_STRING), $uploadedFile->getContent());

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'referee:regenerate',
            'parseHbrs' => 'true',
        ]);
        // You can use NullOutput() if you don't need the output
        $application->run($input, (new NullOutput()));

        (new Client())->post(
            $this->DISCORD_WEBHOOK_URL,
            ['json' => $this->generateDiscordEmbed($calculatedMatchRepository->find($calculatedMatchRepository->getLastMatchId()))]
        );

        return $this->json($calculatedMatchRepository->getLastMatchId());
    }

    private function generateDiscordEmbed(CalculatedMatch $cm): array
    {
        $result = [
            'tts' => false,
            'embeds' => [],
            'content' => "New match has been uploaded!"
        ];
        $result['embeds'][] = [
            "url" => "https://purely-imaginary.github.io/#/showMatch/" . $cm->getId(),
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

    private function matchToDescription(CalculatedMatch $cm): string {
        $matchData[] = "**". ($cm->didRedWon() ? 'Red' : 'Blue') . " wins!**";
        $matchData[] = '**' . $cm->getTeamSnapshot(true)->getScore() . ' : ' . $cm->getTeamSnapshot(false)->getScore() . '**';
        $matchData[] = "\nMatch length: " . $cm->getNiceEndTime();

        $fastestGoal = $cm->getFastestGoal();
        if ($fastestGoal[1] < 5) {
            /** @var $fastestGoal <Goal, int> */
            $matchData[] =
                'Blitzkrieg Order goes to **' .
                $fastestGoal[0]->getPlayer()->getName() .
                "** for fastest goal: **" .
                $fastestGoal[1] .
                '** seconds from whistle at ' .
                $cm->getNiceTime($fastestGoal[0]->getTime()) .
                "!";
        }
        //TODO: Player's rating table with justify
        //TODO: Player events (new best rating, position change)
        return implode("\n", $matchData);
    }

    private function teamToDescription(TeamSnapshot $ts): string
    {
        $teamData = ['**' . ($ts->isRed() ? 'RED' : 'BLUE') . " TEAM:**\n"];
        $teamData[] = "Average rating: **" . round($ts->getAvgTeamRating()) . '**';
        $teamData[] = $ts->getRatingChange() === 0.0 ?
            "NEW PLAYERS IN MATCH - NO POINTS HAS BEEN GIVEN" :
            "Rating change: **". $ts->getNiceRatingChange() . "**";
        foreach ($ts->getPlayerSnapshots() as $playerSnapshot) {
            $teamData[] = "`" . $playerSnapshot->getPlayer()->getName() .
                "`: " . $playerSnapshot->getNiceRating(0) .
                " -> " . $playerSnapshot->getPlayer()->getNiceRating(0);
        }
        return implode("\n", $teamData);
    }

}
