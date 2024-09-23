<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifProfilForm;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{

    private $entityManager;
    private ParticipantRepository $participantRepository;

    public function __construct(EntityManagerInterface $entityManager, ParticipantRepository $participantRepository)
    {
        $this->entityManager = $entityManager;
        $this->participantRepository = $participantRepository;
    }

    #[Route('/participant/profil', name: 'app_profil')]
    public function profil(): Response
    {
        return $this->render('profil/affichage_profil.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
/*
    #[Route('/participant/modification', name: 'app_modifprofil')]
    public function modifprofil(): Response
    {
        return $this->render('profil/modification_profil.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
*/
    #[Route('/participant/profil/edit', name: 'profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();

        if (!$participant) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $form = $this->createForm(ModifProfilForm::class, $participant);
        $form->handleRequest($request);

        // Vérifiez si le formulaire est soumis
        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                /*
                $imageFile = $form->get('imageFile')->getData();

                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // Crée un nom unique pour le fichier

                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    // Déplace le fichier dans le dossier où les images sont stockées
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Assurez-vous d'avoir configuré ce paramètre
                        $newFilename

                    );

                    // Mettre à jour l'entité avec le nom du fichier
                    $participant->setImageName($newFilename);
                }*/

                // Récupérer le nouveau mot de passe s'il est fourni
                $plainPassword = $form->get('plainPassword')->getData();

                if ($plainPassword)
                {
                    // Hasher et mettre à jour le mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($participant, $plainPassword);
                    $participant->setMotPasse($hashedPassword);
                }

                // Sauvegarder les autres modifications
              $this->entityManager->persist($participant);
                $this->entityManager->flush();


                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirectToRoute('app_profil'); // Redirection après succès
            }
            else
            {
                // Afficher l'erreur seulement si le formulaire est soumis et invalide
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        // Rendre le formulaire même en cas d'erreur
        return $this->render('profil/modification_profil.html.twig', [
            'modifForm' => $form->createView(),
            'participant' => $participant,
        ]);
    }
}