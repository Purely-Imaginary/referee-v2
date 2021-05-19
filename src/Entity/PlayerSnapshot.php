<?php

namespace App\Entity;

use App\Repository\PlayerSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Traits\NiceRatingTrait;

/**
 * @ORM\Entity(repositoryClass=PlayerSnapshotRepository::class)
 */
class PlayerSnapshot
{
    use NiceRatingTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"lastMatches", "ratingChart", "matchDetails"})
     * @ORM\ManyToOne(targetEntity=Player::class, inversedBy="playerSnapshots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @Groups({"lastMatches", "ratingChart", "matchDetails"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $rating = null;

    /**
     * @Groups("lastMatches", "matchDetails")
     * @ORM\Column(type="boolean")
     */
    private $isRed;

    /**
     * @Groups({"ratingChart"})
     * @ORM\ManyToOne(targetEntity=TeamSnapshot::class, inversedBy="players", cascade={"persist"})
     */
    private $teamSnapshot;

    /**
     * @Groups({"matchDetails"})
     */
    public function getGoalsAmount(): int
    {
        return count(
            array_values(
                array_filter(
                    $this->getTeamSnapshot()->getCalculatedMatch()->getGoals()->toArray(),
                    fn($v) => $v->getPlayer() === $this->getPlayer()
                )
            )
        );
    }

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

    /**
     * @Groups({"playerDetails"})
     * @return string
     */
    #[Pure] public function getTime(): string {
        return $this->getTeamSnapshot()->getCalculatedMatch()->getTime();
    }
}
