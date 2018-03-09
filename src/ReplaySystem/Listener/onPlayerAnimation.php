<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 08.03.2018
 * Time: 09:05
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerAnimationEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerAnimation implements Listener {

    public function onPlayerAnimation(PlayerAnimationEvent $event){
        //var_dump("onPlayerAnimation");
        $entity = $event->getPlayer();
        $level = $entity->getLevel();
        if($replay = ReplayManager::getActiveReplayByLevel($level)){
            if($replay instanceof Replay){
                $replay->addEntry("Animation", $entity->getId(), ["Animation" => $event->getAnimationType()], ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
            }
        }
    }
}