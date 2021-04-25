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

class MatchCalculatorService
{
    public static int $kCoefficient = 250;

    public function __construct(
        protected string $PROCESSED_FILE_FOLDER,
        protected CalculatedMatchRepository $calculatedMatchRepository,
        protected PlayerRepository $playerRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function process(array $data)
    {
        if ($this->isInDB($data['rawPositionsAtEnd'])) {
            return null;
        }

        $newCalculatedMatch =
            (new CalculatedMatch())
                ->setRawPositions($data['rawPositionsAtEnd'])
                ->setStartTime($data['startingGameTime'])
                ->setEndTime($data['gameTime'])
                ->addTeamSnapshot($this->processTeam($data, 'Red'))
                ->addTeamSnapshot($this->processTeam($data, 'Blue'))
                ->setTime($data['time']);

        foreach ($data['goalsData'] as $rawGoal) {
            $newCalculatedMatch->addGoal($this->parseGoal($rawGoal));
        }

        $ratingChange = $this->calculateRatingChange($newCalculatedMatch);

        $newCalculatedMatch->getTeamSnapshot('red')->setRatingChange($ratingChange);
        $newCalculatedMatch->getTeamSnapshot('blue')->setRatingChange(-$ratingChange);

        $this->updatePlayers($newCalculatedMatch);
        $this->entityManager->persist($newCalculatedMatch);
        return $newCalculatedMatch;
    }

    public function getDataFromFile($filename)
    {
        return json_decode(file_get_contents($this->PROCESSED_FILE_FOLDER . "/" . $filename), true);
    }

    public function isInDB(string $position): bool
    {
        return null !== $this->calculatedMatchRepository->findOneBy(['rawPositions' => $position]);
    }

    public function processTeam(array $data, string $teamName): TeamSnapshot
    {
        $teamSnapshot = (new TeamSnapshot())
            ->setScore($data['score'][$teamName])
            ->setTeamColor($teamName);
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
        return $teamSnapshot;
    }

    private function parseGoal(array $rawGoal): Goal
    {
        $goal = (new Goal())
            ->setPlayer($this->playerRepository->findOneBy(['name' => $rawGoal['goalScorerName']]))
            ->setIsRed(strtolower($rawGoal['goalSide']) === 'red')
            ->setShotTime($rawGoal['goalShotTime'])
            ->setSpeed($rawGoal['goalSpeed'])
            ->setTime($rawGoal['goalTime'])
            ->setTravelTime($rawGoal['goalTravelTime']);
        $this->entityManager->persist($goal);

        return $goal;
    }

    protected function calculateRatingChange(CalculatedMatch $newCalculatedMatch): float
    {
        $ratingDifference = $newCalculatedMatch->getTeamSnapshot('blue')->getAvgTeamRating() - $newCalculatedMatch->getTeamSnapshot('red')->getAvgTeamRating();
        $powerPiece = pow(10, ($ratingDifference / 400));
        $winChance = (1 / (1 + $powerPiece));
        $scorePerformance = 0.5;

        if (($newCalculatedMatch->getTeamSnapshot('red')->getScore() + $newCalculatedMatch->getTeamSnapshot('blue')->getScore() == 0)) {
            return 0;
        }
        $scoreDifference = $newCalculatedMatch->getTeamSnapshot('red')->getScore() - $newCalculatedMatch->getTeamSnapshot('blue')->getScore();
        $scorePerformance = $scoreDifference > 0 ?
            (((1 - $winChance) / 10) * $scoreDifference) + $winChance :
            (($winChance / 10) * $scoreDifference) + $winChance;

        // Old calc method:
        // scorePerformance = float32(scoreDifference+10) / 20

        $ratingChange = $scorePerformance - $winChance * self::$kCoefficient;

        return $ratingChange / count($newCalculatedMatch->getTeamSnapshot('red')->getPlayerSnapshots());
    }

    protected function updatePlayers(CalculatedMatch $newCalculatedMatch)
    {
        foreach ($newCalculatedMatch->getTeamSnapshot('red')->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            $newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();
            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot('blue')->getScore());
            $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
        }

        foreach ($newCalculatedMatch->getTeamSnapshot('blue')->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            !$newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();
            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot('red')->getScore());
            $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
        }
    }
}