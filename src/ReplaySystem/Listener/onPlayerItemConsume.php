<?php

namespace ReplaySystem\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerItemConsume implements Listener {

    public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
        if (!$event->isCancelled()) {
            $entity = $event->getPlayer();
            $level = $entity->getWorld();
            if ($replay = ReplayManager::getActiveReplayByWorld($world)) {
                if ($replay instanceof Replay) {
                    $replay->addEntry("Consume", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getVanillaName())]);
                }
            }
        }
    }
}
