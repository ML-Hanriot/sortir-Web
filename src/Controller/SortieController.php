<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            // On peut ajouter des actions avant la sauvegarde si nécessaire (ex: assigner l'organisateur)
            $sortie->setOrganisateur($this->getUser()); // Assuming current user is an organizer


            // Enregistrer la sortie dans la base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès');
            return $this->redirectToRoute('app_sorties'); // Rediriger vers la liste des sorties
        }

        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/voir/{id}', name: 'voir', methods: ['GET'])]
    public function voir(Sortie $sortie): Response
    {
        return $this->render('sortie/voir.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier', methods: ['GET', 'POST'])]
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

    #[Route('/sortie/{id}/supprimer', name: 'supprimer', methods: ['GET', 'POST'])]
    public function supprimer(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('supprimer' . $sortie->getId(), $request->request->get('_token'))) {
                $entityManager->remove($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Sortie supprimée avec succès');
                return $this->redirectToRoute('app_sorties');
            }
        }

        return $this->render('sortie/annulation.html.twig', [
            'sortie' => $sortie,
        ]);
    }
    //Julie
    // AJOUT DE ROUTES POUR LES REQUÊTES AJAX
    #[Route('/api/villes', name: 'api_villes', methods: ['GET'])]
    public function getVilles(VilleRepository $villeRepository): JsonResponse
    {
        $villes = $villeRepository->findAll();
        $data = [];

        foreach ($villes as $ville) {
            $data[] = [
                'id' => $ville->getId(),
                'nom' => $ville->getNom(),
                'codePostal' => $ville->getCodePostal()
            ];
        }

        return new JsonResponse($data);
    }


    #[Route('/api/lieu/{villeId}', name: 'api_lieux_par_ville', methods: ['GET'])]
    public function getLieuxParVille(LieuRepository $lieuRepository, int $villeId): JsonResponse
    {
        $lieux = $lieuRepository->findBy(['ville' => $villeId]);
        $data = [];

        foreach ($lieux as $lieu) {
            $data[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
            ];
        }

        return new JsonResponse($data);
    }
//    détail d'un lieu spécfique
    #[Route('/api/lieu/{id}', name: 'api_lieu_details', methods: ['GET'])]
    public function getLieuDetails(LieuRepository $lieuRepository, int $id): JsonResponse
    {
        $lieu = $lieuRepository->find($id);

        if (!$lieu) {
            return new JsonResponse(['message' => 'Lieu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $lieu->getId(),
            'nom' => $lieu->getNom(),
            'rue' => $lieu->getRue(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
        ];

        return new JsonResponse($data);
    }

}
