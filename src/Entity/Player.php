<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $wins;

    /**
     * @ORM\Column(type="integer")
     */
    private $losses;

    /**
     * @ORM\Column(type="integer")
     */
    private $goalsShot;

    /**
     * @ORM\Column(type="integer")
     */
    private $goalsScored;

    /**
     * @ORM\Column(type="integer")
     */
    private $goalsLost;

    /**
     * @ORM\Column(type="float")
     */
    private $winRate;

    /**
     * @ORM\Column(type="float")
     */
    private $rating;

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
        return $this->winRate;
    }

    public function setWinRate(float $winRate): self
    {
        $this->winRate = $winRate;

        return $this;
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
        if ($this->playerSnapshots->removeElement($playerSnapshot)) {
            // set the owning side to null (unless already changed)
            if ($playerSnapshot->getPlayer() === $this) {
                $playerSnapshot->setPlayer(null);
            }
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
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getPlayer() === $this) {
                $goal->setPlayer(null);
            }
        }

        return $this;
    }
}
