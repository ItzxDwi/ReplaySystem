<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 10:24
 */

namespace ReplaySystem\Manager;


use pocketmine\level\Level;
use ReplaySystem\ReplaySystem;
use ReplaySystem\Task\PlayReplayTask;

class ReplayManager {

    public static $allReplays = [];

    /**
     * @param Level $level
     * @return bool
     */
    public static function createReplay(Level $level) {
        if (!self::getActiveReplayByLevel($level)) {
            self::$allReplays = [];
            self::$allReplays[] = new Replay($level);
            //var_dump(self::$allReplays);
            return true;
        }
        return false;
    }

    public static function getActiveReplayByLevel(Level $level) {
        foreach (self::getAllActiveReplays() as $replay) {
            if ($replay instanceof Replay) {
                if ($replay->getLevel()->getFolderName() === $level->getFolderName()) {
                    return $replay;
                }
            }
        }
        return false;
    }

    /**
     * @param Level $level
     * @return bool|mixed
     */
    public static function getReplayByLevel(Level $level) {
        foreach (self::getAllReplays() as $replay) {
            if ($replay instanceof Replay) {
                if ($replay->getLevel()->getFolderName() === $level->getFolderName()) {
                    return $replay;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public static function getAllReplays() {
        return self::$allReplays;
    }

    /**
     * @return array
     */
    public static function getAllActiveReplays() {
        $allActiveReplays = [];
        foreach (self::getAllReplays() as $replay) {
            if ($replay instanceof Replay) {
                if ($replay->isActive()) {
                    $allActiveReplays[] = $replay;
                }
            }
        }
        return $allActiveReplays;
    }

    /**
     * @param Level $level
     * @return bool
     */
    public static function stopReplay(Level $level) {
        $replay = self::getActiveReplayByLevel($level);
        if ($replay instanceof Replay) {
            if ($replay->isActive()) {
                $replay->setActive(false);
                return true;
            }
        }
        return false;
    }

    /**
     * @param Level $level
     * @param int $speed
     * @return bool
     */
    public static function playReplay(Level $level, int $speed) {
        $replay = self::getReplayByLevel($level);
        if ($replay instanceof Replay) {
            if (!$replay->isActive()) {
                if (!$replay->isPlaying()) {
                    $replay->setSpeed($speed);
                    $replay->setPlaying();
                    foreach ($level->getPlayers() as $p) {
                        $p->despawnFromAll();
                        $p->sendMessage(ReplaySystem::PREFIX . " Starting Replay");
                    }
                    $level->getServer()->getLogger()->info(ReplaySystem::PREFIX . " Starting Replay");
                    $level->getServer()->getScheduler()->scheduleRepeatingTask(new PlayReplayTask($replay), 1);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param Level $level
     * @param string $id
     * @return bool
     */
    public static function saveReplay(Level $level, string $id) {
        $replay = self::getReplayByLevel($level);
        if ($replay instanceof Replay) {
            if (!$replay->isActive()) {
                if (!$replay->isPlaying()) {
                    return $replay->saveAs($id);
                }
            }
        }
        return false;
    }

    /**
     * @param string $id
     * @param int $speed
     * @param Level $currentLevel
     * @return bool
     */
    public static function playReplayFromFile(string $id, int $speed, Level $currentLevel){
        $path = ReplaySystem::getInstance()->getDataFolder() . "save/" . $id . ".json";
        if(file_exists($path)) {
            $data = file_get_contents($path);
            $data = unserialize($data);
            if($currentLevel->getFolderName() === $data["levelname"]) {
                $level = ReplaySystem::getInstance()->getServer()->getLevelByName($data["levelname"]);
                if ($level instanceof Level) {
                    $replay = new Replay($level, $data);
                    $replay->setSpeed($speed);
                    $replay->setPlaying();
                    foreach ($level->getPlayers() as $p) {
                        $p->despawnFromAll();
                        $p->sendMessage(ReplaySystem::PREFIX . " Starting Saved Replay");
                    }
                    $level->getServer()->getLogger()->info(ReplaySystem::PREFIX . " Starting Saved Replay");
                    $level->getServer()->getScheduler()->scheduleRepeatingTask(new PlayReplayTask($replay), 1);
                    return true;
                }
            }
        }
        return false;
    }
}