<?php

namespace WolfDen133\WSB\Task;

use pocketmine\scheduler\Task;
use WolfDen133\WSB\WSB;

class ScoreboardUpdateTask extends Task
{
    public function onRun () : void
    {
        WSB::getAPI()->updateScoreboards();
    }
}