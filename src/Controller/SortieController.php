<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Collections\ArrayCollection;
#[Route('/sorties', name: 'app_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'sorties')]
    public function sorties(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le campus de l'utilisateur connecté, s'il en a un
        $userCampus = $user ? $user->getCampus() : null;

        // Récupérer le campus sélectionné depuis les filtres
        $selectedCampus = $request->query->get('campus', $userCampus ? $userCampus->getId() : null);

        $filters = [
            'campus' => $selectedCampus,
            'nom' => $request->query->get('nom'),
            'date_debut' => $request->query->get('date_debut'),
            'date_fin' => $request->query->get('date_fin'),
            'organisateur' => $request->query->get('organisateur') ? $user : null,
            'inscrit' => $request->query->get('inscrit') ? $user : null,
            'pasinscrit' => $request->query->get('pasinscrit') ? $user : null,
            'passer' => $request->query->get('passer') ? true : null,
        ];

        // Récupérer toutes les sorties avec les filtres
        //$sorties = $sortieRepository->findByFilters($filters);
        // Vérifier si le campus est tous les campus et qu'aucun autre filtre n'est activé
        if (empty($filters['campus']) && empty($filters['nom']) && empty($filters['date_debut']) && empty($filters['date_fin']) &&
            empty($filters['organisateur']) && empty($filters['inscrit']) && empty($filters['pasinscrit']) &&
            empty($filters['passer'])) {

            // Récupérer toutes les sorties
            $sorties = $sortieRepository->findAll();
        } else {
            // Récupérer les sorties avec les filtres
            $sorties = $sortieRepository->findByFilters($filters);
        }
        $campus = $campusRepository->findAll();

        $filteredSorties = []; // Tableau pour les sorties filtrées

        // Vérifier l'inscription de l'utilisateur pour chaque sortie
        foreach ($sorties as $sortie) {
            $isInscrit = in_array($user, $sortie->getParticipants()->toArray(), true);
            $sortie->isInscrit = $isInscrit;

            // Vérifier si la sortie est ouverte et que la date limite d'inscription n'est pas dépassée
            $currentDate = new \DateTime(); // Date actuelle

            // Filtrage en fonction des états des sorties
            if ($filters['inscrit'] && $isInscrit) {
                $filteredSorties[] = $sortie; // Ajouter à la liste filtrée si l'utilisateur est inscrit
            } elseif (!$filters['inscrit'] && $sortie->getEtat()->getLibelle() === Etat::OUVERT && $sortie->getDateLimiteInscription() >= $currentDate) {
                $filteredSorties[] = $sortie; // Ajouter à la liste filtrée si la sortie est ouverte
            } elseif ($filters['passer'] && $sortie->getEtat()->getLibelle() === Etat::PASSER) {
                $filteredSorties[] = $sortie; // Ajouter les sorties passées
            }
        }

        return $this->render('sortie/sorties.html.twig', [
            'sorties' => $filteredSorties,
            'campus' => $campus,
            'filters' => $filters,
        ]);
    }


    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository,CampusRepository $campusRepository): Response
    {
        $user = $this->getUser();
        $campus = $user ? $user?->getCampus() : null;

        if (!$campus)
        {
            $this->addFlash('error', 'Vous n\'êtes associé à aucun campus.');
            return $this->redirectToRoute('app_sorties');
        }

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setOrganisateur($user);
            $sortie->setCampus($campus); // Associer le campus à la sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès');
            return $this->redirectToRoute('app_sorties');
        }

        return $this->render('sortie/creer.html.twig', [
            'form' => $form->createView(),
            'campus' => $campus, // Le campus de l'utilisateur connecté
            'allCampus' => $campusRepository->findAll(), // Tous les campus pour usage éventuel
        ]);
    }

    #[Route('/voir/{id}', name: 'voir', methods: ['GET'])]
    public function afficher(int $id, SortieRepository $sortieRepository): Response
    {
        // Récupérer la sortie par son ID
        $sortie = $sortieRepository->find($id);

        // Vérifier si la sortie existe
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas.');
        }

        // Récupérer les participants inscrits via la méthode de l'entité Sortie
        $inscrits = $sortie->getParticipants();

        return $this->render('sortie/voir.html.twig', [
            'sortie' => $sortie,
            'inscrits' => $inscrits,
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

    #[Route('/sortie/{id}/annuler', name: 'annuler', methods: ['GET', 'POST'])]
    public function annuler(Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        // Récupérer la sortie par son ID
        $sortie = $sortieRepository->find($id);

        // Vérifier si la sortie existe
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie n\'existe pas.');
        }

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
