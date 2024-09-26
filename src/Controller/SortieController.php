<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\EtatRepository;
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

            // Récupérer les sorties avec les filtres
        $sorties = $sortieRepository->findByFilters($filters);

        $campus = $campusRepository->findAll();

        return $this->render('sortie/sorties.html.twig', [
            'sorties' => $sorties,
            'campus' => $campus,
            'filters' => $filters,
        ]);
    }

    #[Route('/creer', name: 'creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository, CampusRepository $campusRepository, LieuRepository $lieuRepository): Response
    {
        $user = $this->getUser();

        $sortie = new Sortie();
        $sortie->setCampus($user->getCampus());
        $sortie->setOrganisateur($user);
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if($form->get('Enregistrer')->isClicked()) {
            $etat = $etatRepository->findOneBy(['libelle' => Etat::CREER]); // État "Créée"
            }
            else{
                $etat = $etatRepository->findOneBy(['libelle' => Etat::OUVERT]); // État "Créée"
            }

            $sortie->setEtat($etat);  // Associer l'état à la sortie

            // Persister la sortie
            $entityManager->persist($sortie);
            $entityManager->flush();

            $message=$form->get('Enregistrer')->isClicked() ? 'Enregistrée':'Publiée';
            $this->addFlash('success', 'Sortie ' . $message . ' avec succès');

            // Rediriger vers la liste des sorties après un succès
            return $this->redirectToRoute('app_sorties');
        }

        return $this->render('sortie/creer.html.twig', [
            'form' => $form
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

    // Route pour récupérer les lieux par ville
    #[Route('/api/lieu/{villeId}', name: 'api_lieux_par_ville', methods: ['GET'])]
    public function getLieuxParVille(LieuRepository $lieuRepository, int $villeId): JsonResponse
    {
        $lieux = $lieuRepository->findBy(['ville' => $villeId]);

        $data = [];
        foreach ($lieux as $lieu) {
            $data[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
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
