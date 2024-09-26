<?php
namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    #[Route('/campus', name: 'app_campus')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $campusList = $entityManager->getRepository(Campus::class)->findAll();

        return $this->render('admin/campus.html.twig', [
            'campus' => $campusList,
        ]);
    }

#[Route('/new', name: 'campus_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $campus = new Campus();
    $form = $this->createForm(CampusType::class, $campus);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($campus);
        $entityManager->flush();
        return $this->redirectToRoute('app_campus');
    }

    return $this->render('campus/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/campus/edit/{id}', name: 'campus_edit')]
public function edit(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
{
$form = $this->createForm(CampusType::class, $campus);

$form->handleRequest($request);
if ($form->isSubmitted() && $form->isValid()) {
$entityManager->flush();
return $this->redirectToRoute('app_campus');
}

return $this->render('campus/edit.html.twig', [
'form' => $form->createView(),
'campus' => $campus,
]);
}

    #[Route('/campus/delete/{id}', name: 'campus_delete')]
    public function delete(Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($campus);
        $entityManager->flush();

        return $this->redirectToRoute('app_campus'); // Assurez-vous que c'est le bon nom de route
    }
    #[Route('/campus/search', name: 'campus_search')]
    public function search(Request $request, CampusRepository $campusRepository): Response
    {
        $searchTerm = $request->query->get('search');

        // Rechercher les campus par nom
        $campus = $campusRepository->findBySearchTerm($searchTerm);

        return $this->render('admin/campus.html.twig', [
            'campus' => $campus,
        ]);
    }
}
