<?php

namespace ReplaySystem\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerDeath implements Listener {

    public function onPlayerDeath(PlayerDeathEvent $event) {
        $entity = $event->getEntity();
        $level = $entity->getWorld();
        if ($replay = ReplayManager::getActiveReplayByWorld($world)) {
            if ($replay instanceof Replay) {
                $replay->addEntry("Death", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getVanillaName()]);
            }

        }
    }
}
