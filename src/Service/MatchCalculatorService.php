<?php

namespace App\Service;

use App\Entity\CalculatedMatch;
use App\Entity\Goal;
use App\Entity\Player;
use App\Entity\PlayerSnapshot;
use App\Entity\TeamSnapshot;
use App\Repository\CalculatedMatchRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Traits\NiceRatingTrait;

class MatchCalculatorService
{
    use NiceRatingTrait;
    public static int $kCoefficient = 250;

    public function __construct(
        protected string $PROCESSED_FILE_FOLDER,
        protected CalculatedMatchRepository $calculatedMatchRepository,
        protected PlayerRepository $playerRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function process(array $data): ?CalculatedMatch
    {
        if (!isset($data['rawPositionsAtEnd']) || $data['rawPositionsAtEnd'] === null || $this->isInDB($data['rawPositionsAtEnd'])) {
            return null;
        }

        $newCalculatedMatch =
            (new CalculatedMatch())
                ->setRawPositions($data['rawPositionsAtEnd'])
                ->setStartTime($data['startingGameTime'])
                ->setEndTime($data['gameTime'])
                ->addTeamSnapshot($this->processTeam($data, 'red'))
                ->addTeamSnapshot($this->processTeam($data, 'blue'))
                ->setTime($data['time']);

        foreach ($data['goalsData'] as $rawGoal) {
            $newCalculatedMatch->addGoal($this->parseGoal($rawGoal));
        }

        $ratingChange = $this->calculateRatingChange($newCalculatedMatch);

        $newCalculatedMatch->getTeamSnapshot(true)->setRatingChange($ratingChange);
        $newCalculatedMatch->getTeamSnapshot(false)->setRatingChange(-$ratingChange);

        $this->entityManager->flush();
        $this->updatePlayers($newCalculatedMatch);
        $this->entityManager->persist($newCalculatedMatch);
        $this->entityManager->flush();
        return $newCalculatedMatch;
    }

    public function getDataFromFile($filename)
    {
        $contents = file_get_contents($this->PROCESSED_FILE_FOLDER."/".$filename);
        try {
            $decode = json_decode($contents, true, 512, JSON_THROW_ON_ERROR && JSON_OBJECT_AS_ARRAY);
        } catch (\JsonException $e) {
            $i = 0;
        }
        if (json_last_error() != JSON_ERROR_NONE) {
            die(json_last_error_msg());
        }
        return $decode;
    }

    public function isInDB(string $position): bool
    {
        return null !== $this->calculatedMatchRepository->findOneBy(['rawPositions' => $position]);
    }

    public function processTeam(array $data, string $teamName): TeamSnapshot
    {
        $teamSnapshot = (new TeamSnapshot())
            ->setScore($data['score'][$teamName])
            ->setIsRed(strtolower($teamName) === 'red');

        foreach ($data['teams'][$teamName] as $playerName) {
            $player = $this->playerRepository->findOneBy(['name' => $playerName]);
            if (null === $player) {
                $player = (new Player())->setName($playerName);
                $this->entityManager->persist($player);
            }

            $this->entityManager->persist((new PlayerSnapshot())
                ->setIsRed(strtolower($teamName) === 'red')
                ->setPlayer($player)
                ->setRating($player->getRating())
                ->setTeamSnapshot($teamSnapshot));
        }
        $this->entityManager->persist($teamSnapshot);
        $this->entityManager->flush();
        return $teamSnapshot;
    }

    private function parseGoal(array $rawGoal): Goal
    {
        $goal = (new Goal())
            ->setPlayer($this->playerRepository->findOneBy(['name' => $rawGoal['goalScorerName']]))
            ->setIsRed(strtolower($rawGoal['goalSide']) === 'red')
            ->setSpeed($rawGoal['goalSpeed'])
            ->setTime($rawGoal['goalTime']);
        $this->entityManager->persist($goal);

        return $goal;
    }

    protected function calculateRatingChange(CalculatedMatch $newCalculatedMatch): float
    {
        $ratingDifference = $newCalculatedMatch->getTeamSnapshot(false)->getAvgTeamRating() - $newCalculatedMatch->getTeamSnapshot(true)->getAvgTeamRating();
        $powerPiece = pow(10, ($ratingDifference / 400));
        $winChance = (1 / (1 + $powerPiece));

        if ($newCalculatedMatch->getTeamSnapshot(true)->getScore() + $newCalculatedMatch->getTeamSnapshot(false)->getScore() == 0) {
            return 0;
        }
        $scoreDifference = $newCalculatedMatch->getTeamSnapshot(true)->getScore() - $newCalculatedMatch->getTeamSnapshot(false)->getScore();
        $scorePerformance = $scoreDifference > 0 ?
            (((1 - $winChance) / 10) * $scoreDifference) + $winChance :
            (($winChance / 10) * $scoreDifference) + $winChance;

        // Old calc method:
        // scorePerformance = float32(scoreDifference+10) / 20

        $ratingChange = ($scorePerformance - $winChance) * self::$kCoefficient;

        $ratingChange = $ratingChange * (180 / max($newCalculatedMatch->getEndTime(), 90));

        return $ratingChange / count($newCalculatedMatch->getTeamSnapshot(true)->getPlayerSnapshots());
    }

    protected function updatePlayers(CalculatedMatch $newCalculatedMatch)
    {
        foreach ($newCalculatedMatch->getTeamSnapshot(true)->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            $newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();

            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot(false)->getScore());


            $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
        }

        foreach ($newCalculatedMatch->getTeamSnapshot(false)->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            !$newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();

            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot(true)->getScore());

            $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
        }
    }

    public function generateDiscordEmbed(CalculatedMatch $cm): array
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
}