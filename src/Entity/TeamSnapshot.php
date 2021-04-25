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
    private $playerSnapshots;

    /**
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="teamSnapshots")
     */
    private $calculatedMatch;

    /**
     * @ORM\Column(type="float")
     */
    private $ratingChange;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $teamColor;

    public function __construct()
    {
        $this->playerSnapshots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|PlayerSnapshot[]
     */
    public function getPlayerSnapshots(): Collection
    {
        return $this->playerSnapshots;
    }

    public function addPlayer(PlayerSnapshot $player): self
    {
        if (!$this->playerSnapshots->contains($player)) {
            $this->playerSnapshots[] = $player;
            $player->setTeamSnapshot($this);
        }

        return $this;
    }

    public function removePlayer(PlayerSnapshot $player): self
    {
        if ($this->playerSnapshots->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeamSnapshot() === $this) {
                $player->setTeamSnapshot(null);
            }
        }

        return $this;
    }

    public function getAvgTeamRating(): ?float
    {
        return array_sum(
                array_map(
                    fn ($v) => $v->getRating(), $this->getPlayerSnapshots()->toArray()
                )
            ) / count($this->getPlayerSnapshots());
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

    public function getCalculatedMatch(): ?CalculatedMatch
    {
        return $this->calculatedMatch;
    }

    public function setCalculatedMatch(?CalculatedMatch $calculatedMatch): self
    {
        $this->calculatedMatch = $calculatedMatch;

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

    public function getTeamColor(): ?string
    {
        return $this->teamColor;
    }

    public function setTeamColor(string $teamColor): self
    {
        $this->teamColor = $teamColor;

        return $this;
    }
}
