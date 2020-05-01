<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Scene;
use App\Entity\Tag;
use App\Form\DashboardType;
use App\Form\DevicesType;
use App\Form\TagsType;
use App\Services\FunService;
use App\Services\YeelightService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /** @var YeelightService $yeelightService */
    private $yeelightService;

    /**
     * DashboardController constructor.
     */
    public function __construct(YeelightService $yeelightService)
    {
        $this->yeelightService = $yeelightService;
    }

    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $devices = $em->getRepository(Device::class)->findOrdered();
        $scenes =  $em->getRepository(Scene::class)->findAll();

        return $this->render('dashboard/dashboard.html.twig', [
            'scenes' => $scenes,
            'devices' => $devices
        ]);
    }
}
