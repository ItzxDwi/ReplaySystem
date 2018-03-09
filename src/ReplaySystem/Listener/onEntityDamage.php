<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 20:43
 */

namespace ReplaySystem\Listener;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onEntityDamage implements Listener {

    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $level = $entity->getLevel();
        if ($entity->namedtag->hasTag("ReplayEntity")) {
            $event->setCancelled();
        } elseif (!$event->isCancelled()) {
            if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
                if ($replay instanceof Replay) {
                    //var_dump("EntityDamageEvent");
                    $replay->addEntry("Damage", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
                }
            }

        }
    }
}