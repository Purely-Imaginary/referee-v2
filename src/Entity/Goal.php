<?php

namespace App\Entity;

use App\Repository\GoalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?int $id;

    /**
     * @Groups({"matchDetails"})
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="goals")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Player $player;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="goals")
     */
    private ?CalculatedMatch $calculatedMatch;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $time;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $travelTime;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $speed;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $shotTime;

    /**
     * @Groups({"matchDetails"})
     * @ORM\Column(type="boolean")
     */
    private ?bool $isRed;

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
