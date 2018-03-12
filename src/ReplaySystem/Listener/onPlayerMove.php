<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 10:22
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerMove implements Listener {

    public function onEntityMotion(PlayerMoveEvent $event){
        //var_dump("onPlayerMove");
        if (!$event->isCancelled()) {
            $entity = $event->getPlayer();
            $level = $entity->getLevel();
            if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
                if ($replay instanceof Replay) {
                    $replay->addEntity($entity);
                    $replay->addEntry("Move", $entity->getId(), ["Position" => ["X" => $event->getTo()->x, "Y" => $event->getTo()->y, "Z" => $event->getTo()->z, "Yaw" => $entity->getYaw(), "Pitch" => $entity->getPitch()]], ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
                }
            }
        }
    }

}