<?php

namespace App\Entity;

use App\Repository\TeamSnapshotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamSnapshotRepository::class)
 */
class TeamSnapshot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="teamSnapshot")
     */
    private $players;

    /**
     * @ORM\Column(type="float")
     */
    private $AvgTeamRating;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="teamSnapshots")
     */
    private $redCalculatedMatch;

    /**
     * @ORM\Column(type="float")
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
            $player->setTeamSnapshot($this);
        }

        return $this;
    }

    public function removePlayer(PlayerSnapshot $player): self
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeamSnapshot() === $this) {
                $player->setTeamSnapshot(null);
            }
        }

        return $this;
    }

    public function getAvgTeamRating(): ?float
    {
        return $this->AvgTeamRating;
    }

    public function setAvgTeamRating(float $AvgTeamRating): self
    {
        $this->AvgTeamRating = $AvgTeamRating;

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

    public function getRedCalculatedMatch(): ?CalculatedMatch
    {
        return $this->redCalculatedMatch;
    }

    public function setRedCalculatedMatch(?CalculatedMatch $redCalculatedMatch): self
    {
        $this->redCalculatedMatch = $redCalculatedMatch;

        return $this;
    }

    public function getRatingChange(): ?float
    {
        return $this->ratingChange;
    }

    public function setRatingChange(float $ratingChange): self
    {
        $this->ratingChange = $ratingChange;

        return $this;
    }
}
