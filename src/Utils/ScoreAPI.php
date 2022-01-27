<?php


namespace WolfDen133\WSB\Utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreAPI
{
    public const OBJECTIVE = 'objective';

    private const SLOT_SIDEBAR = 'sidebar';
    private const SLOT_MENU = 'list';
    private const SLOT_BELOWNAME = 'belowname';

    private const CRITERIA = 'dummy';

    private const SORT_ACCENTING = 0;
    private const SORT_DESCENDING = 1;

    /** @var SetDisplayObjectivePacket[] */
    private array $scoreboardPackets = [];

    /** @var array[] */
    private array $scoreboardPlayers = [];

    /**
     * @param string $displayName
     * @param string $objectiveName
     * @param string $slot
     * @param int $sort
     */
    public function createScoreboard (string $displayName, string $objectiveName = self::OBJECTIVE, string $slot = self::SLOT_SIDEBAR, int $sort = self::SORT_ACCENTING) : void
    {
        if (isset($this->scoreboardPackets[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot create a new scoreboard with the objectiveName \'' . $objectiveName . '\', scoreboard already exists.');
            return;
        }

        $pk = SetDisplayObjectivePacket::create(
            $slot,
            $objectiveName,
            $displayName,
            self::CRITERIA,
            $sort
        );

        $this->scoreboardPackets[$objectiveName] = $pk;
        $this->scoreboardPlayers[$objectiveName] = [];
    }

    public function removeScoreboard (string $objectiveName = self::OBJECTIVE) : void
    {
        if (count($this->scoreboardPlayers[$objectiveName]) != 0) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot remove the scoreboard with the objectiveName \'' . $objectiveName . '\', scoreboard has viewers.');
            return;
        }

        unset($this->scoreboardPlayers[$objectiveName]);
        unset($this->scoreboardPackets[$objectiveName]);
    }


    /**
     * @param string $displayName
     * @param string $objectiveName
     * @return void
     */
    public function setDisplayName (string $displayName, string $objectiveName = self::OBJECTIVE) : void
    {
        if (!isset($this->scoreboardPackets[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot set the display name of the scoreboard with the objectiveName \'' . $objectiveName . '\', scoreboard does not exists.');
            return;
        }

        $this->scoreboardPackets[$objectiveName]->displayName = $displayName;
    }


    /** Add a viewer to a scoreboard
     * @param Player $player Target
     * @param string $objectiveName Scoreboard objective
     */
    public function addViewer (Player $player, string $objectiveName = self::OBJECTIVE) : void
    {
        if (in_array($player->getName(), $this->scoreboardPlayers[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot add player to scoreboard \'' . $objectiveName . '\' as they are already viewing a scoreboard.');
            return;
        }

        if (!isset($this->scoreboardPackets[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot add player to scoreboard \'' . $objectiveName . '\' as the scoreboard does not exist.');
            return;
        }

        $player->getNetworkSession()->sendDataPacket($this->scoreboardPackets[$objectiveName]);

        $this->scoreboardPlayers[$objectiveName][] = $player->getName();
    }

    /** Update the lines of the scoreboard to a player
     * @param Player $player Target
     * @param string[] $lines
     * @param string $objectiveName Scoreboard objective
     */
    public function pushLinesTo (Player $player, array $lines, string $objectiveName = self::OBJECTIVE) : void
    {
        if (!in_array($player->getName(), $this->scoreboardPlayers[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot update scoreboard \'' . $objectiveName . '\' to the player as they are not viewing this scoreboard.');
            return;
        }

        if (!isset($this->scoreboardPackets[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot update scoreboard \'' . $objectiveName . '\' to the player as the scoreboard does not exist.');
            return;
        }

        $entries = [];
        foreach ($lines as $line => $text) {

            $entry = new ScorePacketEntry();
            $entry->type = $entry::TYPE_FAKE_PLAYER;
            $entry->objectiveName = $objectiveName;
            $entry->customName = $text;
            $entry->scoreboardId = $line;
            $entry->score = $line;

            $entries[] = $entry;
        }

        $cpk = SetScorePacket::create(SetScorePacket::TYPE_CHANGE, $entries);

        $this->removeViewer($player, $objectiveName);
        $this->addViewer($player, $objectiveName);

        $this->sortPlayers($objectiveName);
        $player->getNetworkSession()->sendDataPacket($cpk);
    }

    /** Remove a player from viewing a scoreboard
     * @param Player $player Target
     * @param string $objectiveName Scoreboard objective
     */
    public function removeViewer (Player $player, string $objectiveName = self::OBJECTIVE) : void
    {
        if (!in_array($player->getName(), $this->scoreboardPlayers[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot remove player from scoreboard \'' . $objectiveName . '\' as they are not viewing this scoreboard.');
            return;
        }

        if (!isset($this->scoreboardPackets[$objectiveName])) {
            Server::getInstance()->getLogger()->error('[WSB > ScoreAPI]: Cannot remove player to scoreboard \'' . $objectiveName . '\' as the scoreboard does not exist.');
            return;
        }

        $pk = RemoveObjectivePacket::create($objectiveName);

        $player->getNetworkSession()->sendDataPacket($pk);

        unset($this->scoreboardPlayers[$objectiveName][array_search($player->getName(), $this->scoreboardPlayers[$objectiveName])]);
    }

    private function sortPlayers (string $objectiveName) : void
    {
        $players = [];
        foreach ($this->scoreboardPlayers[$objectiveName] as $player) $players[] = $player;
        $this->scoreboardPlayers[$objectiveName] = $players;
    }

    /**
     * @param string $objectiveName
     * @return bool
     */
    public function isScoreboard (string $objectiveName) : bool
    {
        return isset($this->scoreboardPackets[$objectiveName]);
    }

    /**
     * @param Player $player
     * @param string $objectiveName
     * @return bool
     */
    public function hasScoreboard (Player $player, string $objectiveName = self::OBJECTIVE) : bool
    {
        return in_array($player->getName(), $this->scoreboardPlayers[$objectiveName]);
    }
}