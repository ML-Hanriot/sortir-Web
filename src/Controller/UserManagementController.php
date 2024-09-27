<?php

namespace App\Controller;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserManagementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/users', name: 'user_list')]
    public function listUsers(): Response
    {
        // Récupérer tous les participants
        $participants = $this->entityManager->getRepository(Participant::class)->findAll();

        return $this->render('participant/list.html.twig', [
            'participants' => $participants,
        ]);
    }

    #[Route('/users/delete/{id}', name: 'user_delete')]
    public function deleteUser(Participant $participant): Response
    {
        $this->entityManager->remove($participant);
        $this->entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('user_list');
    }

    #[Route('/users/toggle-active/{id}', name: 'user_toggle_active')]
    public function toggleActive(Participant $participant): Response
    {
        // Bascule l'état actif/inactif de l'utilisateur
        $participant->setActif(!$participant->isActif());
        $this->entityManager->flush();

        $this->addFlash('success', 'État de l\'utilisateur mis à jour avec succès.');
        return $this->redirectToRoute('user_list');
    }

    #[Route('/users/toggle-admin/{id}', name: 'user_toggle_admin')]
    public function toggleAdmin(Participant $participant): Response
    {
        // Bascule le rôle administrateur
        $participant->setAdministrateur(!$participant->isAdministrateur());
        $this->entityManager->flush();

        $this->addFlash('success', 'Rôle administrateur de l\'utilisateur mis à jour avec succès.');
        return $this->redirectToRoute('user_list');
    }
}
