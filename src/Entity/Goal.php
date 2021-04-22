<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GoalRepository::class)
 */
class Goal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="goals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="goals")
     */
    private $calculatedMatch;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $time;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $travelTime;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $speed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shotTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getCalculatedMatch(): ?CalculatedMatch
    {
        return $this->calculatedMatch;
    }

    public function setCalculatedMatch(?CalculatedMatch $calculatedMatch): self
    {
        $this->calculatedMatch = $calculatedMatch;

        return $this;
    }

    public function getTime(): ?float
    {
        return $this->time;
    }

    public function setTime(?float $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getTravelTime(): ?float
    {
        return $this->travelTime;
    }

    public function setTravelTime(?float $travelTime): self
    {
        $this->travelTime = $travelTime;

        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(?float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getShotTime(): ?float
    {
        return $this->shotTime;
    }

    public function setShotTime(?float $shotTime): self
    {
        $this->shotTime = $shotTime;

        return $this;
    }

    public function getIsRed(): ?bool
    {
        return $this->isRed;
    }

    public function setIsRed(bool $isRed): self
    {
        $this->isRed = $isRed;

        return $this;
    }
}
