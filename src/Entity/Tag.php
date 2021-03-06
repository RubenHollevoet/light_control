<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ORM\Table(name="app_tag")
 */
class Tag
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Device", mappedBy="tags")
     */
    private $Devices;

    /**
     * @ORM\Column(type="integer", nullable=true, name="sort")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Call", mappedBy="tag")
     */
    private $calls;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cluster;

    public function __construct()
    {
        $this->Devices = new ArrayCollection();
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

    public function __toString()
    {
        return $this->name;
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
            $call->setTag($this);
        }

        return $this;
    }

    public function removeCall(Call $call): self
    {
        if ($this->calls->contains($call)) {
            $this->calls->removeElement($call);
            // set the owning side to null (unless already changed)
            if ($call->getTag() === $this) {
                $call->setTag(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function isCluster()
    {
        return $this->cluster;
    }

    /**
     * @param mixed $cluster
     */
    public function setCluster($cluster): void
    {
        $this->cluster = $cluster;
    }

    public function getDeviceIdString() {

        $deviceIds = [];

        /** @var Device $device */
        foreach ($this->getDevices() as $device) {
            $deviceIds[] = $device->getId();
        }

        return implode(',', $deviceIds);
    }
}
