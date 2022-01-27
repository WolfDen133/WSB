<?php

namespace WolfDen133\WSB\Event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class TagReplaceEvent extends Event
{
    private array $tags;
    private Player $player;

    public function __construct(array $tags, Player $player)
    {
        $this->tags = $tags;
        $this->player = $player;
    }

    public function getPlayer () : Player
    {
        return $this->player;
    }

    public function setTag (string $find, string $replace) : void
    {
        $this->tags[$find] = $replace;
    }

    public function getTags () : array
    {
        return $this->tags;
    }
}