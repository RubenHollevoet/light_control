<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SceneRepository")
 * @ORM\Table(name="app_scene")
 */
class Scene
{
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Device", inversedBy="scenes")
     * @ORM\JoinTable(name="app_scene_device")
     */
    private $Devices;

    public function __construct()
    {
        $this->Devices = new ArrayCollection();
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
     * @return Collection|Device[]
     */
    public function getDevices(): Collection
    {
        return $this->Devices;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->Devices->contains($device)) {
            $this->Devices[] = $device;
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->Devices->contains($device)) {
            $this->Devices->removeElement($device);
        }

        return $this;
    }
}