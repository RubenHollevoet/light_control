<?php

namespace App\Controller\Api;

use App\Entity\Call;
use App\Entity\Device;
use App\Entity\Scene;
use App\Services\YeelightService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SceneController extends AbstractController
{
    private $yeelightService;

    public function __construct(YeelightService $yeelightService)
    {
        $this->yeelightService = $yeelightService;
    }

    public function trigger(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Scene $scene */
        if(null === $scene = $em->getRepository(Scene::class)->findOneBy(['slug' => $slug])) {
            throw new NotFoundHttpException('Unknown scene for id '.substr($slug, 1));
        }

        $calls = $scene->getCalls();

        $result = [];

        /** @var Call $call */
        foreach ($calls as $call) {

            $method = explode('/', $call->getContent())[0];
            $param = explode('/', $call->getContent())[1];
            $param = explode(',', $param);

            if($call->getDevice()) {
                $result[] = $this->yeelightService->execute($call->getDevice(), $method, $param, 0);
            }
            else {
                /** @var Device $device */
                foreach ($call->getTag()->getDevices() as $device) {
                    $result[] = $this->yeelightService->execute($device, $method, $param, 0);
                }
            }
        }

        return new JsonResponse(['status' => 'ok', 'data' => $result]);
    }
}
