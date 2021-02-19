<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 * @ORM\Table(name="app_device")
 *
 */
class Device
{
    public const BRAND_YEELIGHT = 'Yeelight';
    public const BRAND_ARDUINO = 'Arduino';

    public const TYPE_YEELIGHT_BULB_COLOR_2 = 'Yee bulb V2 color';
    public const TYPE_YEELIGHT_BULB_COLOR_S1 = 'Yee bulb S1 color';
    public const TYPE_YEELIGHT_STRIP_COLOR = 'Yee Led strip';
    public const TYPE_YEELIGHT_FULFILLMENT = 'Yee Fulfillment bulb';
    public const TYPE_WLED = 'WLED strip';
    public const TYPE_ARDUINO_TODO = 'Arduino TODO';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $deviceId;

    /**
     * @ORM\Column(type="string")
     */
    private $brand;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @ORM\Column(type="datetime", length=255)
     */
    private $lastScan;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="Devices")
     * @ORM\JoinTable(name="app_tag_device")
     * @ORM\OrderBy({"sort" = "DESC"})
     */
    private $tags;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Call", mappedBy="device")
     */
    private $calls;

    public function __construct()
    {
        $this->scenes = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->calls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @param mixed $deviceId
     */
    public function setDeviceId($deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param mixed $brand
     */
    public function setBrand($brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getIP(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastScan()
    {
        return $this->lastScan;
    }

    /**
     * @param mixed $lastScan
     */
    public function setLastScan($lastScan): void
    {
        $this->lastScan = $lastScan;
    }

    /**
     * @return Collection|Scene[]
     */
    public function getScenes(): Collection
    {
        return $this->scenes;
    }

    public function addScene(Scene $scene): self
    {
        if (!$this->scenes->contains($scene)) {
            $this->scenes[] = $scene;
            $scene->addDevice($this);
        }

        return $this;
    }

    public function removeScene(Scene $scene): self
    {
        if ($this->scenes->contains($scene)) {
            $this->scenes->removeElement($scene);
            $scene->removeDevice($this);
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags($ignoreClusters = false): array
    {
        if($ignoreClusters) {

            return array_filter($this->tags->toArray(), static function (Tag $tag) {
                return !$tag->isCluster();
            });
        }

        return $this->tags->toArray();
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addDevice($this);
        }

        return $this;
    }

    public function getClusterTag(): ?Tag
    {
        foreach ($this->getTags() as $tag)
        {
            if($tag->isCluster()) {
                return $tag;
            }
        }

        return null;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeDevife($this);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     */
    public function setSort($sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return Collection|Call[]
     */
    public function getCalls(): Collection
    {
        return $this->calls;
    }

    public function addCall(Call $call): self
    {
        if (!$this->calls->contains($call)) {
            $this->calls[] = $call;
            $call->setDevice($this);
        }

        return $this;
    }

    public function removeCall(Call $call): self
    {
        if ($this->calls->contains($call)) {
            $this->calls->removeElement($call);
            // set the owning side to null (unless already changed)
            if ($call->getDevice() === $this) {
                $call->setDevice(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
