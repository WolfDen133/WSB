<?php

namespace WolfDen133\WSB\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use WolfDen133\WSB\WSB;

class WSBCommand extends Command implements PluginOwned
{
    private WSB $plugin;

    public function __construct (WSB $plugin)
    {
        parent::__construct('wsb');

        $this->setDescription('Enable or disable your in-game scoreboard');
        $this->setPermission('wsb.command.use');

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage('Sorry, this command is for players only.');
            return;
        }

        if (empty($args)) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) $sender->sendMessage('Usage: /wsb <on, off, reload>');
            else $sender->sendMessage('Usage: /wsb <on, off>');
            return;
        }

        switch ($args[0]) {
            case 'on':
            case 'enable':
                WSB::getAPI()->removeBlacklist($sender);
                $sender->sendMessage('You will now see the scoreboard.');
                break;

            case 'off':
            case 'disable':
                WSB::getAPI()->addBlacklist($sender);
                $sender->sendMessage('You will no longer see the scoreboard.');
                break;

            case 'reload':
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage('Usage: /wsb <on, off>');
                    return;
                }

                WSB::getAPI()->reloadScoreboards();
                $sender->sendMessage('Successfully reloaded scoreboards!');
                break;

            default:
                $sender->sendMessage('Usage: /wsb <on, off>');
        }
    }

    public function getOwningPlugin () : Plugin
    {
        return $this->plugin;
    }
}