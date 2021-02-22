<?php

namespace App\Twig;

use App\Services\DeviceOptionsService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DeviceOptionsExtentionExtension extends AbstractExtension
{
    /** @var DeviceOptionsService */
    private $deviceOptionsService;

    public function __construct(DeviceOptionsService $deviceOptionsService)
    {
        $this->deviceOptionsService = $deviceOptionsService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_device_options', [$this, 'getDeviceOptions']),
            new TwigFunction('has_device_option', [$this, 'hasDeviceOption']),
        ];
    }

    public function getDeviceOptions(string $deviceType) : array
    {
        return $this->deviceOptionsService->getMenuAction($deviceType);
    }

    public function hasDeviceOption(?string $deviceType, string $option) : bool
    {
        if($deviceType) {
            return in_array((int) $option, $this->getDeviceOptions($deviceType),true);
        }

       return false;
    }
}
