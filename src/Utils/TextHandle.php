<?php

namespace WolfDen133\WSB\Utils;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use WolfDen133\WSB\Event\TagReplaceEvent;
use WolfDen133\WSB\WSB;

class TextHandle
{
    private static function getInternalTags (Player $player) : array
    {
        $self = WSB::getInstance();

        return [
            "#" => "\n",
            "{NAME}" => $player->getName(),
            "{REAL_NAME}" => $player->getName(),
            "{DISPLAY_NAME}" => $player->getDisplayName(),
            "{PING}" => $player->getNetworkSession()->getPing(),
            "{ONLINE_PLAYERS}" => count($self->getServer()->getOnlinePlayers()),
            "{MAX_PLAYERS}" => $self->getServer()->getMaxPlayers(),
            "{X}" => (int)$player->getPosition()->getX(),
            "{Y}" => (int)$player->getPosition()->getY(),
            "{Z}" => (int)$player->getPosition()->getZ(),
            "{REAL_TPS}" => $self->getServer()->getTicksPerSecond(),
            "{TPS}" => $self->getServer()->getTicksPerSecondAverage(),
            "{REAL_LOAD}" => $self->getServer()->getTickUsage(),
            "{LOAD}" => $self->getServer()->getTickUsageAverage(),
            "{LEVEL_NAME}" => $player->getWorld()->getDisplayName(),
            "{LEVEL_FOLDER_NAME}" => $player->getWorld()->getFolderName(),
            "{LEVEL_PLAYERS}" => count($player->getWorld()->getPlayers()),
            "{CONNECTION_IP}" => $player->getNetworkSession()->getIp(),
            "{SERVER_IP}" => $self->getServer()->getIP(),
            "{TIME}" => date($self->getConfig()->get("time-format")),
            "{DATE}" => date($self->getConfig()->get("date-format"))
        ];
    }

    public static function getFormattedText (array $rawLines, Player $player) : array
    {
        $internalTags = self::getInternalTags($player);

        $ev = new TagReplaceEvent($internalTags, $player);
        $ev->call();

        foreach ($rawLines as $index => $line) {
            $text = TextFormat::colorize($line);
            foreach ($ev->getTags() as $find => $replace) {
                $text = str_replace($find, $replace, $text);
            }
            $rawLines[$index] = $text;
        }

        return $rawLines;
    }
}