<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/ville', name: 'app_villes')]
    public function ville(): Response
    {
        return $this->render('admin/ville.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/admin/campus', name: 'app_campus')]
    public function campus(): Response
    {
        return $this->render('admin/campus.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
