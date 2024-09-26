<?php
//marie laure
namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SedesisterController extends AbstractController
{
    #[Route('/sedesister/{id}', name: 'app_sedesister')]
    public function sedesister(Sortie $sortie, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        // Vérifier si l'utilisateur est bien inscrit à la sortie
        if ($sortie->getParticipants()->contains($user)) {
            // Retirer l'utilisateur de la sortie
            $sortie->removeParticipant($user);

            // Sauvegarder les modifications en base de données
            $entityManager->persist($sortie);
            $entityManager->flush();

        }

        // Rediriger vers la liste des sorties ou autre page
        return $this->redirectToRoute('app_sorties');
    }
}

