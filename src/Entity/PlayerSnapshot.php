<?php

namespace App\Entity;

use App\Repository\PlayerSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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
     * @Groups("lastMatches")
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="playerSnapshots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @Groups("lastMatches")
     * @ORM\Column(type="float", nullable=true)
     */
    private $rating = null;

    /**
     * @Groups("lastMatches")
     * @ORM\Column(type="boolean")
     */
    private $isRed;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="playerSnapshots")
     */
    private $calculatedMatch;

    /**
     * @ORM\ManyToOne(targetEntity=TeamSnapshot::class, inversedBy="players", cascade={"persist"})
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
        $player->addPlayerSnapshot($this);
        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
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
        $teamSnapshot->addPlayer($this);

        return $this;
    }
}
