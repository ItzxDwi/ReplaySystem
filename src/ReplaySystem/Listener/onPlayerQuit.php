<?php

namespace ReplaySystem\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerQuit implements Listener {

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $entity = $event->getPlayer();
        $level = $entity->getWorld();
        if ($replay = ReplayManager::getActiveReplayByWorld($world)) {
            if ($replay instanceof Replay) {
                $replay->addEntry("Quit", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getVanillaName())]);
            }
        }

    }
}
