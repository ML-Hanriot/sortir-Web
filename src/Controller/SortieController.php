<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'app_')]
class SortieController extends AbstractController
{
     #[Route('/', name: 'sorties')]
    public function sorties(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        // Logique pour récupérer les sorties
        return $this->render('sortie/sorties.html.twig',["sorties"=>$sorties]);
    }
    #[Route('/creer', name: 'creer', methods: ['POST', 'GET'])]
    public function creer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On peut ajouter des actions avant la sauvegarde si nécessaire (ex: assigner l'organisateur)
            $sortie->setOrganisateur($this->getUser()); // Assuming current user is an organizer

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès');

            return $this->render('sortie/creer.html.twig');
        }

        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/modifier', name: 'modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Sortie mise à jour avec succès');

            return $this->redirectToRoute('app_sorties');
        }

        return $this->render('sortie/modifier.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer', name: 'supprimer', methods: ['POST'])]
    public function supprimer(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {

        if ($this->isCsrfTokenValid('supprimer'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie supprimée avec succès');
        }

        return $this->redirectToRoute('app_sorties');
    }
}
