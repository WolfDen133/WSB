<?php

declare(strict_types=1);

namespace WolfDen133\WSB;

use pocketmine\plugin\PluginBase;
use WolfDen133\WSB\Command\WSBCommand;
use WolfDen133\WSB\Task\ScoreboardUpdateTask;

class WSB extends PluginBase
{
    private static API $api;
    public static bool $pws = false;

    private static self $instance;

    protected function onLoad () : void
    {
        $this->saveDefaultConfig();
        self::$instance = $this;
    }

    protected function onEnable(): void
    {
        date_default_timezone_set((string)$this->getConfig()->get("timezone"));

        WSB::$pws = $this->getConfig()->get('per-world-scoreboards');
        WSB::$api = new API($this);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardUpdateTask(), (int)(((float)$this->getConfig()->get('update-interval')) * 20));

        $this->getServer()->getCommandMap()->register('wsb', new WSBCommand($this));
    }

    public static function getAPI () : API
    {
        return self::$api;
    }

    public static function getInstance () : self
    {
        return self::$instance;
    }
}
