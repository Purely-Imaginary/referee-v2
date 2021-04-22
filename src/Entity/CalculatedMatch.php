<?php

namespace App\Entity;

use App\Repository\CalculatedMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $time;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $startTime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $endTime;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $rawPositions;

    /**
     * @ORM\OneToMany(targetEntity=Goal::class, mappedBy="calculatedMatch")
     */
    private $goals;

    /**
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="calculatedMatch")
     */
    private $playerSnapshots;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->playerSnapshots = new ArrayCollection();
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
    public function getGoals(): Collection
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
    public function getPlayerSnapshots(): Collection
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
}
