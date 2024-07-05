<?php

namespace ReplaySystem\Task;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\world\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\animation\{HurtAnimation, DeathAnimation, RespawnAnimation, ConsumingItemAnimation};
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\scheduler\Task;
use ReplaySystem\Manager\Replay;
use ReplaySystem\ReplaySystem;

class PlayReplayTask extends Task {

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
        parent::__construct();
    }

    public function onRun(): void {
        $this->tempStart = $this->tempStart + $this->speed;
        if (isset($this->tempReplayData[$this->tempStart])) {
            foreach ($this->tempReplayData[$this->tempStart] as $sequenz) {
                $edata = $this->tempEntityData[$sequenz["EntityId"]]["Entity"];
                if (!($this->tempEntityData[$sequenz["EntityId"]]["Spawned"])) {
                    if ($edata["NETWORK_ID"] == -1) {
                        $location = new Location($edata["Position"]["X"], $edata["Position"]["Y"], $edata["Position"]["Z"], $this->replay->getWorld(), 0.0, 0.0);
                        $nbt = CompoundTag::create()->setTag("Skin", CompoundTag::create()->setString("Name", $edata["Skin"]["Name"])->setString("Data", $edata["Skin"]["Data"]));
                        $nbt->setTag("ReplayEntity", CompoundTag::create()->setString("true"));

                        $entity = new Human($location, Human::parseSkinNBT($nbt), $nbt);

                        if (is_string($edata["NameTag"]))
                            $entity->setNameTag($edata["NameTag"]);

                        $entity->setNameTagVisible(true);
                        $entity->setNameTagAlwaysVisible(true);
                        $entity->spawnToAll();
                        $this->spawendEntity[$sequenz["EntityId"]] = $entity;
                        $this->tempEntityData[$sequenz["EntityId"]]["Spawned"] = true;

                    }
                }
                if ($sequenz["Action"] === "Move") { //TODO

                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Human) {
                        $vec3 = $sequenz["Data"]["Position"]                    if (!is_null($vec3)) {
                            $entity->newPosition = new Location($vec3["X"], $vec3["Y"], $vec3["Z"], $vec3["Yaw"], $vec3["Pitch"], $this->replay->getWorld());
                            $entity->setRotation($vec3["Yaw"], $vec3["Pitch"]);
                            $this->processMovement($entity);


                            if (!($entity->getInventory()->getItemInHand()->getId() === $sequenz["Item"]["Id"])) {
false                         $entity->getInventory()->setItemInHand(Item::get($sequenz["Item"]["Id"]));
                                $entity->getInventory()->sendHeldItem($entity->getViewers());

                            }
                        }
                    }
                } elseif ($sequenz["Action"] === "Damage") {
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        $entity->broadcastAnimation(new HurtAnimation($entity));
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
                        $pk->actorRuntimeId = $entity->getId();
                        $pk->action = $sequenz["Data"]["Animation"];
                        $entity->getWorld()->broadcastPacketToViewers($entity->getPosition()->asVector3(), $pk);
                    }
                } elseif ($sequenz["Action"] === "Consume") { //TODO
                    $entity = $this->spawendEntity[$sequenz["EntityId"]];
                    if ($entity instanceof Entity) {
                        if (!($entity->getInventory()->getItemInHand()->getId() === $sequenz["Item"]["Id"])) {
                            $entity->getInventory()->setItemInHand(Item::get($sequenz["Item"]["Id"]));
                            $entity->getInventory()->sendHeldItem($entity->getViewers());
                        }

                        $entity->broadcastAnimation(new ConsumingItemAnimation($entity, Item::get($sequenz["Item"]["Id"]));
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
                        $entity->broadcastAnimation(new DeathAnimation($entity));
                        $entity->broadcastAnimation(new RespawnAnimation($entity));
                        $entity->spawnToAll();
                    }
                }
            }
        }
        if ($this->tempStart >= ($this->stop - $this->start)){
            foreach ($this->spawendEntity as $entity) {
                if ($entity instanceof Entity) {
                    $entity->close();
                }
            }
            foreach ($this->replay->getWorld()->getPlayers() as $p) {
                $p->spawnToAll();
                $p->sendMessage(ReplaySystem::PREFIX . " Stopped Replay");
            }
            $this->plugin->getServer()->getLogger()->info(ReplaySystem::PREFIX . " Stopped Replay");
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
            $this->replay->setPlaying(false);
        }
    }

    //TODO
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
        $pk = new MoveActorAbsolutePacket();
        $pk->actorRuntimeId = $entity->getId();
        $pk->position = $entity->getOffsetPosition($entity->getLocation());
        $pk->yaw = $entity->getLocation()->yaw;
        $pk->pitch = $entity->getLocation()->pitch;
        $pk->headYaw = $entity->getLocation()->yaw; //TODO
        $pk->flag = $entity->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0;
        $entity->getWorld()->broadcastPacketToViewers($entity->getPosition()->asVector3(), $pk));
    }
}
