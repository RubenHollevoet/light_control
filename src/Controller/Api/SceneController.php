<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SceneController extends AbstractController
{
    public function trigger()
    {
        return $this->render('scene/index.html.twig', [
            'controller_name' => 'SceneController',
        ]);
    }
}
