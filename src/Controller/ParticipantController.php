<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantController extends AbstractController
{
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
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Participant $participant */
        $participant = $this->getUser();

        // Créer le formulaire pré-rempli avec les données de l'utilisateur
        $form = $this->createForm(RegistrationFormType::class, $participant);

        // Gérer la requête du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            // Rediriger après la soumission
            return $this->redirectToRoute('app_profil');
        }

        // Afficher le formulaire pré-rempli
        return $this->render('profil/modification_profil.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
