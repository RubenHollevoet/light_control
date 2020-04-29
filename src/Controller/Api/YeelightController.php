<?php

namespace App\Controller\Api;

use App\Entity\Device;
use App\Entity\Tag;
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

    public function index() {

        return $this->render('api/index.html.twig', []);
    }

    public function execute(string $target, string $method, string $params, int $responseId) {
        $em = $this->getDoctrine()->getManager();
//
//        $maxParamCount = 2;
//        switch ($method) {
//            case 'set_ct_abx':
//            case 'set_rgb':
//            case 'set_bright':
//            case 'set_power':
//            case 'start_cf':
//            case 'set_music':
//            case 'bg_set_rgb':
//            case 'bg_set_ct_abx':
//            case 'bg_start_cf':
//            case 'bg_set_power':
//            case 'bg_set_bright':
//                $maxParamCount = 3;
//                break;
//            case 'set_hsv':
//            case 'set_scene':
//            case 'bg_set_hsv':
//            case 'bg_set_scene':
//                $maxParamCount = 4;
//                break;
//            case 'get_prop':
//                $maxParamCount = PHP_INT_MAX;
//                break;
//        }
//
////        $params = $params === '0' ? [] : explode(',', $params, $maxParamCount);
        $params = $params === '0' ? [] : explode(',', $params);
        $params = str_replace('-', ',', $params);
        $params = str_replace('_', ',', $params);

        if(strpos($target, 't') === 0) {
            $yeeResp = [];
            /** @var Tag $tag */
            if(null === $tag = $em->getRepository(Tag::class)->find(substr($target, 1))) {
                throw new NotFoundHttpException('Unknown tag for id '.substr($target, 1));
            }
            foreach ($tag->getDevices() as $device) {
                if($device->getBrand() === Device::BRAND_YEELIGHT) {
                    $yeeResp[] = $this->yeelightService->execute($device, $method, $params, $responseId);
                }
            }
        }
        else {
            /** @var Device $device */
            if(null === $device = $em->getRepository(Device::class)->findOneBy(['id' => $target, 'brand' => Device::BRAND_YEELIGHT])) {
                throw new NotFoundHttpException('No Yeelight registered with this ID');
            }
            $yeeResp = $this->yeelightService->execute($device, $method, $params, $responseId);
        }

        return new JsonResponse($yeeResp);
    }
}
