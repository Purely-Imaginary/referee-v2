<?php

namespace App\Entity;

use App\Repository\TeamSnapshotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

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
    private ?int $id;

    /**
     * @Groups("lastMatches")
     * @ORM\OneToMany(targetEntity=PlayerSnapshot::class, mappedBy="teamSnapshot")
     */
    private $playerSnapshots;

    /**
     * @Groups("lastMatches")
     * @ORM\Column(type="integer")
     */
    private ?int $score;

    /**
     * @ORM\ManyToOne(targetEntity=CalculatedMatch::class, inversedBy="teamSnapshots", cascade={"persist"})
     */
    private $calculatedMatch;

    /**
     * @Groups("lastMatches")
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $ratingChange;

    /**
     * @Groups("lastMatches")
     * @ORM\Column(type="boolean")
     */
    private bool $isRed;

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
        if ($this->playerSnapshots->removeElement($player) && $player->getTeamSnapshot() === $this) {
            $player->setTeamSnapshot(null);
        }

        return $this;
    }

    /**
     * @Groups("lastMatches")
     * @param bool $fillZeroes
     * @return float|null
     */
    public function getAvgTeamRating(bool $fillZeroes = false): ?float
    {
        return array_sum(
                array_map(
                    fn ($v) => $fillZeroes ? ($v->getRating() ?? Player::$startingRating) : $v->getRating(), $this->getPlayerSnapshots()->toArray()
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

    public function isRed(): ?bool
    {
        return $this->isRed;
    }

    public function setIsRed(bool $isRed): self
    {
        $this->isRed = $isRed;

        return $this;
    }

    public function getEnemyTeam(): TeamSnapshot
    {
        return $this->getCalculatedMatch()->getTeamSnapshot(!$this->isRed());
    }
}
