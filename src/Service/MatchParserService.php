<?php

namespace App\Service;

use App\Enum\GoalSideEnum;
use App\Enum\MatchStateEnum;
use App\Repository\CalculatedMatchRepository;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Traits\NiceRatingTrait;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

class MatchParserService
{
    use NiceRatingTrait;

    public static int $kCoefficient = 250;

    public function __construct(
        protected string                    $PROCESSED_FILE_FOLDER,
        protected CalculatedMatchRepository $calculatedMatchRepository,
        protected PlayerRepository          $playerRepository,
        protected EntityManagerInterface    $entityManager
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function getDataFromFile($filename)
    {
        ini_set('memory_limit', '1024M');
        $contents = file_get_contents($this->PROCESSED_FILE_FOLDER."/".$filename);
        $decode = json_decode($contents, true, 512, JSON_THROW_ON_ERROR && JSON_OBJECT_AS_ARRAY);

        return $decode;
    }

    /**
     * rT - replay time
     * s - state: 0: menu, 1: pause, 2: warmup, 3: game, 4: goal
     * gT - game time
     * rS - red score
     * bS - blue score
     * o - overtime
     * pC - player count
     * p - players
     * p[id]['id'] - id
     * p[id]['in'] - input
     * p[id]['k'] - kick
     * p[id]['t'] - team, 0: red, 1: blue
     * p[id]['d'] - player disc
     * b- ball disc
     *
     * @param mixed $matchData
     * @throws Exception
     */
    public function parseMatch(mixed $matchData, string $filename)
    {
        $players = ['red' => [], 'blue' => []];
        $touches = [];
        $kicks = [];
        $goals = [];
        $score = [
            'red' => 0,
            'blue' => 0
        ];

        $gameStartTime = 0;
        $matchLengthInTicks = 0;
        $rawPositionsAtEnd = "";

        $match = $matchData['match'];

        if ($match[0]['gT'] !== 0) {
            $score = [
                'red' => $match[0]['rS'],
                'blue' => $match[0]['bS']
            ];
            $gameStartTime = $match[0]['gT'];
        }

        for ($i = 0; $i < count($match); $i++) {
            $tick = $match[$i];
            $ballSpeed = round(hypot($tick['b']['b']['y'], $tick['b']['b']['x']), 3);
            // calculate players presence in match to determine who participated
            if ($tick['s'] > MatchStateEnum::WARMUP->value) {
                foreach ($tick['p'] as $player) {
                    $team = mb_strtolower(GoalSideEnum::from($player['t'])->name);
                    $players[$team][$matchData['names'][$player['id']]] = isset($players[$team][$matchData['names'][$player['id']]]) ? ++$players[$team][$matchData['names'][$player['id']]] : 1;
                }
                $matchLengthInTicks++;
            }

            // touch detection
            foreach ($tick['p'] as $player) {
                if (($distance = $this->calculateDistance($tick['b'], $player['d'])) < 30
                    && $this->hasVectorChanged($tick['b'], $match[$i - 1]['b'])) {

                    $touchData = [
                        'player' => $matchData['names'][$player['id']],
                        'team' => $player['t'],
                        'distance' => $distance,
                        'tick' => $i,
                        'time' => $tick['gT'],
                        'coords' => $tick['b']
                    ];
                    $touches[] = $touchData;
                    if ($player['in'] > 16) { //kick
                        $kicks[] = $touchData;
                    }

                };

            }

            // goal detection
            if ($i !== 0 && $tick['s'] === MatchStateEnum::GOAL->value && $match[$i - 1]['s'] === MatchStateEnum::GAME->value) {
                if ($tick['rS'] === $score['red'] && $tick['bS'] === $score['blue']) { // false positive
                    $i++;
                    continue;
                }
                $goalSide = $tick['rS'] === $score['red'] ? GoalSideEnum::BLUE->value : GoalSideEnum::RED->value;

                $tick['rS'] === $score['red'] ? $score['blue']++ : $score['red']++;

                // goal scorer and assist detection
                $shotPlace = [];
                $scorerName = $assistName = $shotTime = $assistTime = $assistLength = $assistPlace = -1;
                for ($rev = count($touches) - 1; $rev >= 0; $rev--) {
                    if ($scorerName === -1 && $touches[$rev]['team'] === $goalSide) {
                        $scorerName = $touches[$rev]['player'];
                        $shotTime = $touches[$rev]['time'];
                        $shotPlace = $touches[$rev]['coords'];
                    } elseif ($scorerName !== -1 && $assistName === -1 && $touches[$rev]['player'] !== $scorerName) {
                        if ($touches[$rev]['team'] !== $goalSide) {
                            $assistName = "";
                            break;
                        }
                        $assistName = $touches[$rev]['player'];
                        $assistTime = round($shotTime - $touches[$rev]['time'],3);
                        $assistLength = round($this->calculateDistance($shotPlace, $touches[$rev]['coords']),3);
                        $assistPlace = $touches[$rev]['coords']['a'];
                    } elseif ($scorerName !== -1 && $assistName !== -1) {
                        break;
                    }
                }

                $goals[] = [
                    "goalScorerName" => $scorerName,
                    "goalAssistName" => $assistName,
                    "goalAssistTime" => $assistTime,
                    "goalAssistLength" => $assistLength,
                    "goalAssistPlace" => $assistPlace,
                    "goalShotPlace" => $shotPlace['a'],
                    "goalShotTime" => $shotTime,
                    "goalSide" => $tick['rS'] === $score['red'] ? GoalSideEnum::BLUE->name : GoalSideEnum::RED->name,
                    "goalSpeed" => $ballSpeed,
                    "goalTime" => $tick['gT'],
                    "goalTravelTime" => round($tick['gT'] - $shotTime, 3)
                ];
            }
        }

        return [
            'goalsData' => $goals,
            'gameTime' => $match[count($match) - 1]['gT'],
            'rawPositionsAtEnd' => $this->getPositionsFromTick($match),
            'score' => $score,
            'teams' => $this->generateTeams($players, $matchLengthInTicks),
            'startingGameTime' => $gameStartTime,
            'time' => $this->getTimeFromFilename($filename)
        ];
    }

    public function calculateDistance($ball, $player): float
    {
        $bx = $ball['a']['x'];
        $by = $ball['a']['y'];
        $px = $player['a']['x'];
        $py = $player['a']['y'];

        return sqrt(($bx - $px) ** 2 + ($by - $py) ** 2);
    }

    public function hasVectorChanged($ball1, $ball2): bool
    {
        $vector1 = atan2($ball1['b']['x'], $ball1['b']['y']) * (180 / pi());
        $vector2 = atan2($ball2['b']['x'], $ball2['b']['y']) * (180 / pi());
        $change = abs($vector1 - $vector2);

        return $change > 0.5;
    }

    /**
     * @throws Exception
     */
    public function getPositionsFromTick($ticks):string
    {
        $tick = $this->getLastMeaningfulTick($ticks);
        $returnString = "";
        foreach ($tick['p'] as $player) {
            $returnString .= $player['d']['a']['x'] . ',' . $player['d']['a']['y'] . "|";
        }

        return $returnString;
    }

    /**
     * @throws Exception
     */
    public function getTimeFromFilename(string $filename): string
    {
        $matches = [];
        preg_match("/HBReplay-([0-9]{4})-([0-9]{2})-([0-9]{2})-([0-9]{2})h([0-9]{2})m.hbr2/", $filename, $matches);
        // "2021-11-05 12:42"
        if (count($matches) !== 6){
            throw new Exception("Invalid filename");
        }

        return $matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[4] . ":" . $matches[5];
    }

    /**
     * @throws Exception
     */
    public function getLastMeaningfulTick($ticks): array
    {
        for ($i = count($ticks) - 1; $i > 0; $i--){
            if ($ticks[$i]['s'] === MatchStateEnum::GAME->value){
                return $ticks[$i];
            }
        }
        throw new Exception("No gametime found");
    }

    #[ArrayShape(['red' => "array", 'blue' => "array"])]
    public function generateTeams($playersData,  $gameTimeInTicks, $threshold = 0.6): array
    {
        $returnData = ['red' => [], 'blue' => []];
        foreach ($playersData as $team => $teamData) {
            foreach ($teamData as $playerName => $ticksInPlay) {
                if ($ticksInPlay > $gameTimeInTicks * $threshold) {
                    $returnData[$team][] = $playerName;
                }
            }
        }
        return $returnData;
    }
}