<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sorties', name: 'app_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'sorties')]
    public function sorties(SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $sorties = $sortieRepository->findAll();
        $campus = $campusRepository->findAll(); // Récupérer tous les campus

        return $this->render('sortie/sorties.html.twig', [
            'sorties' => $sorties,
            'campus' => $campus, // Passer la liste des campus à la vue
        ]);
    }

    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Assigner des valeurs supplémentaires comme l'organisateur si nécessaire
            $sortie->setOrganisateur($this->getUser());


            // Enregistrer la sortie dans la base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

            // Message de succès et redirection
            $this->addFlash('success', 'Sortie créée avec succès');
            return $this->redirectToRoute('app_sorties'); // Rediriger vers la liste des sorties
        }

        // Récupération des villes pour le formulaire
        $villes = $villeRepository->findAll();

        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes // Passer la liste des villes à la vue
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
