<?php

namespace App\Enum;

enum InputEnum: int
{
    case Up = 1;
    case Down = 2;
    case Left = 4;
    case Right = 8;
    case Kick = 16;

    public function test($input_)
    {
        return ($input_ & $this->value) == $this->value;
    }
}