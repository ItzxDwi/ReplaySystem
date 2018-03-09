<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 22:10
 */

namespace ReplaySystem\Listener;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use ReplaySystem\Manager\Replay;
use ReplaySystem\Manager\ReplayManager;

class onPlayerToggleSneak implements Listener {

    public function onPlayerToggleSneak(PlayerToggleSneakEvent $event) {
        if (!$event->isCancelled()) {
            $entity = $event->getPlayer();
            $level = $entity->getLevel();
            if ($replay = ReplayManager::getActiveReplayByLevel($level)) {
                if ($replay instanceof Replay) {
                    $replay->addEntry("Sneak", $entity->getId(), ["Sneak" => $entity->isSneaking()], ["Id" => $entity->getInventory()->getItemInHand()->getId()]);
                }
            }
        }
    }
}