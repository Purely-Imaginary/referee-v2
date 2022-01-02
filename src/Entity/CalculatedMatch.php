<?php

namespace App\Entity;

use App\Repository\CalculatedMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CalculatedMatchRepository::class)
 * @ORM\Table(name="`calculated_match`")
 */
class CalculatedMatch
{
    /**
     * @Groups({"lastMatches", "matchDetails"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @Groups({"lastMatches", "ratingChart", "matchDetails"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $time;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $startTime;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $endTime;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private ?string $rawPositions;

    /**
     * @Groups({"matchDetails"})
     * @var Goal[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Goal::class, mappedBy="calculatedMatch")
     */
    private $goals;

    /**
     * @Groups({"lastMatches", "matchDetails"})
     * @var TeamSnapshot[]|Collection
     *
     * @ORM\OneToMany(targetEntity=TeamSnapshot::class, mappedBy="calculatedMatch")
     */
    private $teamSnapshots;

    #[Pure] public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->teamSnapshots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    public function setStartTime(?float $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?float
    {
        return $this->endTime;
    }

    public function setEndTime(?float $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getRawPositions(): ?string
    {
        return $this->rawPositions;
    }

    public function setRawPositions(?string $rawPositions): self
    {
        $this->rawPositions = $rawPositions;

        return $this;
    }

    /**
     * @return Collection|Goal[]
     */
    public function getGoals()
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals[] = $goal;
            $goal->setCalculatedMatch($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal) && $goal->getCalculatedMatch() === $this) {
            $goal->setCalculatedMatch(null);
        }

        return $this;
    }

    /**
     * @return Collection|TeamSnapshot[]
     */
    public function getTeamSnapshots()
    {
        return $this->teamSnapshots;
    }

    public function addTeamSnapshot(TeamSnapshot $teamSnapshot): self
    {
        if (!$this->teamSnapshots->contains($teamSnapshot)) {
            $this->teamSnapshots[] = $teamSnapshot;
            $teamSnapshot->setCalculatedMatch($this);
        }

        return $this;
    }

    public function removeTeamSnapshot(TeamSnapshot $teamSnapshot): self
    {
        if ($this->teamSnapshots->removeElement($teamSnapshot) && $teamSnapshot->getCalculatedMatch() === $this) {
            $teamSnapshot->setCalculatedMatch(null);
        }

        return $this;
    }

    #[Pure] public function getTeamSnapshot(bool $getRed): ?TeamSnapshot
    {
        foreach ($this->getTeamSnapshots() as $teamSnapshot) {
            if ($teamSnapshot->isRed() === $getRed) {
                return $teamSnapshot;
            }
        }

        return null;
    }

    #[Pure] public function didRedWon(): bool
    {
        return $this->getTeamSnapshot(true)->getScore() > $this->getTeamSnapshot(false)->getScore();
    }

    public function getNiceEndTime($preformatted = true): string {
        $timeString = $this->getNiceTime($this->getEndTime());
        return $preformatted ? ($timeString !== "10:00" ? '**' : '') . $timeString . ($timeString !== "10:00" ? '**' : '') : $timeString;
    }

    public function getNiceTime(float $seconds): string {
        $seconds = (int)floor($seconds);

        return floor($seconds / 60) . ":" . ($seconds % 60 < 10 ? '0' . $seconds % 60 : $seconds % 60);
    }

    /**
     * @return array<Goal,int>
     */
    public function getFastestGoal(): array
    {
        $prevTime = 0;
        $minTime = 6000;
        $bestGoal = "";
        foreach ($this->getGoals() as $goal) {
            if ($goal->getTime() - $prevTime < $minTime) {
                $minTime = $goal->getTime() - $prevTime;
                $bestGoal = $goal;
            }
            $prevTime = $goal->getTime();
        }
        return [$bestGoal, $minTime];
    }

    public function getFilename(): string
    {
        return 'HBReplay-'.date('Y-m-d-H\hi\m',strtotime($this->getTime())).'.hbr2';
    }
}
