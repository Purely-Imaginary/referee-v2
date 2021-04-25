<?php

namespace App\Entity;

use App\Repository\CalculatedMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=CalculatedMatchRepository::class)
 * @ORM\Table(name="`calculated_match`")
 */
class CalculatedMatch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $time;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $startTime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $endTime;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private ?string $rawPositions;

    /**
     * @var Goal[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Goal::class, mappedBy="calculatedMatch")
     */
    private $goals;

    /**
     * @var PlayerSnapshot[]|Collection
     *
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="calculatedMatch")
     */
    private $playerSnapshots;

    /**
     * @var TeamSnapshot[]|Collection
     *
     * @ORM\OneToMany(targetEntity=TeamSnapshot::class, mappedBy="redCalculatedMatch")
     */
    private $teamSnapshots;

    #[Pure] public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->playerSnapshots = new ArrayCollection();
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
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getCalculatedMatch() === $this) {
                $goal->setCalculatedMatch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PlayerSnapshot[]
     */
    public function getPlayerSnapshots()
    {
        return $this->playerSnapshots;
    }

    public function addPlayerSnapshot(PlayerSnapshot $playerSnapshot): self
    {
        if (!$this->playerSnapshots->contains($playerSnapshot)) {
            $this->playerSnapshots[] = $playerSnapshot;
            $playerSnapshot->setCalculatedMatch($this);
        }

        return $this;
    }

    public function removePlayerSnapshot(PlayerSnapshot $playerSnapshot): self
    {
        if ($this->playerSnapshots->removeElement($playerSnapshot)) {
            // set the owning side to null (unless already changed)
            if ($playerSnapshot->getCalculatedMatch() === $this) {
                $playerSnapshot->setCalculatedMatch(null);
            }
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
        if ($this->teamSnapshots->removeElement($teamSnapshot)) {
            // set the owning side to null (unless already changed)
            if ($teamSnapshot->getCalculatedMatch() === $this) {
                $teamSnapshot->setCalculatedMatch(null);
            }
        }

        return $this;
    }

    #[Pure] public function getTeamSnapshot(string $teamColor): ?TeamSnapshot
    {
        foreach ($this->getTeamSnapshots() as $teamSnapshot) {
            if (strtolower($teamSnapshot->getTeamColor()) === strtolower($teamColor))
                return $teamSnapshot;
        }

        return null;
    }

    #[Pure] public function didRedWon(): bool
    {
        return $this->getTeamSnapshot('red')->getScore() > $this->getTeamSnapshot('blue')->getScore();
    }
}
