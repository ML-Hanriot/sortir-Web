<?php
namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
private $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
$this->entityManager = $entityManager;
}

#[Route('/villes', name: 'villes_index')]
public function index(Request $request): Response
{
    $repository = $this->entityManager->getRepository(Ville::class);
    $search = $request->query->get('search');

    // Récupération de la liste des campus
    $campusRepository = $this->entityManager->getRepository(Campus::class);
    $campusList = $campusRepository->findAll();

    // Si une recherche est effectuée
    if ($search) {
        $villes = $repository->findBy(['nom' => $search]);
    } else {
        $villes = $repository->findAll(); // Sinon, récupère toutes les villes
    }

    return $this->render('admin/ville.html.twig', [
        'villes' => $villes,
        'campus' => $campusList, // Assure-toi que cette ligne est bien présente
    ]);
}

#[Route('/villes/new', name: 'villes_new')]
public function new(Request $request): Response
{
    $ville = new Ville();
    $form = $this->createForm(VilleType::class, $ville);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->persist($ville);
        $this->entityManager->flush();

        return $this->redirectToRoute('villes_index');
    }

    // Récupère toutes les villes pour les afficher dans le template
    $repository = $this->entityManager->getRepository(Ville::class);
    $villes = $repository->findAll();

    return $this->render('ville/addville.html.twig', [
        'form' => $form->createView(),
        'villes' => $villes, // Ajout de la variable 'villes'
    ]);
}

#[Route('/villes/edit/{id}', name: 'villes_edit')]
public function edit(Request $request, Ville $ville): Response
{
    $form = $this->createForm(VilleType::class, $ville);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->flush();

        return $this->redirectToRoute('villes_index');
    }

    // Récupère toutes les villes pour les afficher dans le template
    $repository = $this->entityManager->getRepository(Ville::class);
    $villes = $repository->findAll();

    return $this->render('admin/ville.html.twig', [
        'form' => $form->createView(),
        'ville' => $ville,
        'villes' => $villes, // Ajout de la variable 'villes'
    ]);
}

#[Route('/villes/delete/{id}', name: 'villes_delete')]
public function delete(Ville $ville): Response
{
$this->entityManager->remove($ville);
$this->entityManager->flush();

return $this->redirectToRoute('villes_index');
}

    #[Route('/villes/search', name: 'villes_search')]
    public function search(Request $request): Response
    {
        $repository = $this->entityManager->getRepository(Ville::class);
        $search = $request->query->get('search');

        $villes = $repository->findBy(['nom' => $search]);

        return $this->render('admin/ville.html.twig', [
            'villes' => $villes,
        ]);
    }
}
