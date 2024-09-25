<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class SinscrireController extends AbstractController
{
    #[Route('/sinscrire/{id}', name: 'app_sinscrire')]
    public function inscrire(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Vérification que l'utilisateur est bien un participant
        if (!$user instanceof Participant) {
            $this->addFlash('error', 'Vous devez être un participant pour vous inscrire.');
            return $this->redirectToRoute('app_sorties');
        }

        // Recharger l'entité sortie pour s'assurer qu'elle est à jour
        $entityManager->refresh($sortie);

        // Vérifier si l'utilisateur est déjà inscrit à cette sortie
        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à cette sortie.');
        } else {
            // Ajout de l'utilisateur à la sortie
            $sortie->addParticipant($user);

            // Enregistrement des modifications dans la base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Vous êtes inscrit à la sortie !');
        }

        // Redirection vers la page des sorties
        return $this->redirectToRoute('app_sorties', ['id' => $sortie->getId()]);
    }
}
