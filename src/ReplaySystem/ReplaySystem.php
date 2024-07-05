<?php

namespace ReplaySystem;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ReplaySystem\Commands\CommandReplay;
use ReplaySystem\Listener\onEntityDamage;
use ReplaySystem\Listener\onPlayerAnimation;
use ReplaySystem\Listener\onPlayerDeath;
use ReplaySystem\Listener\onPlayerItemConsume;
use ReplaySystem\Listener\onPlayerMove;
use ReplaySystem\Listener\onPlayerQuit;
use ReplaySystem\Listener\onPlayerToggleSneak;

class ReplaySystem extends PluginBase {
    use SingletonTrait;

    const PREFIX = "§7[§6ReplaySystem§7]";

    public function onEnable() {
        self::setInstance($this);

        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "save/");

        $this->getServer()->getPluginManager()->registerEvents(new onPlayerMove(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onEntityDamage(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onPlayerToggleSneak(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onPlayerAnimation(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onPlayerItemConsume(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onPlayerQuit(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new onPlayerDeath(), $this);

        $this->getServer()->getCommandMap()->register("ReplaySystem", new CommandReplay());
    }

    public function onDisable(){
        foreach($this->getServer()->getWorldManager()->getWorlds() as $world){
            foreach($world->getEntities() as $entity){
                if($entity->saveNBT()->getTag("ReplayEntity") !== null){
                    $entity->close();
                }
            }
        }
    }
}
