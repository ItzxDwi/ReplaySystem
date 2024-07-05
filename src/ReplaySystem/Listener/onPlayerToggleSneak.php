<?php

namespace ReplaySystem\Listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerToggleSneak implements Listener {

    public function onPlayerToggleSneak(PlayerToggleSneakEvent $event) {
        if (!$event->isCancelled()) {
            $entity = $event->getPlayer();
            $level = $entity->getWorld();
            if ($replay = ReplayManager::getActiveReplayByWorld($world)) {
                if ($replay instanceof Replay) {
                    $replay->addEntry("Sneak", $entity->getId(), ["Sneak" => $entity->isSneaking()], ["Id" => $entity->getInventory()->getItemInHand()->getVanillaName()]);
                }
            }
        }
    }
}
