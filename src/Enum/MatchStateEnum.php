<?php

namespace App\Enum;

enum MatchStateEnum: int
{
    case MENU = 0;
    case PAUSE = 1;
    case WARMUP = 2;
    case GAME = 3;
    case GOAL = 4;
}