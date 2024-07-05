<?php

namespace ReplaySystem\Listener;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onEntityDamage implements Listener {

    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $world = $entity->getWorld();
        if ($entity->saveNBT()->getTag("ReplayEntity") !== null) {
            $event->cancel();
        } elseif (!$event->isCancelled()) {
            if ($replay = ReplayManager::getActiveReplayByWorld($world)) {
                if ($replay instanceof Replay) {
                    $replay->addEntry("Damage", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getVanillaName()]);
                }
            }

        }
    }
}
