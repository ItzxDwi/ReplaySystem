<?php
/**
 * Created by PhpStorm.
 * User: McpeBooster
 * Date: 07.03.2018
 * Time: 13:44
 */

namespace ReplaySystem\Task;


use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\scheduler\PluginTask;
use ReplaySystem\Manager\Replay;
use ReplaySystem\ReplaySystem;

class PlayReplayTask extends PluginTask {

    private $plugin;
    private $replay;
    private $start;
    private $stop;
    private $speed;
    private $tempStart = 0;
    private $tempReplayData = [];
    private $tempEntityData = [];
    private $spawendEntity = [];

    public function __construct(Replay $replay) {
        $this->plugin = ReplaySystem::getInstance();
        $this->replay = $replay;
        $this->start = $replay->getStart();
        $this->stop = $replay->getStop();
        $this->speed = $replay->getSpeed();
        $this->tempReplayData = $replay->getReplayData();
        $this->tempEntityData = $replay->getEntityData();
        parent::__construct($this->plugin);
    }

    public function onRun(int $currentTick) {
        $this->tempStart = $this->tempStart + $this->speed;
        if (isset($this->tempReplayData[$this->tempStart])) {
            foreach ($this->tempReplayData[$this->tempStart] as $sequenz) {
                $edata = $this->tempEntityData[$sequenz["EntityId"]]["Entity"];
                if (!($this->tempEntityData[$sequenz["EntityId"]]["Spawned"])) {
                    if ($edata instanceof Entity) {
                        if ($edata instanceof Human) {
                            $nbt = new CompoundTag("", [
                                "Pos" => new ListTag("Pos", [
                                    new DoubleTag("", $edata->getX()),
                                    new DoubleTag("", $edata->getY()),
                                    new DoubleTag("", $edata->getZ())
                                ]),
                                "Motion" => new ListTag("Motion", [
                                    new DoubleTag("", 0),
                                    new DoubleTag("", 0),
                                    new DoubleTag("", 0)
                                ]),
                                "Rotation" => new ListTag("Rotation", [
                                    new FloatTag("", 90),
                                    new FloatTag("", 0)
                                ]),
                                "Skin" => new CompoundTag("Skin", [
                                        "Data" => new StringTag("Data", $edata->getSkin()->getSkinData()),
                                        "Name" => new StringTag("Name", $edata->getSkin()->getSkinId())
                                    ]
                                ),
                                "ReplayEntity" => new StringTag("ReplayEntity", "true"),
                            ]);

                            $entity = new Human($this->replay->getLevel(), $nbt);

                            if (!is_null($edata->getNameTag()))
                                $entity->setNameTag($edata->getNameTag());

                            $entity->setNameTagVisible(true);
                            $entity->setNameTagAlwaysVisible(true);
                            $entity->spawnToAll();
                            $this->spawendEntity[$sequenz["EntityId"]] = $entity;
                            $this->tempEntityData[$sequenz["EntityId"]]["Spawned"] = true;

                        }
                    }
                }
                if ($sequenz["Action"] === "Move") {

                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $vec3 = $sequenz["Data"];
                        if ($vec3 instanceof Location) {
                            if ($entity instanceof Human) {
                                $entity->newPosition = $vec3;
                                $entity->setRotation($vec3->getYaw(), $vec3->getPitch());
                                $this->processMovement($entity);

                                if(!($entity->getInventory()->getItemInHand()->getId() === $sequenz["Item"]["Id"])) {
                                    $entity->getInventory()->setItemInHand(Item::get($sequenz["Item"]["Id"]));
                                    $entity->getInventory()->sendHeldItem($entity->getViewers());
                                }
                            }
                        }
                    }
                } elseif ($sequenz["Action"] === "Damage") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if($entity instanceof Entity){
                        $entity->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION);
                    }
                } elseif ($sequenz["Action"] === "Sneak") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $entity->setSneaking($sequenz["Data"]["Sneak"]);
                    }
                } elseif ($sequenz["Action"] === "Animation") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $pk = new AnimatePacket();
                        $pk->entityRuntimeId = $entity->getId();
                        $pk->action = $sequenz["Data"]["Animation"];
                        $this->plugin->getServer()->broadcastPacket($entity->getViewers(), $pk);
                    }
                } elseif ($sequenz["Action"] === "Consume") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        if(!($entity->getInventory()->getItemInHand()->getId() === $sequenz["Item"]["Id"])) {
                            $entity->getInventory()->setItemInHand(Item::get($sequenz["Item"]["Id"]));
                            $entity->getInventory()->sendHeldItem($entity->getViewers());
                        }

                        $entity->broadcastEntityEvent(EntityEventPacket::EATING_ITEM);
                    }
                } elseif ($sequenz["Action"] === "Quit") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $entity->despawnFromAll();
                        $entity->close();
                        unset($this->spawendEntity[$sequenz["EntityId"]]);
                        $this->tempEntityData[$sequenz["EntityId"]]["Spawned"] = false;
                    }
                } elseif ($sequenz["Action"] === "Death") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $entity->getInventory()->setItemInHand(Item::get(Item::AIR));
                        $entity->getInventory()->sendHeldItem($entity->getViewers());
                        $entity->broadcastEntityEvent(EntityEventPacket::DEATH_ANIMATION);
                        $entity->broadcastEntityEvent(EntityEventPacket::RESPAWN);
                        $entity->spawnToAll();
                    }
                }
            }
        }
        if ($this->tempStart >= ($this->stop - $this->start)) {
            foreach ($this->spawendEntity as $entity) {
                if ($entity instanceof Entity) {
                    $entity->close();
                }
            }
            foreach ($this->replay->getLevel()->getPlayers() as $p) {
                $p->spawnToAll();
                $p->sendMessage(ReplaySystem::PREFIX . " Stopped Replay");
            }
            $this->plugin->getServer()->getLogger()->info(ReplaySystem::PREFIX . " Stopped Replay");
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $this->replay->setPlaying(false);
        }
    }

    private function processMovement(Entity $entity) {
        $newPos = $entity->newPosition;

        $dx = $newPos->x - $entity->x;
        $dy = $newPos->y - $entity->y;
        $dz = $newPos->z - $entity->z;
        $entity->move($dx, $dy, $dz);

        $entity->x = $newPos->x;
        $entity->y = $newPos->y;
        $entity->z = $newPos->z;
        $radius = $entity->width / 2;
        $entity->boundingBox->setBounds($entity->x - $radius, $entity->y, $entity->z - $radius, $entity->x + $radius, $entity->y + $entity->height, $entity->z + $radius);

        $to = $entity->getLocation();

        $delta = (($entity->lastX - $to->x) ** 2) + (($entity->lastY - $to->y) ** 2) + (($entity->lastZ - $to->z) ** 2);
        $deltaAngle = abs($entity->lastYaw - $to->yaw) + abs($entity->lastPitch - $to->pitch);
        if (($delta > 0.0001 or $deltaAngle > 1.0)) {
            $entity->lastX = $to->x;
            $entity->lastY = $to->y;
            $entity->lastZ = $to->z;
            $entity->lastYaw = $to->yaw;
            $entity->lastPitch = $to->pitch;

            $this->broadcastMovement($entity);

            $entity->newPosition = null;
        }
    }

    private function broadcastMovement(Entity $entity) {
        $pk = new MoveEntityPacket();
        $pk->entityRuntimeId = $entity->getId();
        $pk->position = $entity->getOffsetPosition($entity);
        $pk->yaw = $entity->yaw;
        $pk->pitch = $entity->pitch;
        $pk->headYaw = $entity->yaw; //TODO
        $pk->teleported = false;
        $entity->getLevel()->addChunkPacket($entity->chunk->getX(), $entity->chunk->getZ(), $pk);
    }

}
