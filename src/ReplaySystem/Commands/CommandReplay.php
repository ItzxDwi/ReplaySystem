<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 12:24
 */

namespace ReplaySystem\Commands;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ReplaySystem\Manager\ReplayManager;
use ReplaySystem\ReplaySystem;

class CommandReplay extends Command {

    public function __construct() {
        parent::__construct("replay", "", "ReplaySystem Main Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($sender instanceof Player) {
            $player = $sender;
            if (!empty($args[0])) {
                if ($player->hasPermission("replay.command")) {
                    if ($args[0] === "start") {
                        if (ReplayManager::createReplay($player->getLevel())) {
                            $player->sendMessage(ReplaySystem::PREFIX . " Replay started recording");
                            return true;
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cReplay record error");
                        return false;

                    } elseif ($args[0] === "stop") {
                        if (ReplayManager::stopReplay($player->getLevel())) {
                            $player->sendMessage(ReplaySystem::PREFIX . " Replay started stoping");
                            return true;
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cReplay stop error");
                        return false;
                    } elseif ($args[0] === "play") {
                        $speed = 1;
                        if (!empty($args[1])) {
                            $int = intval($args[1]);
                            if (!is_null($int)) {
                                if ($int >= 1) {
                                    $speed = $int;
                                }
                            }
                        }
                        if (ReplayManager::playReplay($player->getLevel(), $speed)) {
                            $player->sendMessage(ReplaySystem::PREFIX . " Replay started playing (Speed " . $speed . "x)");
                            return true;
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cReplay play error");
                        return false;
                    } elseif ($args[0] === "playsave") {
                        $speed = 1;
                        if (!empty($args[2])) {
                            $int = intval($args[2]);
                            if (!is_null($int)) {
                                if ($int >= 1) {
                                    $speed = $int;
                                }
                            }
                        }
                        if (ReplayManager::playReplayFromFile($args[1], $speed, $player->getLevel())) {
                            $player->sendMessage(ReplaySystem::PREFIX . " Saved Replay started playing (Name " . $args[1] . ", Speed " . $speed . "x)");
                            return true;
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cSaved Replay play error");
                        return false;
                    } elseif ($args[0] === "listsave") {
                        $player->sendMessage(ReplaySystem::PREFIX . "§7------" . ReplaySystem::PREFIX . "------");
                        foreach (scandir(ReplaySystem::getInstance()->getDataFolder() . "save/") as $d) {
                            if(!in_array($d, [".", ".."])) {
                                $player->sendMessage(ReplaySystem::PREFIX . "§7-> §e" . str_replace(".json", "", $d));
                            }
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . "§7------" . ReplaySystem::PREFIX . "------");
                        return true;

                    } elseif ($args[0] === "deletesave") {
                        if (!empty($args[1])) {
                            if (ReplayManager::deleteReplayByName($args[1])) {
                                $player->sendMessage(ReplaySystem::PREFIX . " Replay successfully deleted");
                                return true;
                            }
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cReplay delete error");
                        return false;

                    } elseif ($args[0] === "save") {
                        if (!empty($args[1])) {
                            if (ReplayManager::saveReplay($player->getLevel(), $args[1])) {
                                $player->sendMessage(ReplaySystem::PREFIX . " Replay started saving");
                                return true;
                            }
                        }
                        $player->sendMessage(ReplaySystem::PREFIX . " §cReplay save error");
                        return false;
                    }
                }

            }
        }
        $sender->sendMessage(ReplaySystem::PREFIX . "§7------" . ReplaySystem::PREFIX . "------");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay start");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay stop");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay play [speed:1]");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay save [name]");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay playsave [name] [speed:1]");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay deletesave [name]");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7-> §e/replay listsave");
        $sender->sendMessage(ReplaySystem::PREFIX . "§7------" . ReplaySystem::PREFIX . "------");
    }
}