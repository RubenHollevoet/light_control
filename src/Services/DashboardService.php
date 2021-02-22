<?php


namespace App\Services;

use App\Entity\Device;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    /** @var EntityManager $em */
    private $em;

    /**
     * DashboardService constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAllFixtures()
    {
        $fixtures = [];

        $devices = $this->em->getRepository(Device::class)->findOrdered();

        foreach ($devices as $device) {
            /** @var Device $device */

            $tags = [];
            foreach ($device->getTags(true) as $tag) {
                $tags[] = $tag->getName();
            }

            if($tag = $device->getClusterTag()) {
                $fixtures['t'.$tag->getId()] = [
                    'name' => $tag->getName(),
                    'type' => $device->getType(),
                    'isCluster' => true,
                    'tags' => $tags,
                ];
            }
            else {
                $fixtures[$device->getId()] = [
                    'name' => $device->getName(),
                    'type' => $device->getType(),
                    'ip' => $device->getIP(),
                    'isCluster' => false,
                    'tags' => $tags
                ];
            }
        }

        return $fixtures;
    }
}
