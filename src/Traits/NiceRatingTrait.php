<?php

namespace App\Traits;

trait NiceRatingTrait
{
    public function getNiceRating(int $round = 2): string {
        return null !== $this->getRating() ? round($this->getRating(), $round) : 'unknown';
    }
}