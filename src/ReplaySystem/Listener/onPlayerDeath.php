<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 08.03.2018
 * Time: 14:55
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerDeath implements Listener {

    public function onPlayerDeath(PlayerDeathEvent $event) {
        //var_dump("onPlayerDeath");
        $entity = $event->getEntity();
        $level = $entity->getLevel();
        if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
            if ($replay instanceof Replay) {
                $replay->addEntry("Death", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
            }

        }
    }
}