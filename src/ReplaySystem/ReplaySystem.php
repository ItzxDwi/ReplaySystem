<?php

/*
 *
 * o     o                       .oPYo.                        o
 * 8b   d8                       8   `8                        8
 * 8`b d'8 .oPYo. .oPYo. .oPYo. o8YooP' .oPYo. .oPYo. .oPYo.  o8P .oPYo. oPYo.
 * 8 `o' 8 8    ' 8    8 8oooo8  8   `b 8    8 8    8 Yb..     8  8oooo8 8  `'
 * 8     8 8    . 8    8 8.      8    8 8    8 8    8   'Yb.   8  8.     8
 * 8     8 `YooP' 8YooP' `Yooo'  8oooP' `YooP' `YooP' `YooP'   8  `Yooo' 8
 * ..::::..:.....:8 ....::.....::......::.....::.....::.....:::..::.....:..::::
 * :::::::::::::::8 :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 * :::::::::::::::..:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 *
 *
 * Plugin made by McpeBooster
 *
 * Author: McpeBooster
 * Twitter: @McpeBooster
 * Website: McpeBooster.tk
 * E-Mail: mcpebooster@gmail.com
 * YouTube: http://YouTube.com/c/McpeBooster
 * GitHub: http://GitHub.com/McpeBooster
 *
 * ©McpeBooster
 */

/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 10:17
 */

namespace ReplaySystem;


use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use ReplaySystem\Commands\CommandReplay;
use ReplaySystem\Listener\onEntityDamage;
use ReplaySystem\Listener\onPlayerAnimation;
use ReplaySystem\Listener\onPlayerDeath;
use ReplaySystem\Listener\onPlayerItemConsume;
use ReplaySystem\Listener\onPlayerMove;
use ReplaySystem\Listener\onPlayerQuit;
use ReplaySystem\Listener\onPlayerToggleSneak;

class ReplaySystem extends PluginBase {

    const PREFIX = "§7[§6ReplaySystem§7]";

    public static $instance;
    public $baseLang;

    public function onEnable() {
        $this->getLogger()->info(self::PREFIX . " by §6McpeBooster§7!");

        self::$instance = $this;

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
        foreach($this->getServer()->getLevels() as $level){
            if($level instanceof Level){
                foreach ($level->getEntities() as $entity){
                    if($entity->namedtag->hasTag("ReplayEntity")) {
                        $entity->close();
                    }
                }
            }
        }
    }

    /**
     * @return ReplaySystem
     */
    public static function getInstance(): ReplaySystem {
        return self::$instance;
    }

}