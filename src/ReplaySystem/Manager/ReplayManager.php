<?php

namespace ReplaySystem\Manager;

use pocketmine\world\World;
use ReplaySystem\ReplaySystem;
use ReplaySystem\Task\PlayReplayTask;

class ReplayManager {

    public static $allReplays = [];

    /**
     * @param World $world
     * @return bool
     */
    public static function createReplay(World $world) {
        if (!self::getActiveReplayByWorld($world)) {
           // self::$allReplays = [];
            self::$allReplays[] = new Replay($world);
            return true;
        }
        return false;
    }

    public static function getActiveReplayByWorld(World $world) {
        foreach (self::getAllActiveReplays() as $replay) {
            if ($replay instanceof Replay) {
                if ($replay->getWorld()->getFolderName() === $world->getFolderName()) {
                    return $replay;
                }
            }
        }
        return false;
    }

    /**
     * @param World $world
     * @return bool|mixed
     */
    public static function getReplayByWorld(World $world) {
        foreach (self::getAllReplays() as $replay) {
            if ($replay instanceof Replay) {
                if ($replay->getWorld()->getFolderName() === $world->getFolderName()) {
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
     * @param World $world
     * @return bool
     */
    public static function stopReplay(World $world) {
        $replay = self::getActiveReplayByWorld($world);
        if ($replay instanceof Replay) {
            if ($replay->isActive()) {
                $replay->setActive(false);
                return true;
            }
        }
        return false;
    }

    /**
     * @param World $world
     * @param int $speed
     * @return bool
     */
    public static function playReplay(World $world, int $speed) {
        $replay = self::getReplayByWorld($world);
        if ($replay instanceof Replay) {
            if (!$replay->isActive()) {
                if (!$replay->isPlaying()) {
                    $replay->setSpeed($speed);
                    $replay->setPlaying();
                    foreach ($world->getPlayers() as $p) {
                        $p->despawnFromAll();
                        $p->sendMessage(ReplaySystem::PREFIX . " Starting Replay");
                    }
                    Server::getInstance()->getScheduler()->scheduleRepeatingTask(new PlayReplayTask($replay), 1);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param World $world
     * @param string $id
     * @return bool
     */
    public static function saveReplay(World $world, string $id) {
        $replay = self::getReplayByWorld($world);
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
     * @param World $currentWorld
     * @return bool
     */
    public static function playReplayFromFile(string $id, int $speed, World $currentWorld){
        $path = ReplaySystem::getInstance()->getDataFolder() . "save/" . $id . ".json";
        if(file_exists($path)) {
            $data = $file_get_contents($path);
            $data = unserialize($data);
            if($currentWorld->getFolderName() === $data["worldname"]) {
                $world = ReplaySystem::getInstance()->getServer()->getWorldManager()->getWorldByName($data["levelname"]);
                if ($level instanceof World) {
                    $replay = new Replay($world, $data);
                    $replay->setSpeed($speed);
                    $replay->setPlaying();
                    foreach ($world->getPlayers() as $p) {
                        $p->despawnFromAll();
                        $p->sendMessage(ReplaySystem::PREFIX . " Starting Saved Replay");
                    }
                    Server::getInstance()->getScheduler()->scheduleRepeatingTask(new PlayReplayTask($replay), 1);
                    return true;
                }
            }
        }
        return false;
    }

    public static function deleteReplayByName(string $id){
        $path = ReplaySystem::getInstance()->getDataFolder() . "save/" . $id . ".json";
        if(file_exists($path)) {
            unlink($path);
            return true;
        }
        return false;
    }
}
