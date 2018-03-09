<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 08.03.2018
 * Time: 14:23
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerQuit implements Listener {

    public function onPlayerQuit(PlayerQuitEvent $event) {
        //var_dump("onPlayerQuit");
        $entity = $event->getPlayer();
        $level = $entity->getLevel();
        if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
            if ($replay instanceof Replay) {
                $replay->addEntry("Quit", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
            }
        }

    }
}