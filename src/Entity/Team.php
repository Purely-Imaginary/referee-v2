<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="team")
     */
    private $players;

    /**
     * @ORM\Column(type="float")
     */
    private $avgTeamRating;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ratingChange;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|PlayerSnapshot[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(PlayerSnapshot $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players[] = $player;
            $player->setTeam($this);
        }

        return $this;
    }

    public function removePlayer(PlayerSnapshot $player): self
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    public function getAvgTeamRating(): ?float
    {
        return $this->avgTeamRating;
    }

    public function setAvgTeamRating(float $avgTeamRating): self
    {
        $this->avgTeamRating = $avgTeamRating;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getRatingChange(): ?float
    {
        return $this->ratingChange;
    }

    public function setRatingChange(?float $ratingChange): self
    {
        $this->ratingChange = $ratingChange;

        return $this;
    }
}
