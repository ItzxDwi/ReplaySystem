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

        if ($this->checkUpdate()) {
            $this->getServer()->shutdown();
            return false;
        }

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

    /**
     * @return bool
     */
    public function checkUpdate() {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $datei = file_get_contents("https://raw.githubusercontent.com/McpeBooster/ReplaySystem-McpeBooster/master/plugin.yml", false, stream_context_create($arrContextOptions));
        if (!$datei)
            return false;
        $datei = str_replace("\n", "", $datei);
        $newversion = explode("version: ", $datei);
        $newversion = explode("api: ", $newversion[1]);
        $newversion = $newversion[0];
        //var_dump($newversion);
        $plugin = $this->getServer()->getPluginManager()->getPlugin("ReplaySystem");
        $version = $plugin->getDescription()->getVersion();
        //var_dump($version);
        if (!($version === $newversion)) {
            $update = false;
            if (intval($version[0]) < intval($newversion[0])) {
                $update = true;
            } elseif (intval($version[0]) === intval($newversion[0])) {
                if (intval($version[1]) < intval($newversion[1])) {
                    $update = true;
                } elseif (intval($version[1]) === intval($newversion[1])) {
                    if (intval($version[2]) < intval($newversion[2])) {
                        $update = true;
                    }
                }
            }
            if ($update) {
                $this->getLogger()->info("§aNew Update available!");
                $this->getLogger()->info("§7Local Version: §6" . $version);
                $this->getLogger()->info("§7Newest Version: §6" . $newversion);
                $this->getLogger()->info("§aPlease Download the Newest Version... §7(" . $newversion . ")");
                /*$path = dirname(__FILE__);
                if (is_dir($path)) {
                    $this->updateDir(str_replace("src/ReplaySystem", "", $path));
                } else {
                    $file = @file_get_contents("https://raw.githubusercontent.com/McpeBooster/ReplaySystem-McpeBooster/master/release/ReplaySystem" . $newversion . ".phar", false, stream_context_create($arrContextOptions));
                    if ($file) {
                        file_put_contents($path, $file);
                    } else {
                        $this->getLogger()->emergency("Error while downloading... §7(" . $newversion . ")");
                        return false;
                    }
                }
                $this->getLogger()->info("§aSuccessfully downloaded Newest Version... §7(" . $newversion . ")");*/
                return true;
            }
        }
        $this->getLogger()->info("§aReplaySystem has the Latest Version!");
        $this->getLogger()->info("§7Local Version: §6" . $version);
        $this->getLogger()->info("§7Newest Version: §6" . $newversion);
        return false;
    }
    /**
     * @param $path
     * @return bool
     */
    public function updateDir($path) {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        //foreach(scandir($path) as $f) {
        foreach (glob($path . "*") as $f) {
            if (!in_array($f, [".", ".."]) && !($f == $path)) {
                if (is_dir($f)) {
                    $this->updateDir($f . "/");
                } else {
                    $url1 = str_replace($this->getServer()->getDataPath(), "", str_replace("plugins", "", $f));
                    /* var_dump("Server: " . $this->getServer()->getDataPath());
                      var_dump("Datei: " . $f);
                      var_dump("New Path: " . $url1); */
                    $url2 = explode("/", $url1);
                    unset($url2[1]);
                    $url3 = "";
                    foreach ($url2 as $u2) {
                        if (!($u2 == "")) {
                            $url3 = $url3 . "/" . $u2;
                        }
                    }
                    //var_dump($url3);
                    if ($d = @file_get_contents("https://raw.githubusercontent.com/McpeBooster/ReplaySystem-McpeBooster/master" . $url3, false, stream_context_create($arrContextOptions))) {
                        file_put_contents($f, $d);
                    }
                }
            }
        }
        return true;
    }

}