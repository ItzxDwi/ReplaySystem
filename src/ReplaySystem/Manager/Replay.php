<?php

namespace ReplaySystem\Manager;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\world\World;
use pocketmine\Server;
use ReplaySystem\ReplaySystem;

class Replay {

    const REPLAY_INACTIVE = 0;
    const REPLAY_ACTIVE = 1;
    const REPLAY_PLAYING = 2;

    private $state;
    private $world;

    private $start;
    private $stop;

    private $speed = 3;

    private $replayData = [];
    private $entityData = [];

    public function __construct(World $world, $data = null) {
        $this->world = $world;
        $this->start = Server::getInstance()->getTick();

        if(is_array($data)) {
            foreach ($data as $index => $d){
                if($index === "state")
                    $this->state = $d;
                if($index === "start")
                    $this->start = $d;
                if($index === "stop")
                    $this->stop = $d;
                if($index === "replayData")
                    $this->replayData = $d;
                if($index === "entityData")
                    $this->entityData = $d;
            }
        }

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
        $this->stop = Server::getInstance()->getTick();
        return true;
    }

    public function getWorld(): World {
        return $this->world;
    }

    /**
     * @param string $action
     * @param int|null $entityid
     * @param null $data
     * @param Item|null $item
     * @return array
     */
    public function addEntry(string $action, int $entityid = null, $data = null, $item = null) {
        return $this->replayData[Server::getInstance()->getTick() - $this->start][] = ["Action" => $action, "EntityId" => $entityid, "Data" => $data, "Item" => $item];
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

        $sdata = null;
        $sname = null;
        if($entity instanceof Human){
            $sdata = $entity->getSkin()->getSkinData();
            $sname = $entity->getSkin()->getSkinId();
        }
        $this->entityData[$entity->getId()] = ["Entity" => ["NETWORK_ID" => $entity::NETWORK_ID, "Position" => ["X" => $entity->x, "Y" => $entity->y, "Z" => $entity->z], "Skin" => ["Data" => $sdata, "Name" => $sname], "NameTag" => $entity->getNameTag()], "Spawned" => false];
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
    public function saveAs(string $id){
        $path = ReplaySystem::getInstance()->getDataFolder() . "save/" . $id . ".json";
        if(!file_exists($path)) {
            $data = [
                "state" => $this->state,
                "worldname" => $this->world->getFolderName(),
                "start" => $this->start,
                "stop" => $this->stop,
                "replayData" => $this->replayData,
                "entityData" => $this->entityData,
            ];
            file_put_contents($path, serialize($data));
            return true;
        }
        return false;
    }
}
