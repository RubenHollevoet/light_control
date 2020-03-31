<?php

namespace App\Controller\Api;

use App\Entity\Device;
use App\Services\YeelightService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YeelightController extends AbstractController
{
    /** @var YeelightService $yeelightService */
    private $yeelightService;

    /**
     * YeelightController constructor.
     */
    public function __construct(YeelightService $yeelightService)
    {
        $this->yeelightService = $yeelightService;
    }

    public function execute(int $id, string $method, string $params, int $responseId) {
        $em = $this->getDoctrine()->getManager();

        /** @var Device $device */
        if(null === $device = $em->getRepository(Device::class)->findOneBy(['id' => $id, 'type' => Device::TYPE_YEELIGHT])) {
            throw new NotFoundHttpException('No Yeelight registered with this ID');
        }

        $maxParamCount = 2;
        switch ($method) {
            case 'set_ct_abx':
            case 'set_rgb':
            case 'set_bright':
            case 'set_power':
            case 'start_cf':
            case 'set_music':
            case 'bg_set_rgb':
            case 'bg_set_ct_abx':
            case 'bg_start_cf':
            case 'bg_set_power':
            case 'bg_set_bright':
                $maxParamCount = 3;
                break;
            case 'set_hsv':
            case 'set_scene':
            case 'bg_set_hsv':
            case 'bg_set_scene':
                $maxParamCount = 4;
                break;
            case 'get_prop':
                $maxParamCount = PHP_INT_MAX;
                break;
        }

        $yeeResp = $this->yeelightService->execute($device, $method, explode(',', $params, $maxParamCount), $responseId);

        return new JsonResponse($yeeResp);
    }
}
