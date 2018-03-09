<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 08.03.2018
 * Time: 13:02
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerItemConsume implements Listener {

    public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
        if (!$event->isCancelled()) {
            $entity = $event->getPlayer();
            $level = $entity->getLevel();
            if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
                if ($replay instanceof Replay) {
                    $replay->addEntry("Consume", $entity->getId(), null, ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
                }
            }
        }
    }
}