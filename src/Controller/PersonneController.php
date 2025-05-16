<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PersonneController extends AbstractController
{
    #[Route('/personne', name: 'app_personne')]
    public function index(): Response
    {
        return $this->render('personne/index.html.twig', [
            'controller_name' => 'PersonneController',
            'nom' => 'Jeremie',
        ]);
    }


    #[Route('/test', name: 'app_test')]
    public function test(): Response
    {
        return $this->render('personne/test.html.twig');
    }
}
