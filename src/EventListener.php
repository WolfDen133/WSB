<?php

namespace WolfDen133\WSB;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class EventListener implements Listener
{
    public function onPlayerJoinEvent (PlayerJoinEvent $event) : void
    {
        WSB::getAPI()->sendScoreboard($event->getPlayer(), $event->getPlayer()->getPosition()->getWorld()->getFolderName());
    }

    public function onPlayerQuitEvent (PlayerQuitEvent $event) : void
    {
        WSB::getAPI()->removeScoreboard($event->getPlayer());
    }

    public function onPlayerLevelChangeEvent (EntityTeleportEvent $event) : void
    {
        if (!($event->getEntity() instanceof Player)) return;
        if ($event->getFrom()->getWorld()->getFolderName() == $event->getTo()->getWorld()->getFolderName()) return;

        /** @var Player $player */
        $player = $event->getEntity();

        WSB::getAPI()->removeScoreboard($player);
        WSB::getAPI()->sendScoreboard($player, $event->getTo()->getWorld()->getFolderName());
    }

    public function onDataPacketSendEvent (DataPacketSendEvent $event) : void
    {
        foreach ($event->getPackets() as $packet) {
            if (!($packet instanceof AvailableCommandsPacket)) continue;

            foreach ($event->getTargets() as $target) {
                $args = [
                    [
                        CommandParameter::enum('action', new CommandEnum('blacklist', ['on', 'off']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM)
                    ]
                ];

                if ($target->getPlayer()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $args[] = [
                        CommandParameter::enum('reload', new CommandEnum('reload', ['reload']), CommandParameter::FLAG_FORCE_COLLAPSE_ENUM)
                    ];
                }

                if (isset($packet->commandData['wsb'])) $packet->commandData['wsb']->overloads = $args;
            }
        }
    }
}