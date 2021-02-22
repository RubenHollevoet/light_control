<?php


namespace App\Services;

use App\Entity\Device;

class DeviceOptionsService
{
    public const ACTION_ON_OFF = 0;
    public const ACTION_WW = 1;
    public const ACTION_SLIDER_BRIGHTNESS = 2;
    public const ACTION_FLASH = 3;
    public const ACTION_OPEN_IN_BROWSER = 4;

    public function getMenuAction(string $deviceType) {

        $actions = [
            Device::TYPE_YEELIGHT_BULB_COLOR_2 => [
                self::ACTION_ON_OFF,
                self::ACTION_WW,
                self::ACTION_SLIDER_BRIGHTNESS,
                self::ACTION_FLASH,
            ],
            Device::TYPE_YEELIGHT_BULB_COLOR_S1 => [
                self::ACTION_ON_OFF,
                self::ACTION_WW,
                self::ACTION_SLIDER_BRIGHTNESS,
                self::ACTION_FLASH,
            ],
            Device::TYPE_YEELIGHT_STRIP_COLOR => [
                self::ACTION_ON_OFF,
                self::ACTION_WW,
                self::ACTION_SLIDER_BRIGHTNESS,
                self::ACTION_FLASH,
            ],
            Device::TYPE_YEELIGHT_FULFILLMENT => [
                self::ACTION_ON_OFF,
                self::ACTION_SLIDER_BRIGHTNESS,
                self::ACTION_FLASH,
            ],
            Device::TYPE_WLED => [
                self::ACTION_ON_OFF,
                self::ACTION_OPEN_IN_BROWSER,
            ],
            Device::TYPE_ARDUINO_TODO => [
            ],
        ];

        return $actions[$deviceType] ?? [];
    }
}
