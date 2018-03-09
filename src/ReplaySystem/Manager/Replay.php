<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 10:29
 */

namespace ReplaySystem\Manager;


use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use ReplaySystem\ReplaySystem;

class Replay {

    const REPLAY_INACTIVE = 0;
    const REPLAY_ACTIVE = 1;
    const REPLAY_PLAYING = 2;

    private $state;
    private $level;

    private $start;
    private $stop;

    private $speed = 3;

    private $replayData = [];
    private $entityData = [];

    public function __construct(Level $level) {
        $this->level = $level;
        $this->start = $level->getServer()->getTick();

        $this->setActive();
    }


    /**
     * @return bool
     */
    public function isActive() {
        return ($this->state === self::REPLAY_ACTIVE);
    }

    /**
     * @param bool $active
     * @return int
     */
    public function setActive(bool $active = true) {
        if ($active === true)
            return ($this->state = self::REPLAY_ACTIVE);

        $this->state = self::REPLAY_INACTIVE;
        $this->stop = $this->level->getServer()->getTick();
        return true;
    }

    /**
     * @return Level
     */
    public function getLevel(): Level {
        return $this->level;
    }

    /**
     * @param string $action
     * @param int|null $entityid
     * @param null $data
     * @param Item|null $item
     * @return array
     */
    public function addEntry(string $action, int $entityid = null, $data = null, $item = null) {
        return $this->replayData[$this->level->getServer()->getTick() - $this->start][] = ["Action" => $action, "EntityId" => $entityid, "Data" => $data, "Item" => $item];
    }

    /**
     * @param int $entityid
     * @return bool
     */
    public function isRegisteredEntity(int $entityid) {
        return isset($this->entityData[$entityid]);
    }

    /**
     * @param int $entityid
     * @return bool|mixed
     */
    public function getEntityById(int $entityid) {
        if (!$this->isRegisteredEntity($entityid))
            return false;

        return $this->entityData[$entityid];
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function addEntity(Entity $entity) {
        if ($this->isRegisteredEntity($entity->getId()))
            return true;

        $this->entityData[$entity->getId()] = ["Entity" => $entity, "NETWORK_ID" => $entity::NETWORK_ID, "Spawned" => false];
        return true;
    }

    /**
     * @return array
     */
    public function getEntityData() {
        return $this->entityData;
    }

    /**
     * @return array
     */
    public function getReplayData() {
        return $this->replayData;
    }

    /**
     * @return int
     */
    public function getStart(): int {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getStop(): int {
        return $this->stop;
    }

    /**
     * @return int
     */
    public function getSpeed(): int {
        return $this->speed;
    }

    /**
     * @param int $speed
     * @return int
     */
    public function setSpeed(int $speed = 1): int {
        return $this->speed = $speed;
    }

    /**
     * @return bool
     */
    public function isPlaying() {
        return ($this->state === self::REPLAY_PLAYING);
    }

    /**
     * @param bool $playing
     * @return bool|int
     */
    public function setPlaying(bool $playing = true) {
        if ($playing === true)
            return ($this->state = self::REPLAY_PLAYING);

        $this->state = self::REPLAY_INACTIVE;
        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    /*public function saveAs(string $id){
        $path = ReplaySystem::getInstance()->getDataFolder() . "save/" . $id . ".json";
        $data = [
            "state"=> $this->state,
            "level"=> $this->level,
            "start"=> $this->start,
            "stop"=> $this->stop,
            "replayData"=> $this->replayData,
            "entityData"=> $this->entityData,
        ];
        file_put_contents($path, json_encode($data));
        return true;
    }*/
}