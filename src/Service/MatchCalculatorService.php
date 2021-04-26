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
            ->setShotTime($rawGoal['goalShotTime'])
            ->setSpeed($rawGoal['goalSpeed'])
            ->setTime($rawGoal['goalTime'])
            ->setTravelTime($rawGoal['goalTravelTime']);
        $this->entityManager->persist($goal);

        return $goal;
    }

    protected function calculateRatingChange(CalculatedMatch $newCalculatedMatch, bool $checkForUnclassified = true): float
    {
        if ($checkForUnclassified && $this->checkForUnclassifiedPlayers($newCalculatedMatch)){
            return 0;
        }

        $ratingDifference = $newCalculatedMatch->getTeamSnapshot(false)->getAvgTeamRating(true) - $newCalculatedMatch->getTeamSnapshot(true)->getAvgTeamRating(true);
        $powerPiece = pow(10, ($ratingDifference / 400));
        $winChance = (1 / (1 + $powerPiece));

        if (($newCalculatedMatch->getTeamSnapshot(true)->getScore() + $newCalculatedMatch->getTeamSnapshot(false)->getScore() == 0)) {
            return 0;
        }
        $scoreDifference = $newCalculatedMatch->getTeamSnapshot(true)->getScore() - $newCalculatedMatch->getTeamSnapshot(false)->getScore();
        $scorePerformance = $scoreDifference > 0 ?
            (((1 - $winChance) / 10) * $scoreDifference) + $winChance :
            (($winChance / 10) * $scoreDifference) + $winChance;

        // Old calc method:
        // scorePerformance = float32(scoreDifference+10) / 20

        $ratingChange = ($scorePerformance - $winChance) * self::$kCoefficient;

        return $ratingChange / count($newCalculatedMatch->getTeamSnapshot(true)->getPlayerSnapshots());
    }

    protected function updatePlayers(CalculatedMatch $newCalculatedMatch)
    {
        foreach ($newCalculatedMatch->getTeamSnapshot(true)->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            $newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();

            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot(false)->getScore());

            if ($player->getRating() !== null) {
                $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
            }
            if ($player->getTotalMatches() >= Player::$unrankedMatchesAmount && $player->getRating() === null){
                $this->classifyPlayer($player);
            }
        }

        foreach ($newCalculatedMatch->getTeamSnapshot(false)->getPlayerSnapshots() as $playerSnapshot) {
            $player = $playerSnapshot->getPlayer();
            !$newCalculatedMatch->didRedWon() ? $player->addWin() : $player->addLoss();

            $player->setGoalsScored($player->getGoalsScored() + $playerSnapshot->getTeamSnapshot()->getScore());
            $player->setGoalsLost($player->getGoalsLost() + $newCalculatedMatch->getTeamSnapshot(true)->getScore());

            if ($player->getRating() !== null) {
                $player->setRating($player->getRating() + $playerSnapshot->getTeamSnapshot()->getRatingChange());
            }
            if ($player->getTotalMatches() >= Player::$unrankedMatchesAmount && $player->getRating() === null){
                $this->classifyPlayer($player);
            }
        }
    }

    protected function checkForUnclassifiedPlayers(CalculatedMatch $calculatedMatch): bool
    {
        foreach ($calculatedMatch->getTeamSnapshots() as $teamSnapshot) {
            foreach ($teamSnapshot->getPlayerSnapshots() as $playerSnapshot) {
                if ($playerSnapshot->getPlayer()->getTotalMatches() < Player::$unrankedMatchesAmount)
                {
                    return true;
                }
            }
        }
        return false;
    }

    protected function classifyPlayer(Player $player): void
    {
        $matchesEstimatedRatings = [];
        foreach ($player->getPlayerSnapshots() as $playerSnapshot) {
            $matchesEstimatedRatings[] = $this->estimateRating($player, $playerSnapshot->getTeamSnapshot());
        }
        $rating = array_sum($matchesEstimatedRatings) / count($matchesEstimatedRatings);
        $player->setRating($rating);
    }

    function estimateRating(Player $player, TeamSnapshot $teamSnapshot): float {
        $ratingForTie = $teamSnapshot->getPlayerSnapshots()->count() * $teamSnapshot->getEnemyTeam()->getAvgTeamRating(true);
        foreach ($teamSnapshot->getPlayerSnapshots() as $playerSnapshot) {
            if ($playerSnapshot->getPlayer() !== $player) {
                $ratingForTie -= $playerSnapshot->getRating() ?? Player::$startingRating;
            }
        }
        $estimatedRatingChangeForRed = $this->calculateRatingChange($teamSnapshot->getCalculatedMatch(), false);
        $estimatedRatingChangeForRed = $teamSnapshot->isRed() ? $estimatedRatingChangeForRed : -$estimatedRatingChangeForRed;

        return $ratingForTie + ($estimatedRatingChangeForRed * 40);
    }
}