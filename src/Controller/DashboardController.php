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
        $em = $this->getDoctrine()->getManager();

        $devices = $em->getRepository(Device::class)->findAll();
        $scenes =  $em->getRepository(Scene::class)->findAll();

        return $this->render('dashboard/dashboard.html.twig', [
            'scenes' => $scenes,
            'devices' => $devices
        ]);
    }

    public function devices(Request $request) {
        if($request->get('action') === 'scan_yeelights') {
            $this->yeelightService->scanForYeelights();
            return $this->redirectToRoute('app.devices', []);
        }

        $em = $this->getDoctrine()->getManager();

        $devices = $em->getRepository(Device::class)->findAll();
        $form = $this->createForm(DevicesType::class, ['devices' => $devices]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $formDevices = $formData['devices'];

            //persist all new tags
            foreach ($formDevices as $formDevice) {
                $em->persist($formDevice);
            }

            //update $tags
            $devices = $em->getRepository(Tag::class)->findAll();

            //remove tags that aren't pushed anymore
//            foreach ($tags as $tag) {
//                if(!in_array($tag, $newTags)) {
//                    $em->remove($tag);
//                }
//            }

            $em->flush();
        }

        return $this->render('dashboard/devices.html.twig', [
            'form' => $form->createView(),
            'devices' => []
        ]);
    }

    public function tags(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository(Tag::class)->findAll();
        $form = $this->createForm(TagsType::class, ['tags' => $tags]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $newTags = $formData['tags'];

            //persist all new tags
            foreach ($newTags as $tag) {
                $em->persist($tag);
            }

            //update $tags
            $tags = $em->getRepository(Tag::class)->findAll();

            //remove tags that aren't pushed anymore
            foreach ($tags as $tag) {
                if(!in_array($tag, $newTags)) {
                    $em->remove($tag);
                }
            }

            $em->flush();
        }

        return $this->render('dashboard/tags.html.twig', [
            'form' => $form->createView(),
            'tags' => []
        ]);
    }

    public function scenes(Request $request) {
        return $this->render('dashboard/scenes.html.twig', [
            'form' => '',
            'scenes' => []
        ]);
    }

    public function api(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository(Tag::class)->findAll();
        $devices = $em->getRepository(Device::class)->findAll();

        $ip = $_SERVER['REMOTE_ADDR'];
        if($_SERVER['SERVER_PORT'] !== 80 && $_SERVER['SERVER_PORT'] !== 443) {
            $ip .= ':'. $_SERVER['SERVER_PORT'];
        }

        return $this->render('dashboard/api.html.twig', [
            'ip' => $ip,
            'tags' => $tags,
            'devices' => $devices,
            'scenes' => []
        ]);
    }
}
