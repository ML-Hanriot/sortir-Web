<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/profil', name: 'app_profil')]
    public function profil(): Response
    {
        return $this->render('profil/affichage_profil.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
    #[Route('/participant/modification', name: 'app_modifprofil')]
    public function modifprofil(): Response
    {
        return $this->render('profil/modification_profil.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
}
