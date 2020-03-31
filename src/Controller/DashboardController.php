<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Scene;
use App\Form\DashboardType;
use App\Services\FunService;
use App\Services\YeelightService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
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
        if($request->get('action') === 'scan_yeelights') {
            $this->yeelightService->scanForYeelights();
            $this->redirectToRoute('app.dashboard', []);
        }

        $em = $this->getDoctrine()->getManager();

        $devices = $em->getRepository(Device::class)->findAll();
        $scenes =  $em->getRepository(Scene::class)->findAll();

        $form = $this->createForm(DashboardType::class, ['devices' => $devices]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $devices = $formData['devices'];

            $em = $this->getDoctrine()->getManager();

            foreach ($devices as $device) {
                $em->persist($device);
            }

            $em->flush();
        }

        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
            'scenes' => $scenes,
            'devices' => $devices
        ]);
    }
}
