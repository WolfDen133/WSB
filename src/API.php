<?php

namespace WolfDen133\WSB;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use WolfDen133\WSB\Utils\ScoreAPI;
use WolfDen133\WSB\Utils\TextHandle;

class API
{
    private Plugin $plugin;

    private int $scoreboardIndex = 0;

    private array $lines = [];
    private array $displayNames = [];

    /** @var string[] */
    private array $blacklist;

    /** @var array[] */
    private array $worldLines = [];

    private static ScoreAPI $API;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        self::$API = new ScoreAPI();

        $blacklist = new Config(WSB::getInstance()->getDataFolder() . "blacklist.json", Config::JSON);
        if (!$blacklist->exists('players')) $blacklist->set('players', []);
        $blacklist->save();

        $this->blacklist = $blacklist->get('players');

        $this->loadScoreboards();
        $this->pad();
    }

    private function pad () : void
    {
        $length = (int)$this->plugin->getConfig()->get('padding');

        if ($length == -1) return;

        foreach ($this->lines as $index => $line) {
            $text = TextFormat::clean($line);

            $times = ($length - strlen(str_replace(' ', '', $text)) <= 0 ? 0 : $length - strlen(str_replace(' ', '', $text)));
            $this->lines[$index] = $line . str_repeat(' ', $times);
        }

        if (!WSB::$pws) return;

        foreach ($this->worldLines as $world => $lines) {
            foreach ($lines as $index => $line) {
                $text = TextFormat::clean($line);

                $times = ($length - strlen(str_replace(' ', '', $text)) <= 0 ? 0 : $length - strlen(str_replace(' ', '', $text)));
                $this->worldLines[$world][$index] = $line . str_repeat(' ', $times);
            }
        }
    }

    private function loadScoreboards () : void
    {
        $config = $this->plugin->getConfig();

        $this->lines = $config->get('lines');
        $this->displayNames = $config->get('display-names');

        self::$API->createScoreboard($this->displayNames[0]);

        if (WSB::$pws) {
            foreach ($config->get('worlds') as $world => $lines) {
                self::$API->createScoreboard($this->displayNames[0], $world);

                foreach ($lines as $line) $this->worldLines[$world][] = $line;
            }
        }
    }

    public function addBlacklist (Player $player) : void
    {
        $config = new Config(WSB::getInstance()->getDataFolder() . 'blacklist.json', Config::JSON);
        $players = $config->get('players');

        if (!in_array($player->getName(), $players)) $players[] = $player->getName();
        if (!in_array($player->getName(), $this->blacklist)) $this->blacklist[] = $player->getName();

        $config->set('players', $players);
        $config->save();

        $this->removeScoreboard($player);
    }

    public function removeBlacklist (Player $player) : void
    {
        $config = new Config(WSB::getInstance()->getDataFolder() . 'blacklist.json', Config::JSON);
        $players = $config->get('players');

        if (in_array($player->getName(), $players)) unset($players[array_search($player->getName(), $players)]);
        if (in_array($player->getName(), $this->blacklist)) unset($this->blacklist[array_search($player->getName(), $this->blacklist)]);

        $config->set('players', $players);
        $config->save();

        $this->sendScoreboard($player, $player->getLocation()->getWorld()->getFolderName());
    }


    public function sendScoreboard (Player $player, string $destinationWorld) : void
    {
        if (in_array($player->getName(), $this->blacklist)) return;
        foreach (array_keys($this->worldLines) as $world) {
            if ($world != $destinationWorld) continue;
            if (!self::$API->isScoreboard($world)) continue;

            self::$API->addViewer($player, $world);
            $this->updateTo($player);

            return;
        }

        self::$API->addViewer($player);
        $this->updateTo($player);
    }

    public function reloadScoreboards () : void
    {
        foreach (WSB::getInstance()->getServer()->getOnlinePlayers() as $player) $this->removeScoreboard($player);

        foreach (array_keys($this->worldLines) as $world) {
            if (!self::$API->isScoreboard($world)) continue;

            self::$API->removeScoreboard($world);
        }

        self::$API->removeScoreboard();

        $this->loadScoreboards();
        $this->pad();

        foreach (WSB::getInstance()->getServer()->getOnlinePlayers() as $player) $this->sendScoreboard($player, $player->getLocation()->getWorld()->getFolderName());
        $this->updateScoreboards();
    }

    public function removeScoreboard (Player $player) : void
    {
        foreach (array_keys($this->worldLines) as $world) {

            if (!self::$API->isScoreboard($world)) continue;
            if (!self::$API->hasScoreboard($player, $world)) continue;

            self::$API->removeViewer($player, $world);
            return;
        }

        if (self::$API->hasScoreboard($player)) self::$API->removeViewer($player);
    }

    public function updateScoreboards () : void
    {
        $this->scoreboardIndex++;
        if ($this->scoreboardIndex > count($this->displayNames) - 1) $this->scoreboardIndex = 0;

        self::$API->setDisplayName(TextFormat::colorize($this->displayNames[$this->scoreboardIndex]));

        foreach (array_keys($this->worldLines) as $world) self::$API->setDisplayName(TextFormat::colorize($this->displayNames[$this->scoreboardIndex]), $world);

        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) $this->updateTo($player);
    }

    private function updateTo (Player $player) : void
    {
        if (self::$API->isScoreboard($player->getPosition()->getWorld()->getFolderName())) {

            if (self::$API->hasScoreboard($player, $player->getPosition()->getWorld()->getFolderName())) {

                self::$API->pushLinesTo($player, TextHandle::getFormattedText($this->worldLines[$player->getPosition()->getWorld()->getFolderName()], $player), $player->getPosition()->getWorld()->getFolderName());
                return;
            }
        }

        if (self::$API->hasScoreboard($player)) self::$API->pushLinesTo($player, TextHandle::getFormattedText($this->lines, $player));
    }
}