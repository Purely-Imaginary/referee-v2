<?php

namespace App\Entity;

use App\Repository\PlayerSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerSnapshotRepository::class)
 */
class PlayerSnapshot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="playerSnapshots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\Column(type="float")
     */
    private $rating;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRed;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="players")
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="playerSnapshots")
     */
    private $calculatedMatch;

    /**
     * @ORM\ManyToOne(targetEntity=TeamSnapshot::class, inversedBy="players")
     */
    private $teamSnapshot;

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

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(float $rating): self
    {
        $this->rating = $rating;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

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

    public function getTeamSnapshot(): ?TeamSnapshot
    {
        return $this->teamSnapshot;
    }

    public function setTeamSnapshot(?TeamSnapshot $teamSnapshot): self
    {
        $this->teamSnapshot = $teamSnapshot;

        return $this;
    }
}
