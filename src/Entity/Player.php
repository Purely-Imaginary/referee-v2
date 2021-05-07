<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player
{
    public static int $unrankedMatchesAmount = 10;
    public static int $startingRating = 1200;
    /**
     * @Groups({"lastMatches", "playersTable"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"lastMatches", "ratingChart", "playersTable"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @Groups({"playersTable"})
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $wins = 0;

    /**
     * @Groups({"playersTable"})
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $losses = 0;

    /**
     * @Groups({"playersTable"})
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $goalsShot = 0;

    /**
     * @Groups({"playersTable"})
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $goalsScored = 0;

    /**
     * @Groups({"playersTable"})
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $goalsLost = 0;

    /**
     * @Groups({"ratingChart", "playersTable"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $rating = null;

    /**
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="player", orphanRemoval=true)
     */
    private $playerSnapshots;

    /**
     * @ORM\OneToMany(targetEntity=Goal::class, mappedBy="player", orphanRemoval=true)
     */
    private $goals;

    public function __construct()
    {
        $this->playerSnapshots = new ArrayCollection();
        $this->goals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWins(): ?int
    {
        return $this->wins;
    }

    public function setWins(int $wins): self
    {
        $this->wins = $wins;

        return $this;
    }

    public function getLosses(): ?int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): self
    {
        $this->losses = $losses;

        return $this;
    }

    public function getGoalsShot(): ?int
    {
        return $this->goalsShot;
    }

    public function setGoalsShot(int $goalsShot): self
    {
        $this->goalsShot = $goalsShot;

        return $this;
    }

    public function getGoalsScored(): ?int
    {
        return $this->goalsScored;
    }

    public function setGoalsScored(int $goalsScored): self
    {
        $this->goalsScored = $goalsScored;

        return $this;
    }

    public function getGoalsLost(): ?int
    {
        return $this->goalsLost;
    }

    public function setGoalsLost(int $goalsLost): self
    {
        $this->goalsLost = $goalsLost;

        return $this;
    }

    public function getWinRate(): ?float
    {
        return $this->getWins() / ($this->getWins() + $this->getLosses());
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(float $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Collection|PlayerSnapshot[]
     */
    public function getPlayerSnapshots(): Collection
    {
        return $this->playerSnapshots;
    }

    public function addPlayerSnapshot(PlayerSnapshot $playerSnapshot): self
    {
        if (!$this->playerSnapshots->contains($playerSnapshot)) {
            $this->playerSnapshots[] = $playerSnapshot;
            $playerSnapshot->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerSnapshot(PlayerSnapshot $playerSnapshot): self
    {
        if ($this->playerSnapshots->removeElement($playerSnapshot) && $playerSnapshot->getPlayer() === $this) {
            $playerSnapshot->setPlayer(null);
        }

        return $this;
    }

    /**
     * @return Collection|Goal[]
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals[] = $goal;
            $goal->setPlayer($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal) && $goal->getPlayer() === $this) {
            $goal->setPlayer(null);
        }

        return $this;
    }

    public function addWin(): void
    {
        $this->setWins($this->getWins() + 1);
    }

    public function addLoss(): void
    {
        $this->setLosses($this->getLosses() + 1);
    }

    public function getTotalMatches(): int
    {
        return $this->getWins() + $this->getLosses();
    }

    /**
     * @Groups({"playersTable"})
     * @return int
     */
    public function getLastPlayed(): int
    {
        $lastPlayed = 0;
        foreach ($this->getPlayerSnapshots() as $playerSnapshot) {
            $lastPlayed = max($lastPlayed, strtotime($playerSnapshot->getTime()));
        }
        return $lastPlayed;
    }
}
