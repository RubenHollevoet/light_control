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

        return $this->yeelightService->processEvent($target, $method, $params, $responseId);
    }
}
