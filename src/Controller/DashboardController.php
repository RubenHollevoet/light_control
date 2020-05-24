<?php

namespace App\Controller;

use App\Entity\Call;
use App\Entity\Device;
use App\Entity\Scene;
use App\Entity\Tag;
use App\Form\CallsType;
use App\Form\DevicesType;
use App\Form\ScenesType;
use App\Form\TagsType;
use App\Services\YeelightService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $devices = $em->getRepository(Device::class)->findOrdered();
        $tags =  $em->getRepository(Tag::class)->findOrdered();
        $scenes =  $em->getRepository(Scene::class)->findAll();

        $sceneLabels = $em->createQuery('SELECT s.label FROM App\Entity\Scene s GROUP BY s.label')->getResult();
        $sceneLabels = array_column($sceneLabels, 'label');
        $sceneLabels = array_filter($sceneLabels);

        return $this->render('dashboard/dashboard.html.twig', [
            'sceneLabels' => $sceneLabels,
            'scenes' => $scenes,
            'devices' => $devices,
            'tags' => $tags,
        ]);
    }

    public function devices(Request $request) {
        if($request->get('action') === 'scan_yeelights') {
            $this->yeelightService->scanForYeelights();
            return $this->redirectToRoute('app.devices', []);
        }

        $em = $this->getDoctrine()->getManager();

        $devices = $em->getRepository(Device::class)->findOrdered();
        $form = $this->createForm(DevicesType::class, ['devices' => $devices]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $formDevices = $formData['devices'];

            //persist all new tags
            foreach ($formDevices as $formDevice) {
                $em->persist($formDevice);
            }

            //update $devices
            $devices = $em->getRepository(Device::class)->findOrdered();

            //remove tags that aren't pushed anymore
            foreach ($devices as $device) {
                if(!in_array($device, $formDevices, true)) {
                    $em->remove($device);
                }
            }

            $em->flush();
        }

        return $this->render('dashboard/devices.html.twig', [
            'form' => $form->createView(),
            'devices' => [],
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
            'tags' => [],
        ]);
    }

    public function scenes(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $scenes = $em->getRepository(Scene::class)->findAll();

        $form = $this->createForm(ScenesType::class, ['scenes' => $scenes]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $formScenes = $formData['scenes'];

            //persist all new scenes
            foreach ($formScenes as $scene) {
                $em->persist($scene);
            }

            //update $scenes
            $scenes = $em->getRepository(Scene::class)->findAll();

            //remove $scenes that aren't pushed anymore
            foreach ($scenes as $scene) {
                if(!in_array($scene, $formScenes)) {
                    $em->remove($scene);
                }
            }
            $em->flush();
        }

        $sceneLabels = $em->createQuery('SELECT s.label FROM App\Entity\Scene s GROUP BY s.label')->getResult();
        $sceneLabels = array_column($sceneLabels, 'label');
        $sceneLabels = array_filter($sceneLabels);

        return $this->render('dashboard/scenes.html.twig', [
            'sceneLabels' => $sceneLabels,
            'ip' => $this->getHost(),
            'form' => $form->createView(),
//            'scenes' => [],
        ]);
    }

    public function calls(Request $request, $scene) {
        $em = $this->getDoctrine()->getManager();
        /** @var Scene $scene */
        $scene = $em->getRepository(Scene::class)->find($scene);
        $sceneCalls = $scene->getCalls();

        $form = $this->createForm(CallsType::class, ['calls' => $scene->getCalls()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $formCalls = [];
            foreach ($form->get('calls') as $callData)
            {
                $deviceTag = $callData->get('deviceTag')->getData();
                $api = $callData->get('api')->getData();                //ignored for now since can only be Yeelight

                /** @var Call $call */
                $call = $callData->getData();

                if($deviceTag[0] === 't') {
                    /** @var Tag $tag */
                    if(null === $tag = $em->getRepository(Tag::class)->find(substr($deviceTag, 1))) {
                        throw new NotFoundHttpException('Unknown tag for id '.substr($deviceTag, 1));
                    }

                    $call->setDevice(null);
                    $call->setTag($tag);
                }
                else {
                    /** @var Device $device */
                    if(null === $device = $em->getRepository(Device::class)->find($deviceTag)) {
                        throw new NotFoundHttpException('Unknown tag for id '.$deviceTag);
                    }

                    $call->setDevice($device);
                    $call->setTag(null);
                }


                $formCalls[] = $call;

                $call->setScene($scene);
                $em->persist($call);
            }

            //remove $sceneCalls that aren't pushed anymore
            foreach ($sceneCalls as $call) {
                if(!in_array($call, $formCalls)) {
                    $em->remove($call);
                }
            }
            $em->flush();
        }

        return $this->render('dashboard/calls.html.twig', [
            'scene' => $scene,
            'form' => $form->createView(),
        ]);
    }

    public function api(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository(Tag::class)->findAll();
        $devices = $em->getRepository(Device::class)->findOrdered();

        return $this->render('dashboard/api.html.twig', [
            'ip' => $this->getHost(),
            'tags' => $tags,
            'devices' => $devices,
            'scenes' => [],
        ]);
    }

    private function getHost() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if($_SERVER['SERVER_PORT'] !== 80 && $_SERVER['SERVER_PORT'] !== 443) {
            $ip .= ':'. $_SERVER['SERVER_PORT'];
        }

        return $ip;
    }
}
